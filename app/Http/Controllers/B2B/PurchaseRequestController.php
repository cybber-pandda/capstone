<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Notification;
use App\Models\B2BAddress;
use App\Models\User;

class PurchaseRequestController extends Controller
{

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
            ->whereIn('status', [null, 'pending'])
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
            'quantity' => 'required|integer|min:1'
        ]);

        $userId = auth()->id();

        $purchaseRequest = PurchaseRequest::firstOrCreate([
            'customer_id' => $userId,
        ]);

        $product = Product::findOrFail($request->product_id);

        $item = $purchaseRequest->items()->where('product_id', $request->product_id)->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->subtotal = $item->quantity * $product->price;
            $item->save();
        } else {
            $purchaseRequest->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'subtotal' => $request->quantity * $product->price
            ]);
        }

        $salesOfficers = User::where('role', 'salesofficer')->get(); 
        foreach ($salesOfficers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'type' => 'purchase_request',
                'message' => 'A new purchase request has been updated by ' . auth()->user()->name . '. <br><a href="' . route('salesofficer.purchase-requests.index') . '">Visit</a>',
            ]);
        }

        // Notification::create([
        //     'user_id' => $userId,
        //     'type' => 'purchase request',
        //     'message' => 'You have updated your purchase request for product ID: ' . $request->product_id . '. <br><a href="' . route('salesofficer.purchase-requests.index') . '">Visit Link</a>',
        // ]);

        $items = $purchaseRequest->items()->with('product.productImages')->get();

        $mapped = $items->map(function ($item) {
            $product = $item->product;
            return [
                'id' => $item->id,
                'product_name' => $product->name,
                'product_image' => asset(optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png'),
                'quantity' => $item->quantity,
                'price' => $product->price,
                'subtotal' => $item->subtotal,
            ];
        });

        return response()->json([
            'message' => 'Purchase request updated successfully.',
            'items' => $mapped->take(5),
            'total_quantity' => $items->sum('quantity'),
            'subtotal' => $items->sum('subtotal'),
            'pending_count' => $items->sum('quantity')
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

        $item->quantity = $request->quantity;
        $item->subtotal = $item->quantity * $item->product->price;
        $item->save();

        return response()->json(['message' => 'Quantity updated.']);
    }

    public function submitItem(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::where('id', $id)
            ->where('status', null) 
            ->firstOrFail();

        $purchaseRequest->status = 'pending';
        $purchaseRequest->b2b_delivery_date = $request->expected_delivery_date ?? null;
        $purchaseRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Your quotation is being processed. Please wait for approval.',
            'data' => [
                'new_status' => $purchaseRequest->status,
                'submitted_at' => $purchaseRequest->submitted_at
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


}
