<?php

namespace App\Http\Controllers\SalesOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\SubmitManualOrderEmailNotification;
use App\Notifications\ManualOrderReceiptNotification;
use Illuminate\Support\Facades\Notification;

use App\Models\ManualEmailOrder;
use App\Models\Inventory;
use App\Models\StockBatch;

class EmailManualOrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ManualEmailOrder::all();

            return DataTables::of($query)
                ->addColumn('customer_name', function ($pr) {
                    return $pr->customer_name ?? '--';
                })
                ->addColumn('customer_type', function ($pr) {
                    return $pr->customer_type ?? '--';
                })
                ->addColumn('customer_address', function ($pr) {
                    return $pr->customer_address ?? '--';
                })
                ->addColumn('phone_number', function ($pr) {
                    return $pr->customer_phone_number ?? '--';
                })
                ->addColumn('total_items', function ($pr) {
                    $products = json_decode($pr->purchase_request, true) ?? [];
                    return array_sum(array_column($products, 'qty'));
                })
                ->addColumn('delivery_fee', function ($pr) {
                    return '₱' .$pr->delivery_fee;
                })
                ->addColumn('grand_total', function ($pr) {
                    $products = json_decode($pr->purchase_request, true) ?? [];
                    $deliveryFee = $pr->delivery_fee ?? 0;
                    $total = 0;
                    foreach ($products as $product) {
                        $total += ((float) $product['qty']) * ((float) $product['price']);
                    }
                    return '₱' . number_format($total + $deliveryFee, 2);
                })
                ->editColumn('created_at', function ($pr) {
                    return $pr->created_at->format('F d, Y H:i:s');
                })
                ->addColumn('status', function ($pr) {
                    return '<span class="badge bg-info text-dark">' . ucfirst($pr->status) . '</span>';
                })
                ->addColumn('action', function ($pr) {
                    $products = json_decode($pr->purchase_request, true) ?? [];
                    $deliveryFee = $pr->delivery_fee ?? 0;
                    $requestId = $pr->id ?? null;

                    // Fetch product and category names
                    $detailedProducts = [];
                    foreach ($products as $p) {
                        $categoryName = DB::table('categories')->where('id', $p['category_id'])->value('name');
                        $productName  = DB::table('products')->where('id', $p['product_id'])->value('name');

                        $detailedProducts[] = [
                            'category' => $categoryName ?? 'N/A',
                            'product'  => $productName ?? 'N/A',
                            'qty'      => $p['qty'],
                            'price'    => $p['price'], 
                        ];
                    }

                    $buttons = '<button class="btn btn-sm btn-inverse-primary view-products" 
                                    data-products=\'' . json_encode($detailedProducts) . '\' data-fee="'.$deliveryFee.'" data-id="'.$requestId.'">
                                     <i class="link-icon" data-lucide="eye"></i>
                                </button> ';

                    if ($pr->status === 'pending') {
                        $buttons .= '<button class="btn btn-sm btn-inverse-success approve-order me-1" 
                                        data-id="' . $pr->id . '">
                                         <i class="link-icon" data-lucide="check"></i>
                                    </button>';
                    }

                    if ($pr->customer_type === 'Manual Order' && $pr->status === 'pending') {
                        $buttons .= '<button class="btn btn-sm btn-inverse-danger reject-order" 
                                        data-id="' . $pr->id . '">
                                         <i class="link-icon" data-lucide="x"></i>
                                    </button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_emailmanual', [
            'page' => 'Manual Order'
        ]);
    }

    public function approve(Request $request)
    {
        $order = ManualEmailOrder::findOrFail($request->id);

        if ($request->type === 'approve') {
            if ($order->status !== 'approve') {
                $order->status = 'approve';
                $order->save();

                $items = json_decode($order->purchase_request, true);

                if (!empty($items)) {
                    foreach ($items as $item) {
                        Inventory::create([
                            'product_id' => $item['product_id'],
                            'type'       => 'out',
                            'quantity'   => $item['qty'],
                            'reason'     => 'sold',
                        ]);

                        StockBatch::reduceFIFO($item['product_id'],  $item['qty'], 'Manual Order (For product id #' .  $item['product_id'] . ')');
                    }
                }

                if (!empty($order->customer_email)) {
                    Notification::route('mail', $order->customer_email)
                        ->notify(new ManualOrderReceiptNotification($order, 'approve'));
                }
            }

            return response()->json(['message' => 'Order approved and receipt sent successfully.']);
        }

        if ($request->type === 'reject') {
            if ($order->status !== 'rejected') {
                $order->status = 'rejected';
                $order->save();

                if (!empty($order->customer_email)) {
                    Notification::route('mail', $order->customer_email)
                        ->notify(new ManualOrderReceiptNotification($order, 'reject'));
                }
            }

            return response()->json(['message' => 'Order successfully rejected.']);
        }

        return response()->json(['message' => 'Invalid action.'], 400);
    }



    public function submit_manual_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = ManualEmailOrder::create([
            'customer_email' => $request->customer_email,
        ]);


        Notification::route('mail', $request->customer_email)->notify(new SubmitManualOrderEmailNotification($customer->id, $request->customer_email));


        return response()->json([
            'type' => 'success',
            'message' => 'Email manual order successfully sent!',
        ], 200);
    }

    public function delivery_fee(Request $request){
        $validator = Validator::make($request->all(), [
            'manual_order_fee' => 'required',
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $manualOrder = ManualEmailOrder::findOrFail($request->order_id);
        $manualOrder->delivery_fee = $request->manual_order_fee;
        $manualOrder->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Delivery successfully updated!',
        ], 200);

    }

}