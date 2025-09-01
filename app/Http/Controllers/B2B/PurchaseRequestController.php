<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Notification;
use App\Models\B2BAddress;
use App\Models\User;

class PurchaseRequestController extends Controller
{   

    private function generateTransactionUid()
    {
        $timestamp = now()->format('YmdHis');
        return 'PR_' . $timestamp . '_' . Str::uuid()->toString();
    }

    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         // $status = $request->get('status');

    //         $purchaseRequests = PurchaseRequest::with(['items.product.productImages'])
    //             // ->when($status, fn($q) => $q->where('status', $status))
    //             ->where('status', 'pending')
    //             ->latest()
    //             ->get();

    //         $data = [];

    //         foreach ($purchaseRequests as $pr) {
    //             foreach ($pr->items as $item) {
    //                 $product = $item->product;
    //                 $image = optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png';

    //                 $data[] = [
    //                     'sku' => $product->sku,
    //                     'name' => $product->name,
    //                     'price' => number_format($product->price, 2),
    //                     'quantity' => $item->quantity,
    //                     'subtotal' => number_format($item->quantity * $product->price, 2),
    //                     'image' => '<img src="' . asset($image) . '" width="50" height="50">',
    //                     'status' => ucfirst($pr->status),
    //                     'created_at' => $item->created_at->toDateTimeString(),
    //                 ];
    //             }
    //         }

    //         return datatables()->of($data)->rawColumns(['image'])->make(true);
    //     }

    //     return view('pages.b2b.v_purchaseList', [
    //         'page' => 'Purchase Requests'
    //     ]);
    // }
    public function index(Request $request)
    {

        $userId = auth()->id();

        $purchaseRequests = PurchaseRequest::with(['items.product.productImages'])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', 'pending');
            })
            ->where('customer_id', $userId)
            ->latest()
            ->get();

        $hasAddress = B2BAddress::where('user_id', $userId)->exists();

        return view('pages.b2b.v_purchaseList', [
            'page' => 'Purchase Requests',
            'purchaseRequests' => $purchaseRequests,
            'hasAddress' => $hasAddress
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $userId = auth()->id();

        // Check if there is already a pending purchase request
        $pendingRequest = PurchaseRequest::where('customer_id', $userId)
            ->where('status', 'pending')
            ->get();

       if ($pendingRequest->isNotEmpty()) {
            return response()->json([
                'message' => 'You already have a pending purchase request. Please wait until it is processed before creating a new one.'
            ], 400);
        }

        // Create a new purchase request
        $purchaseRequest = PurchaseRequest::create([
            'customer_id' => $userId,
            'status' => null
        ]);

        $product = Product::findOrFail($request->product_id);
        $price = $this->calculateProductPrice($product);

        // Add the product item
        $purchaseRequest->items()->create([
            'product_id' => $request->product_id,
            'quantity'   => $request->quantity,
            'subtotal'   => $request->quantity * $price
        ]);

        // Notify sales officers
        $salesOfficers = User::where('role', 'salesofficer')->get();
        foreach ($salesOfficers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'type'    => 'purchase_request',
                'message' => 'A new purchase request has been submitted by ' . auth()->user()->name .
                    '. <br><a href="' . route('salesofficer.purchase-requests.index') . '">Visit</a>',
            ]);
        }

        $items = $purchaseRequest->items()->with('product.productImages')->get();

        $mapped = $items->map(function ($item) {
            $product = $item->product;
            $price = $this->calculateProductPrice($product);
            return [
                'id'            => $item->id,
                'product_name'  => $product->name,
                'product_image' => asset(optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png'),
                'quantity'      => $item->quantity,
                'price'         => $price,
                'subtotal'      => $item->subtotal,
            ];
        });

        return response()->json([
            'message'        => 'Purchase request created successfully.',
            'items'          => $mapped->take(5),
            'total_quantity' => $items->sum('quantity'),
            'subtotal'       => $items->sum('subtotal'),
            'pending_count'  => $items->sum('quantity')
        ]);
    }

    public function updateItem(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $item = PurchaseRequestItem::with('purchaseRequest')->findOrFail($id);

        if (
            $item->purchaseRequest->customer_id !== auth()->id() ||
            $item->purchaseRequest->status !== null
        ) {
            return response()->json([
                'message' => 'You can only update items from pending purchase requests.'
            ], 403);
        }

        $price = $this->calculateProductPrice($item->product);

        $item->quantity = $request->quantity;
        $item->subtotal = $item->quantity * $price;
        $item->save();

        return response()->json(['message' => 'Quantity updated.']);
    }

    public function submitItem(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $request->validate([
            'prids' => 'required|array',
            'prids.*' => 'integer|exists:purchase_requests,id',
            'expected_delivery_date' => 'nullable|date'
        ]);

        // Get the purchase requests to check ownership
        $purchaseRequests = PurchaseRequest::where('customer_id', $user->id)
            ->whereIn('id', $request->prids)
            ->whereNull('status')
            ->get();

        if ($purchaseRequests->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid purchase requests found to submit.'
            ], 404);
        }

        // Update all purchase requests
        $updatedCount = PurchaseRequest::whereIn('id', $purchaseRequests->pluck('id'))
            ->update([
                'transaction_uuid' => $this->generateTransactionUid(),
                'status' => 'pending',
                'b2b_delivery_date' => $request->expected_delivery_date
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Your purchase requests are being processed. Please wait for approval.',
            'data' => [
                'updated_count' => $updatedCount
            ]
        ]);
    }

    public function deleteItem($id)
    {
        $item = PurchaseRequestItem::with('purchaseRequest')->findOrFail($id);
        $purchaseRequest = $item->purchaseRequest;

        if (
            $purchaseRequest->customer_id !== auth()->id() ||
            $purchaseRequest->status !== null
        ) {
            return response()->json([
                'message' => 'You can only delete items from waiting purchase requests.'
            ], 403);
        }

        $purchaseRequest->loadCount('items');

        if ($purchaseRequest->items_count === 1) {
            $purchaseRequest->delete();
            $item->delete();

            return response()->json([
                'message' => 'Item removed. Purchase request also deleted.',
                'purchase_request_deleted' => true
            ]);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed from purchase request.',
            'purchase_request_deleted' => false
        ]);
    }

    private function calculateProductPrice(Product $product)
    {
        return ($product->discount && $product->discount > 0 && $product->discounted_price)
            ? $product->discounted_price
            : $product->price;
    }
}
