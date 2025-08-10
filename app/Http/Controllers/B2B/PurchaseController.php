<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestReturn;
use App\Models\PurchaseRequestRefund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $purchaseRequests = PurchaseRequest::with([
                'items.product.productImages',
                'items.returnRequest', // add this relation
                'items.refundRequest'  // add this relation
            ])
                // ->where('status', 'delivered')
                ->latest()
                ->get();

            $data = [];

            foreach ($purchaseRequests as $pr) {
                // i want to hide first the button it will show only if $pr->status === 'delivered'
                foreach ($pr->items as $item) {
                    $product = $item->product;
                    $image = optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png';

                    // Check return/refund flags
                    $return = $item->returnRequest;
                    $refund = $item->refundRequest;

                    $showReturn = !$return || $return->status !== 'approved';
                    $showRefund = !$refund || $refund->processed_by === null;

                    $actions = [];
                   
                    if ($pr->status === 'delivered') {
                        if ($showReturn) {
                            $actions[] = '<button class="btn btn-xs btn-warning btn-return" data-id="' . $item->id . '">Return</button>';
                        } else {
                            $actions[] = '-';
                        }

                        if ($showRefund) {
                            $actions[] = '<button class="btn btn-xs btn-danger btn-refund" data-id="' . $item->id . '">Refund</button>';
                        } else {
                            $actions[] = '-';
                        }
                    } else {
                        $actions[] = '';
                        $actions[] = '';
                    }

                    $actionHtml = implode('&nbsp;', $actions);

                    $data[] = [
                        'id' => $item->id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'price' => number_format($product->price, 2),
                        'quantity' => $item->quantity,
                        'subtotal' => number_format($item->quantity * $product->price, 2),
                        'image' => '<img src="' . asset($image) . '" width="50" height="50">',
                        'created_at' => $item->created_at->toDateTimeString(),
                        'actions' => $actionHtml,
                    ];
                }
            }

            return datatables()->of($data)->rawColumns(['image', 'actions'])->make(true);
        }

        return view('pages.b2b.v_purchase', [
            'page' => 'My Purchase',
        ]);
    }


    public function requestReturn(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:purchase_request_items,id',
            'reason' => 'required|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = PurchaseRequestItem::with('purchaseRequest', 'product')->findOrFail($request->item_id);

        if ($item->purchaseRequest->status !== 'delivered') {
            return response()->json(['message' => 'Only delivered items can be returned.'], 422);
        }

        if (PurchaseRequestReturn::where('purchase_request_item_id', $item->id)->exists()) {
            return response()->json(['message' => 'Return request already submitted for this item.'], 409);
        }

        // Handle file upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $filename = Str::random(10) . '.' . $request->file('photo')->getClientOriginalExtension();
            $photoPath = $request->file('photo')->move(public_path('assets/upload'), $filename);
            $photoPath = 'assets/uploads/' . $filename;
        }

        PurchaseRequestReturn::create([
            'purchase_request_id' => $item->purchase_request_id,
            'purchase_request_item_id' => $item->id,
            'product_id' => $item->product_id,
            'reason' => $request->reason,
            'photo' => $photoPath,
        ]);

        return response()->json(['message' => 'Return request submitted.']);
    }

    public function requestRefund(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:purchase_request_items,id',
            'reason' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|max:50',
            'reference' => 'required|string|max:255',
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = PurchaseRequestItem::with('purchaseRequest', 'product')->findOrFail($request->item_id);

        if ($item->purchaseRequest->status !== 'delivered') {
            return response()->json(['message' => 'Only delivered items can be refunded.'], 422);
        }

        if (PurchaseRequestRefund::where('purchase_request_item_id', $item->id)->exists()) {
            return response()->json(['message' => 'Refund request already submitted for this item.'], 409);
        }

        // Handle file upload
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $filename = Str::random(10) . '.' . $request->file('proof')->getClientOriginalExtension();
            $request->file('proof')->move(public_path('assets/upload'), $filename);
            $proofPath = 'assets/uploads/' . $filename;
        }

        PurchaseRequestRefund::create([
            'purchase_request_id' => $item->purchase_request_id,
            'purchase_request_item_id' => $item->id,
            'product_id' => $item->product_id,
            'amount' => $request->amount,
            'method' => $request->method,
            'reference' => $request->reference,
            'proof' => $proofPath,
            'processed_by' => null,
        ]);

        return response()->json(['message' => 'Refund request submitted.']);
    }

    public function purchaseReturnRefund(Request $request)
    {
        $type = $request->input('type');

        if ($request->ajax()) {
            if ($type === 'return') {
                $data = PurchaseRequestReturn::with(['product', 'purchaseRequestItem'])
                    ->latest()
                    ->get()
                    ->map(function ($r) {
                        $product = $r->product;
                        $item = $r->purchaseRequestItem;

                        return [
                            'image' => '<img src="' . asset(optional($product->productImages->first())->image_path ?? 'assets/shop/img/noimage.png') . '" width="50">',
                            'sku' => $product->sku,
                            'name' => $product->name,
                            'quantity' => $item->quantity,
                            'reason' => $r->reason,
                            'status' => ucfirst($r->status),
                            'date' => $r->created_at->toDateTimeString(),
                        ];
                    });

                return datatables()->of($data)->rawColumns(['image'])->make(true);
            }

            if ($type === 'refund') {
                $data = PurchaseRequestRefund::with(['product', 'purchaseRequestItem'])
                    ->latest()
                    ->get()
                    ->map(function ($r) {
                        $product = $r->product;
                        $item = $r->purchaseRequestItem;

                        return [
                            'image' => '<img src="' . asset(optional($product->productImages->first())->image_path ?? 'assets/shop/img/noimage.png') . '" width="50">',
                            'sku' => $product->sku,
                            'name' => $product->name,
                            'quantity' => $item->quantity,
                            'amount' => number_format($r->amount, 2),
                            'method' => ucfirst($r->method),
                            'reference' => $r->reference,
                            'status' => $r->processed_by ? 'Processed' : 'Pending',
                            'date' => $r->created_at->toDateTimeString(),
                        ];
                    });

                return datatables()->of($data)->rawColumns(['image'])->make(true);
            }

            if ($type === 'cancelled') {
                // Leave logic blank — you will handle this
                return datatables()->of([])->make(true);
            }

            return response()->json(['message' => 'Invalid type'], 400);
        }

        return view('pages.b2b.v_returnRefund', [
            'page' => 'Return & Refund',
        ]);
    }
}
