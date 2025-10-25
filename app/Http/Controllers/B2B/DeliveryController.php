<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\Delivery;
use App\Models\CompanySetting;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\B2BAddress;
use App\Models\B2BDetail;
use App\Models\Bank;
use App\Models\ProductRating;
use App\Models\PaidPayment;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
         // 1️⃣ If user is NOT logged in → show login page
    if (!Auth::check()) {
        $page = 'Sign In';
        $companysettings = DB::table('company_settings')->first();

        return response()
            ->view('auth.login', compact('page', 'companysettings'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    // 2️⃣ If user is logged in → check their role
    $user = Auth::user();

    // Example role logic (adjust 'role' and role names to match your database)
    
   if ($user->role === 'b2b') {
    
        $user = auth()->user();
        $userId = $user->id;

        if ($request->ajax()) {
            $query = Order::with([
                'user',
                'b2bAddress',
                'items.product',
                'delivery.deliveryUser'
            ])
            ->where('user_id', $userId)
            ->has('delivery')
            ->latest();

            return datatables()->of($query)
                ->addColumn('order_number', fn($order) => $order->order_number ?? 'N/A')
                ->addColumn('delivery_name', fn($order) => optional($order->delivery->deliveryUser)->name ?? 'Unassigned')
                ->addColumn('total_items', fn($order) => $order->items->sum('quantity') ?? 0)
            ->addColumn('grand_total', function ($order) {
                $purchaseRequestId = null;
                
                if (preg_match('/REF (\d+)-/', $order->order_number, $matches)) {
                    $purchaseRequestId = $matches[1];
                }

                $subtotal = 0;
                $vatRate = 0;
                $deliveryFee = 0;

                if ($purchaseRequestId) {
                    $subtotal = \DB::table('purchase_request_items')
                        ->where('purchase_request_id', $purchaseRequestId)
                        ->sum('subtotal');

                    $purchaseRequest = \App\Models\PurchaseRequest::find($purchaseRequestId);
                    if ($purchaseRequest) {
                        $vatRate = $purchaseRequest->vat ?? 0;
                        $deliveryFee = $purchaseRequest->delivery_fee ?? 0;
                    }
                }

                $vatAmount = $subtotal * ($vatRate / 100);
                $grandTotal = $subtotal + $vatAmount + $deliveryFee;

                return '₱' . number_format($grandTotal, 2);
            })

                ->addColumn('status', function ($order) {
                    $status = $order->delivery->status ?? 'unknown';
                    $messages = [
                        'pending' => 'To assign rider',
                        'assigned' => 'Rider assigned',
                        'on_the_way' => 'Out for delivery',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ];
                    $badgeColors = [
                        'pending' => 'warning',
                        'assigned' => 'info',
                        'on_the_way' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    ];
                    $badgeText = $messages[$status] ?? ucfirst($status);
                    $badgeClass = $badgeColors[$status] ?? 'secondary';

                    return '<span class="text-' . $badgeClass . '" style="font-weight:bold;font-size:11px;">' . $badgeText . '</span>';
                })
                ->addColumn('rating', function ($order) {
                    $rating = $order->delivery->rating->rating ?? null;
                    if (!$rating) return '<i>No rating yet.</i>';
                    return 'Rating: ' . str_repeat('<i class="fa fa-star text-warning"></i>', $rating) 
                        . str_repeat('<i class="fa fa-star-o text-muted"></i>', 5 - $rating);
                })
                ->addColumn('action', function ($order) {
                    $status = $order->delivery->status ?? 'unknown';
                    $trackBtn = '';
                    if ($status === 'on_the_way') {
                        $trackBtn = '<a href="' . route('b2b.delivery.track.index', $order->delivery->id) . '" class="btn btn-sm btn-primary ms-2"> Track </a>';
                    }

                    $hasRiderRating = !empty($order->delivery->rating);
                    $hasProductRating = ProductRating::where('user_id', auth()->id())
                        ->whereIn('product_id', $order->items->pluck('product_id'))
                        ->exists();

                    $proofBtn = '';
                    $invoiceBtn = '';
                    $deliveryReceiptBtn = '';
                    $ratingBtn = '';

                    if ($order->delivery->sales_invoice_flg == 1) {
                        $invoiceBtn = '<a href="' . route('b2b.delivery.invoice', $order->delivery->id) . '" class="btn btn-sm btn-primary" title="View Invoice" style="margin-right:5px;font-size:10.5px;"> <i class="fa fa-file-text" aria-hidden="true"></i> </a>';
                    }

                    if ($status === 'delivered' && $order->delivery->proof_delivery) {
                        $proofBtn = '<button class="btn btn-sm btn-info view-proof-btn" data-proof="' . asset($order->delivery->proof_delivery) . '" title="View Proof of Delivery" style="margin-right:5px;font-size:10.5px;"> <i class="fa fa-file-image" aria-hidden="true"></i> </button>';
                        $deliveryReceiptBtn = '<a href="' . route('b2b.delivery.receipt', $order->delivery->id) . '" class="btn btn-sm btn-danger" title="View Delivery Receipt" style="margin-right:5px;font-size:10.5px;"> <i class="fa fa-clipboard" aria-hidden="true"></i> </a>';

                        if ($hasRiderRating && $hasProductRating) {
                            $ratingBtn = '<button class="btn btn-sm btn-secondary" disabled style="margin-right:5px;background:gray;opacity:0.6;color:black;"> Rated </button>';
                        } else {
                            $ratingBtn = '<a href="' . route('b2b.delivery.product.rate', $order->order_number) . '" title="Rate Delivery Driver" class="btn btn-warning btn-sm" style="font-size:10.5px;"> <i class="fa fa-car" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i> </a> 
                            <a href="' . route('b2b.delivery.product.rate', $order->order_number) . '" title="Rate Products" class="btn btn-success btn-sm" style="font-size:10.5px;"> <i class="fa fa-shopping-cart" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i> </a>';
                        }
                    }

                    return $trackBtn . $proofBtn . $invoiceBtn . $deliveryReceiptBtn . $ratingBtn;
                })
                ->rawColumns(['status', 'action', 'rating'])
                ->make(true);
        }

        return view('pages.b2b.v_delivery', [
            'page' => 'My Deliveries',
        ]);
    }
    //for returning to the dashboard
         return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function track_delivery($id)
    {
        $delivery = Delivery::with(['order.b2bAddress'])->findOrFail($id);
        $customerLat = $delivery->order->b2bAddress->delivery_address_lat ?? null;
        $customerLng = $delivery->order->b2bAddress->delivery_address_lng ?? null;
        $deliveryManLat = $delivery->delivery_latitude;
        $deliveryManLng = $delivery->delivery_longitude;

        return view('pages.b2b.v_track_delivery', [
            'page' => 'Track Delivery',
            'delivery' => $delivery,
            'deliveryManLat'=> $deliveryManLat,
            'deliveryManLng'=> $deliveryManLng,
            'customerLat' => $customerLat,
            'customerLng' => $customerLng,
        ]);
    }

    public function view_invoice($id)
    {
        $invoiceData = Delivery::with([
            'order.b2bAddress', 'order.user', 'order.items.product'
        ])->findOrFail($id);

        $page = 'Invoice';
        $isPdf = false;
        $banks = Bank::get();
        $b2bReqDetails = null;
        $b2bAddress = null;
        $salesOfficer = null;
        $quotation = null;
        $paidPR = null;
        $superadmin = User::where('role', 'superadmin')->first();
        $companySettings = CompanySetting::first();

        if ($invoiceData->order?->order_number) {
            if (preg_match('/REF (\d+)-/', $invoiceData->order->order_number, $matches)) {
                $purchaseRequestId = $matches[1];
                $quotation = PurchaseRequest::with(['customer', 'items.product'])
                    ->where('customer_id', auth()->id())
                    ->findOrFail($purchaseRequestId);

                if ($quotation->customer_id) {
                    $b2bReqDetails = B2BDetail::where('user_id', $quotation->customer_id)->first();
                    $b2bAddress = B2BAddress::where('user_id', $quotation->customer_id)->where('status', 'active')->first();
                }

                if ($quotation->prepared_by_id) {
                    $salesOfficer = User::where('id', $quotation->prepared_by_id)->first();
                }

                if ($quotation->payment_method == 'pay_now') {
                    $paidPR = PaidPayment::where('purchase_request_id', $quotation->id)->first();
                }
            }
        }

        return view('pages.invoice', compact(
            'invoiceData', 'quotation', 'page', 'companySettings', 'isPdf', 'banks',
            'b2bReqDetails', 'b2bAddress', 'salesOfficer', 'superadmin', 'paidPR'
        ));
    }

    public function view_receipt($id)
    {
        $invoiceData = Delivery::with([
            'order.b2bAddress', 'order.user', 'order.items.product'
        ])->findOrFail($id);

        $page = 'Invoice';
        $isPdf = false;
        $banks = Bank::get();
        $b2bReqDetails = null;
        $b2bAddress = null;
        $salesOfficer = null;
        $quotation = null;
        $paidPR = null;
        $superadmin = User::where('role', 'superadmin')->first();
        $companySettings = CompanySetting::first();

        if ($invoiceData->order?->order_number) {
            if (preg_match('/REF (\d+)-/', $invoiceData->order->order_number, $matches)) {
                $purchaseRequestId = $matches[1];
                $quotation = PurchaseRequest::with(['customer', 'items.product'])
                    ->where('customer_id', auth()->id())
                    ->findOrFail($purchaseRequestId);

                if ($quotation->customer_id) {
                    $b2bReqDetails = B2BDetail::where('user_id', $quotation->customer_id)->first();
                    $b2bAddress = B2BAddress::where('user_id', $quotation->customer_id)->where('status', 'active')->first();
                }

                if ($quotation->prepared_by_id) {
                    $salesOfficer = User::where('id', $quotation->prepared_by_id)->first();
                }

                if ($quotation->payment_method == 'pay_now') {
                    $paidPR = PaidPayment::where('purchase_request_id', $quotation->id)->first();
                }
            }
        }

        return view('pages.receipt', compact(
            'invoiceData', 'quotation', 'page', 'companySettings', 'isPdf', 'banks',
            'b2bReqDetails', 'b2bAddress', 'salesOfficer', 'superadmin', 'paidPR'
        ));
    }

    public function downloadInvoice($id)
    {
        $invoiceData = Delivery::with([
            'order.b2bAddress', 'order.user', 'order.items.product'
        ])->findOrFail($id);

        $page = 'Invoice';
        $isPdf = true;
        $banks = Bank::get();
        $b2bReqDetails = null;
        $b2bAddress = null;
        $salesOfficer = null;
        $quotation = null;
        $superadmin = User::where('role', 'superadmin')->first();
        $companySettings = CompanySetting::first();

        // Extract PR from order number
        $purchaseRequest = null;
        if ($invoiceData->order?->order_number) {
            if (preg_match('/REF (\d+)-/', $invoiceData->order->order_number, $matches)) {
                $purchaseRequestId = $matches[1];
                $quotation = PurchaseRequest::with(['customer', 'items.product'])
                    ->where('customer_id', auth()->id())
                    ->findOrFail($purchaseRequestId);

                if ($quotation->customer_id) {
                    $b2bReqDetails = B2BDetail::where('user_id', $quotation->customer_id)->first();
                    $b2bAddress = B2BAddress::where('user_id', $quotation->customer_id)->where('status', 'active')->first();
                }

                if ($quotation->prepared_by_id) {
                    $salesOfficer = User::where('id', $quotation->prepared_by_id)->first();
                }

                $purchaseRequest = PurchaseRequest::find($purchaseRequestId);
            }
        }

        $pdf = Pdf::loadView('pages.invoice', compact(
            'invoiceData', 'page', 'companySettings', 'isPdf', 'purchaseRequest', 'quotation', 'banks',
            'b2bReqDetails', 'b2bAddress', 'salesOfficer', 'superadmin'
        ))->setPaper('A4', 'portrait');

        return $pdf->download("invoice-{$invoiceData->order?->order_number}.pdf");
    }

    public function rate_page($id)
    {
        $delivery = Delivery::with('deliveryUser', 'rating')->findOrFail($id);

        return view('pages.b2b.v_rating', [
            'delivery' => $delivery,
            'page' => 'Rate Delivery Rider',
        ]);
    }

    public function save_rating(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $delivery = Delivery::findOrFail($id);

        if ($delivery->rating) {
            return redirect()->back()->with('info', 'You already rated this delivery.');
        }

        $delivery->rating()->create([
            'rating' => $request->rating,
            'feedback' => $request->feedback,
        ]);

        return redirect()->route('b2b.delivery.index')->with('success', 'Thank you for your feedback!');
    }

public function rate_product_page($orderNumber)
{
    $order = null;
    $delivery = null;

    if (preg_match('/REF (\d+)-/', $orderNumber, $matches)) {
        $purchaseRequestId = $matches[1];
        $order = PurchaseRequest::with('items.product')->where('id', $purchaseRequestId)->first();

        // Find the corresponding Order
        $realOrder = Order::where('order_number', 'like', 'REF ' . $purchaseRequestId . '-%')->first();
        $delivery = $realOrder?->delivery;
    }

    return view('pages.b2b.v_productrating', [
        'order' => $order,
        'delivery' => $delivery,
        'page' => 'Rate Products & Driver Service',
    ]);
}

    public function submit_product_rating(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $rating = $request->rating ?? 5; // default to 5 if not provided

        ProductRating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $productId,
            ],
            [
                'rating' => $rating,
                'review' => $request->feedback,
            ]
        );

        return redirect()->route('b2b.delivery.index')->with('success', 'Thanks for rating this product!');
    }

public function submit_all_ratings(Request $request, $orderId)
{
    // Validate inputs
    $request->validate([
        'rider_rating' => 'required|integer|min:1|max:5',
        'rider_feedback' => 'nullable|string|max:1000',
        'ratings' => 'nullable|array',
        'ratings.*' => 'nullable|integer|min:1|max:5',
        'feedbacks' => 'nullable|array',
        'feedbacks.*' => 'nullable|string|max:1000',
    ]);

    // Get the PurchaseRequest and its items
    $purchaseRequest = PurchaseRequest::with('items.product')->findOrFail($orderId);

    // --- Save Rider Rating ---
    // Find the corresponding Order
    $order = Order::where('order_number', 'like', 'REF ' . $purchaseRequest->id . '-%')->first();

    if ($order && $order->delivery && !$order->delivery->rating) {
        $order->delivery->rating()->create([
            'rating' => $request->rider_rating,
            'feedback' => $request->rider_feedback,
        ]);
    }

    // --- Save Product Ratings ---
    foreach ($purchaseRequest->items as $item) {
        $productId = $item->product_id;
        $rating = $request->ratings[$productId] ?? 5;
        $feedback = $request->feedbacks[$productId] ?? null;

        ProductRating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $productId,
            ],
            [
                'rating' => $rating,
                'review' => $feedback,
            ]
        );
    }

    return redirect()->route('b2b.delivery.index')
        ->with('success', 'Thanks for rating the rider and all products!');
}

}
