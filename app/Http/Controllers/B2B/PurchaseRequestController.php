<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;

class PurchaseRequestController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // $status = $request->get('status');

            $purchaseRequests = PurchaseRequest::with(['items.product.productImages'])
                // ->when($status, fn($q) => $q->where('status', $status))
                ->where('status', 'pending')
                ->latest()
                ->get();

            $data = [];

            foreach ($purchaseRequests as $pr) {
                foreach ($pr->items as $item) {
                    $product = $item->product;
                    $image = optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png';

                    $data[] = [
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'price' => number_format($product->price, 2),
                        'quantity' => $item->quantity,
                        'subtotal' => number_format($item->quantity * $product->price, 2),
                        'image' => '<img src="' . asset($image) . '" width="50" height="50">',
                        'status' => ucfirst($pr->status),
                        'created_at' => $item->created_at->toDateTimeString(),
                    ];
                }
            }

            return datatables()->of($data)->rawColumns(['image'])->make(true);
        }

        return view('pages.b2b.v_purchaseList', [
            'page' => 'Purchase Requests'
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
            'status' => 'pending'
        ]);

        $item = $purchaseRequest->items()->where('product_id', $request->product_id)->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            $purchaseRequest->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        $items = $purchaseRequest->items()->with('product.productImages')->get();

        $mapped = $items->map(function ($item) {
            $product = $item->product;
            return [
                'id' => $item->id,
                'product_name' => $product->name,
                'product_image' => asset(optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png'),
                'quantity' => $item->quantity,
                'price' => $product->price,
                'subtotal' => $item->quantity * $product->price,
            ];
        });

        return response()->json([
            'message' => 'Purchase request updated successfully.',
            'items' => $mapped->take(5),
            'total_quantity' => $items->sum('quantity'),
            'subtotal' => $items->sum(fn($i) => $i->quantity * $i->product->price),
            'pending_count' => $items->sum('quantity')
        ]);
    }


    public function destroyItem($id)
    {
        $userId = auth()->id();

        $item = PurchaseRequestItem::findOrFail($id);

        if ($item->purchaseRequest->customer_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        $item->delete();

        return response()->json(['message' => 'Item removed from purchase request.']);
    }
}
