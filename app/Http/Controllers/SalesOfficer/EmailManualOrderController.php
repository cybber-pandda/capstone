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
                    return is_null($pr->customer_email) ? 'Walk-In' : 'Manual Order';
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
                ->addColumn('grand_total', function ($pr) {
                    $products = json_decode($pr->purchase_request, true) ?? [];
                    $total = 0;
                    foreach ($products as $product) {
                        $total += ((float) $product['qty']) * ((float) $product['price']);
                    }
                    return '₱' . number_format($total, 2);
                })
                ->editColumn('created_at', function ($pr) {
                    return $pr->created_at->format('F d, Y H:i:s');
                })
                ->addColumn('status', function ($pr) {
                    return '<span class="badge bg-info text-dark">'. ucfirst($pr->status) .'</span>';
                })
               ->addColumn('action', function ($pr) {
                    $products = json_decode($pr->purchase_request, true) ?? [];

                    // Fetch product and category names
                    $detailedProducts = [];
                    foreach ($products as $p) {
                        $categoryName = DB::table('categories')->where('id', $p['category_id'])->value('name');
                        $productName  = DB::table('products')->where('id', $p['product_id'])->value('name');

                        $detailedProducts[] = [
                            'category' => $categoryName ?? 'N/A',
                            'product'  => $productName ?? 'N/A',
                            'qty'      => $p['qty'],
                            'price'    => $p['price']
                        ];
                    }

                    $buttons = '<button class="btn btn-sm btn-primary view-products" 
                                    data-products=\'' . json_encode($detailedProducts) . '\'>
                                    View
                                </button> ';

                    if ($pr->status === 'waiting') {
                        $buttons .= '<button class="btn btn-sm btn-success approve-order" 
                                        data-id="' . $pr->id . '">
                                        Approve
                                    </button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['status','action'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_emailmanual', [
            'page' => 'Manual Order'
        ]);
    }

    public function approve(Request $request)
    {
        $order = ManualEmailOrder::findOrFail($request->id);

        if ($order->status !== 'approve') {
            $order->status = 'approve';
            $order->save();

            Notification::route('mail', $order->customer_email)->notify(new ManualOrderReceiptNotification($order));
        }

        return response()->json(['message' => 'Order approved and receipt sent successfully.']);
    }

    public function submit_manual_order(Request $request){
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
}
