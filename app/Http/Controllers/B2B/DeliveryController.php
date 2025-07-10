<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Order;
use App\Models\Delivery;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); // assuming B2B user
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
                    $total = $order->items->sum(function ($item) {
                        return $item->quantity * ($item->product->price ?? 0);
                    });
                    return '₱' . number_format($total, 2);
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
                ->addColumn('action', function ($order) {
                    $status = $order->delivery->status ?? 'unknown';

                    $trackBtn = '';
                    if ($status === 'on_the_way') {
                        $trackBtn = '<a href="' . route('b2b.delivery.track.index', $order->delivery->id) . '" class="btn btn-sm btn-primary ms-2">
                        Track
                    </a>';
                    }

                    $proofBtn = '';
                    if ($status === 'delivered' && $order->delivery->proof_delivery) {
                        $proofBtn = '<button class="btn btn-sm btn-info ms-2 view-proof-btn" 
                        data-proof="' . asset($order->delivery->proof_delivery) . '">
                        View Proof
                    </button>';
                    }

                    return $trackBtn . $proofBtn;
                })

                ->rawColumns(['status', 'action'])
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
}

