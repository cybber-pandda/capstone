<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Order;
use App\Models\Delivery;
use App\Models\CompanySetting;
use App\Models\PurchaseRequest;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
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
                    $subtotal = $order->items->sum(function ($item) {
                        return $item->quantity * ($item->product->price ?? 0);
                    });

                    // Default values
                    $vatRate = 0;
                    $deliveryFee = 0;

                    if (preg_match('/REF (\d+)-/', $order->order_number, $matches)) {
                        $purchaseRequestId = $matches[1];
                        $purchaseRequest = \App\Models\PurchaseRequest::find($purchaseRequestId);

                        if ($purchaseRequest) {
                            $vatRate = $purchaseRequest->vat ?? 0;
                            $deliveryFee = $purchaseRequest->delivery_fee ?? 0;
                        }
                    }

                    $vat = $subtotal * ($vatRate / 100);
                    $grandTotal = $subtotal + $vat + $deliveryFee;

                    return '₱' . number_format($grandTotal, 2);
                })
                ->addColumn('status', function ($order) {
                    $status = $order->delivery->status ?? 'unknown';

                    $messages = [
                        'pending' => 'Waiting for admin to assign a rider',
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

                    return '<span class="badge bg-' . $badgeClass . '">' . $badgeText . '</span>';
                })
                ->addColumn('rating', function ($order) {
                    $rating = $order->delivery->rating->rating ?? null;

                    if (!$rating) return '';

                    return 'Rating: '.str_repeat('<i class="fa fa-star text-warning"></i>', $rating) .
                        str_repeat('<i class="fa fa-star-o text-muted"></i>', 5 - $rating);
                })
                ->addColumn('action', function ($order) {
                    $status = $order->delivery->status ?? 'unknown';

                    $trackBtn = '';
                    if ($status === 'on_the_way') {
                        $trackBtn = '<a href="' . route('b2b.delivery.track.index', $order->delivery->id) . '" class="btn btn-sm btn-primary ms-2">
                        Track
                    </a>';
                    }

                    $proofBtn = '';
                    $invoiceBtn = '';
                    $ratingBtn = '';

                    if ($status === 'delivered' && $order->delivery->proof_delivery) {
                        $proofBtn = '<button class="btn btn-sm btn-info view-proof-btn" 
                                        data-proof="' . asset($order->delivery->proof_delivery) . '" style="margin-right:5px;">
                                        View Proof
                                    </button>';

                        $invoiceBtn = '<a href="' . route('b2b.delivery.invoice', $order->delivery->id) . '" class="btn btn-sm btn-primary" style="margin-right:5px;">
                                        Generate Invoice
                                       </a>';

                        if ($order->delivery->rating) {
                            $ratingBtn = '<button class="btn btn-sm btn-secondary" disabled style="margin-right:5px;background:gray;opacity:0.6;color:black;">
                                            Rated
                                        </button>';
                        } else {
                             $ratingBtn = '<a href="' . route('b2b.delivery.rider.rate', $order->delivery->id) . '" class="btn btn-warning btn-sm">
                                                Rate Rider
                                            </a>  
                                            
                                            <a href="' . route('b2b.delivery.rider.rate', $order->delivery->id) . '" class="btn btn-success btn-sm">
                                                Rate Product
                                            </a>
                                            
                                            ';
                        }

                       
                    }

                    return $trackBtn . $proofBtn . $invoiceBtn . $ratingBtn;
                })

                ->rawColumns(['status', 'action', 'rating'])
                ->make(true);
        }

        return view('pages.b2b.v_delivery', [
            'page' => 'My Deliveries',
        ]);
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
            'deliveryManLat' => $deliveryManLat,
            'deliveryManLng' => $deliveryManLng,
            'customerLat' => $customerLat,
            'customerLng' => $customerLng,
        ]);
    }

    public function view_invoice($id)
    {
        $invoiceData = Delivery::with([
            'order.b2bAddress',
            'order.user',
            'order.items.product'
        ])->findOrFail($id);

        $companySettings = CompanySetting::first();
        $page = 'Invoice';
        $isPdf = false;

        // Attempt to extract PR ID from order number and get PR
        $purchaseRequest = null;
        if ($invoiceData->order?->order_number) {
            if (preg_match('/REF (\d+)-/', $invoiceData->order->order_number, $matches)) {
                $purchaseRequestId = $matches[1];
                $purchaseRequest = PurchaseRequest::find($purchaseRequestId);
            }
        }

        return view('pages.invoice', compact('invoiceData', 'page', 'companySettings', 'isPdf', 'purchaseRequest'));
    }

    public function downloadInvoice($id)
    {
        $invoiceData = Delivery::with([
            'order.b2bAddress',
            'order.user',
            'order.items.product'
        ])->findOrFail($id);

        $companySettings = CompanySetting::first();
        $page = 'Invoice';
        $isPdf = true;

        // Extract PR from order number
        $purchaseRequest = null;
        if ($invoiceData->order?->order_number) {
            if (preg_match('/REF (\d+)-/', $invoiceData->order->order_number, $matches)) {
                $purchaseRequestId = $matches[1];
                $purchaseRequest = PurchaseRequest::find($purchaseRequestId);
            }
        }

        $pdf = Pdf::loadView('pages.invoice', compact('invoiceData', 'page', 'companySettings', 'isPdf', 'purchaseRequest'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("invoice-{$invoiceData->order?->order_number}.pdf");
    }

    public function rate_page($id)
    {
        $delivery = Delivery::with('deliveryUser', 'rating')->findOrFail($id);

        // if ($delivery->rating) {
        //     return redirect()
        //         ->route('b2b.delivery.index')
        //         ->with('toast_back', 'You have already rated this delivery.');
        // }

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
}
