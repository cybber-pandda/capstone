<?php

namespace App\Http\Controllers\DeliveryRider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\DeliveryHistory;

class DeliveryController extends Controller
{
    public function delivery_pickup(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);
        $delivery->status = 'on_the_way';
        $delivery->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }

    public function delivery_orders(Request $request)
    {
        $user = User::getCurrentUser();
        $status = $request->get('status');

        if ($request->ajax()) {
            $query = Order::with([
                'user',
                'b2bAddress',
                'delivery.deliveryUser',
                'items.product',
            ])
                ->whereHas('delivery', function ($q) use ($user, $status) {
                    $q->where('delivery_rider_id', $user->id);

                    if (!empty($status)) {
                        $q->where('status', $status);
                    }
                })
                ->latest();

            return datatables()->of($query)
                ->addColumn('order_number', fn($pr) => $pr->order_number ?? 'N/A')
                ->addColumn('customer_name', fn($pr) => optional($pr->user)->name ?? 'N/A')
                ->addColumn('total_items', fn($pr) => $pr->items->sum('quantity'))
                ->addColumn('grand_total', function ($pr) {
                    $total = $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
                    return '₱' . number_format($total, 2);
                })
                ->editColumn('created_at', fn($pr) => $pr->created_at->format('Y-m-d H:i:s'))
                ->addColumn('action', function ($pr) {
                    $btn = '<button class="btn btn-sm btn-inverse-info view-items-btn" data-id="' . $pr->id . '"><i class="link-icon" data-lucide="list"></i> View Items</button> ';
                    //$btn .= '<a href="' . route('deliveryrider.delivery.tracking', $pr->id) . '" class="btn btn-sm btn-inverse-primary">Track</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.admin.deliveryrider.v_deliveryOrders', [
            'page' => 'Delivery Orders'
        ]);
    }

    public function getOrderItems($id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        $html = view('components.order-items', compact('order'))->render();
        return response()->json(['html' => $html]);
    }

    public function delivery_histories(Request $request)
    {
        $user = User::getCurrentUser();

        if ($request->ajax()) {
            $query = Order::with([
                'user',
                'items.product',
                'delivery.histories' => fn($q) => $q->latest('logged_at')
            ])
                ->whereHas(
                    'delivery',
                    fn($q) =>
                    $q->where('delivery_rider_id', $user->id)
                )
                ->latest();

            return datatables()->of($query)
                ->addColumn('order_number', fn($pr) => $pr->order_number ?? 'N/A')
                ->addColumn('customer_name', fn($pr) => optional($pr->user)->name ?? 'N/A')
                ->addColumn('total_items', fn($pr) => $pr->items->sum('quantity'))
                ->addColumn('grand_total', function ($pr) {
                    $total = $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
                    return '₱' . number_format($total, 2);
                })
                ->addColumn('tracking_number', fn($pr) => $pr->delivery->tracking_number ?? 'N/A')
                ->addColumn('action', function ($pr) {
                    return '<button class="btn btn-sm btn-inverse-primary view-details-btn" data-id="' . $pr->delivery->id . '"><i class="link-icon" data-lucide="clock"></i> View History</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.admin.deliveryrider.v_deliveryHistory', [
            'page' => 'Delivery History'
        ]);
    }

    public function getDeliveryDetails(Order $order)
    {
        $order->load([
            'user',
            'items.product',
            'delivery.histories' => fn($q) => $q->latest('logged_at')
        ]);

        $html = view('components.delivery-history-details', compact('order'))->render();

        return response()->json(['html' => $html]);
    }

    public function delivery_location(Request $request)
    {
        $user = User::getCurrentUser();
        $status = $request->get('status');

        if ($request->ajax()) {
            $query = Order::with([
                'user',
                'b2bAddress',
                'items.product',
                'delivery'
            ])
                ->whereHas('delivery', function ($q) use ($user, $status) {
                    $q->where('delivery_rider_id', $user->id);

                    if (!empty($status)) {
                        $q->where('status', $status);
                    }
                })
                ->latest();

            return datatables()->of($query)
                ->addColumn('order_number', fn($order) => $order->order_number ?? 'N/A')
                ->addColumn('customer_name', fn($order) => optional($order->user)->name ?? 'N/A')
                ->addColumn('total_items', fn($order) => $order->items->sum('quantity') ?? 0)
                ->addColumn('grand_total', function ($order) {
                    $total = $order->items->sum(function ($item) {
                        return $item->quantity * ($item->product->price ?? 0);
                    });
                    return '₱' . number_format($total, 2);
                })
                ->addColumn('address', fn($order) => optional($order->b2bAddress)->full_address ?? 'N/A')
                ->addColumn('action', function ($order) {
                    $status = $order->delivery->status ?? 'unknown';

                    $messages = [
                        'pending'     => 'Waiting for admin to assign a rider',
                        'assigned'    => 'Waiting to be accepted by deliveryman',
                        'delivered'   => 'Delivery completed',
                        'cancelled'   => 'Delivery was cancelled',
                    ];

                    $badgeColors = [
                        'pending'     => 'warning',
                        'assigned'    => 'info',
                        'delivered'   => 'success',
                        'cancelled'   => 'danger',
                    ];

                    if ($status === 'on_the_way') {
                        $trackBtn = '<a href="' . route('deliveryrider.delivery.tracking', $order->delivery->id) . '" class="btn btn-sm btn-inverse-primary me-1">
                                        <i class="link-icon" data-lucide="truck"></i> Track
                                    </a>';

                        $markDeliveredBtn = '<button type="button" class="btn btn-sm btn-inverse-success mark-delivered-btn"
                                                data-id="' . $order->delivery->id . '">
                                                <i class="link-icon" data-lucide="check-circle"></i> Mark as Delivered
                                            </button>';

                        return $trackBtn . $markDeliveredBtn;
                    }

                    $badgeText = $messages[$status] ?? ucfirst($status);
                    $badgeClass = $badgeColors[$status] ?? 'secondary';

                    $badge = '<span class="badge bg-' . $badgeClass . '">' . $badgeText . '</span>';

                    if ($status === 'delivered' && $order->delivery->proof_delivery) {
                        $proofBtn = '<button class="btn btn-sm btn-inverse-info ms-2 view-proof-btn" 
                            data-proof="' . asset($order->delivery->proof_delivery) . '">
                        <i class="link-icon" data-lucide="eye"></i> View Proof
                     </button>';
                        return $badge . $proofBtn;
                    }

                    return $badge;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.admin.deliveryrider.v_deliveryLocation', [
            'page' => 'Delivery Location',
        ]);
    }

    public function delivery_tracking($id)
    {
        $delivery = Delivery::with(['order.b2bAddress'])->findOrFail($id);
        $customerLat = $delivery->order->b2bAddress->delivery_address_lat ?? null;
        $customerLng = $delivery->order->b2bAddress->delivery_address_lng ?? null;
        $deliveryManLat = $delivery->delivery_latitude;
        $deliveryManLng = $delivery->delivery_longitude;

        return view('pages.admin.deliveryrider.v_locationTracking', [
            'page' => 'Location Tracking',
            'delivery' => $delivery,
            'deliveryManLat' => $deliveryManLat,
            'deliveryManLng' => $deliveryManLng,
            'customerLat' => $customerLat,
            'customerLng' => $customerLng,
        ]);
    }

    public function logLocation(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $delivery = Delivery::findOrFail($id);

        DeliveryHistory::create([
            'delivery_id' => $delivery->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'remarks' => $request->remarks ?? 'Updated from geolocation',
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Location logged']);
    }

    public function delivery_tracking_sse($id)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        while (true) {
            $delivery = Delivery::with('latestHistory')->find($id);
            if ($delivery && $delivery->latestHistory) {
                $data = [
                    'lat' => $delivery->latestHistory->latitude,
                    'lng' => $delivery->latestHistory->longitude,
                ];
                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();
            }
            sleep(5); // Every 5 seconds
        }
    }

    public function upload_proof(Request $request)
    {
        $request->validate([
            'delivery_id' => 'required|exists:deliveries,id',
            'proof_delivery' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $delivery = Delivery::findOrFail($request->delivery_id);

        if ($request->hasFile('proof_delivery')) {
            $file = $request->file('proof_delivery');
            $destinationPath = public_path('assets/upload');
            $filename = uniqid('proof_') . '.' . $file->getClientOriginalExtension();

            if (!empty($delivery->proof_delivery)) {
                $existingPath = public_path($delivery->proof_delivery);
                if (file_exists($existingPath)) {
                    @unlink($existingPath);
                }
            }

            $file->move($destinationPath, $filename);

            $delivery->delivery_date = Carbon::now();
            $delivery->proof_delivery = 'assets/upload/' . $filename;
            $delivery->status = 'delivered';
            $delivery->save();

            return response()->json([
                'success' => true,
                'message' => 'Proof of delivery uploaded successfully.',
                'file_path' => $delivery->proof_delivery
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file was uploaded.'
        ], 400);
    }
}
