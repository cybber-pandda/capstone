<?php

namespace App\Http\Controllers\DeliveryRider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\DeliveryHistory;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\Inventory;
use App\Models\B2BDetail;
use App\Models\B2BAddress;
use App\Models\PaidPayment;
use App\Models\StockBatch;
use App\Models\PrReserveStock;
use App\Models\PurchaseRequestItem;

class DeliveryController extends Controller
{
    public function deliveryPickup(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);
        $delivery->status = 'on_the_way';
        $delivery->save();

        // Notify customer
        $user = $delivery->order?->user;
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'delivery',
                'message' => 'Your order #' . $delivery->order->order_number . ' is now on the way. <br><a href="' . route('b2b.delivery.track.index', $delivery->id) . '">Visit Link</a>',
            ]);
        }

        return response()->json(['message' => 'Status updated successfully.']);
    }

    public function deliveryOrders(Request $request)
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

        if ($user->role === 'deliveryrider') {

            $user = User::getCurrentUser();
            $status = $request->get('status');

            if ($request->ajax()) {
                $query = Order::with([
                    'user',
                    'b2bAddress',
                    'delivery.deliveryUser',
                    'items.product',
                ])
                    // ->whereHas('delivery', function ($q) use ($user, $status) {
                    //     $q->where('delivery_rider_id', $user->id);

                    //     if (!empty($status)) {
                    //         $q->where('status', $status);
                    //     }
                    // })
                    //new fix for pending
                    //fix the pending
                    ->whereHas('delivery', function ($q) use ($user, $status) {
                        if ($status === 'pending') {
                            // Show all deliveries that are still pending (no assigned rider)
                            $q->whereNull('delivery_rider_id')
                                ->where('status', 'pending');
                        } else {
                            // For other statuses, show only deliveries assigned to this rider
                            $q->where('delivery_rider_id', $user->id);

                            if (!empty($status)) {
                                $q->where('status', $status);
                            }
                        }
                    })
                    ->latest();

                return datatables()->of($query)
                    ->addColumn('order_number', fn($pr) => $pr->order_number ?? 'N/A')
                    ->addColumn('customer_name', fn($pr) => optional($pr->user)->name ?? 'N/A')
                    ->addColumn('total_items', fn($pr) => $pr->items->sum('quantity'))
                    ->addColumn('grand_total', fn($order) => $this->calculateGrandTotal($order))
                    ->editColumn('created_at', fn($pr) => $pr->created_at->format('Y-m-d H:i:s'))
                    ->addColumn('action', function ($pr) {
                        $btn = '<button class="btn btn-sm btn-inverse-info view-items-btn" data-id="' . $pr->id . '"><i class="link-icon" data-lucide="list"></i>Items</button> ';
                        //New show sales invoice
                        // Determine the purchaseRequestId: prefer an explicit relation/column, otherwise parse from order_number
                        $purchaseRequestId = null;
                        if (property_exists($pr, 'purchase_request_id') && !empty($pr->purchase_request_id)) {
                            $purchaseRequestId = $pr->purchase_request_id;
                        } elseif (!empty($pr->order_number) && preg_match('/REF (\d+)-/', $pr->order_number, $matches)) {
                            $purchaseRequestId = $matches[1];
                        }

                        if ($purchaseRequestId) {
                            $btn .= '<a href="' . route('deliveryrider.delivery.sales.inv', [
                                'prId' => $purchaseRequestId ?? 0,
                                'customerId' => $pr->user_id ?? 0
                            ]) . '" class="btn btn-sm btn-inverse-primary">Invoice</a>';
                        } else {
                            // optional: show disabled button or nothing
                            $btn .= '<button class="btn btn-sm btn-secondary" disabled title="No purchase request found">Invoice</button>';
                        }
                        return $btn;
                    })
                    //fix search
                    ->filter(function ($query) use ($request) {
                        if ($search = $request->get('search')['value']) {
                            $query->where(function ($q) use ($search) {
                                $q->where('order_number', 'like', "%{$search}%")
                                    ->orWhereHas('user', function ($sub) use ($search) {
                                        $sub->where('name', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('delivery', function ($sub) use ($search) {
                                        $sub->where('status', 'like', "%{$search}%");
                                    });
                            });
                        }
                    })

                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('pages.admin.deliveryrider.v_deliveryOrders', [
                'page' => 'Delivery Orders'
            ]);
        }
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    // public function getOrderItems($id)
    // {
    //     $order = Order::with('items.product')->findOrFail($id);

    //     // Compute discounted totals per item
    //     foreach ($order->items as $item) {
    //         $basePrice = $item->product->price ?? 0;
    //         $discountedPrice = ($item->product->discount ?? 0) == 0
    //             ? $basePrice
    //             : ($item->product->discounted_price ?? $basePrice);

    //         $item->final_price = $discountedPrice;
    //         $item->total_price = $item->quantity * $discountedPrice;
    //     }

    //     $html = view('components.order-items', compact('order'))->render();

    //     return response()->json(['html' => $html]);
    // }

    public function getOrderItems($id)
    {
        $order = Order::with('user')->findOrFail($id);

        $purchaseRequestItems = collect();

        // Extract PR ID from order_number
        if ($order?->order_number) {
            preg_match('/REF (\d+)-/', $order->order_number, $matches);
            if (isset($matches[1])) {
                $purchaseRequestId = $matches[1];

                // Get PR items with product info
                $purchaseRequestItems = PurchaseRequestItem::with('product')
                    ->where('purchase_request_id', $purchaseRequestId)
                    ->get();

                // Compute discounted totals per item if needed
                foreach ($purchaseRequestItems as $item) {
                    $basePrice = $item->unit_price ?? ($item->product->price ?? 0);
                    $discountedPrice = ($item->product->discount ?? 0) == 0
                        ? $basePrice
                        : ($item->product->discounted_price ?? $basePrice);

                    $item->final_price = $discountedPrice;
                    $item->total_price = $item->quantity * $discountedPrice;
                }
            }
        }

        $html = view('components.order-items', compact('order', 'purchaseRequestItems'))->render();

        return response()->json(['html' => $html]);
    }



    public function deliveryHistories(Request $request)
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

        if ($user->role === 'deliveryrider') {

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
                    ->addColumn('grand_total', fn($order) => $this->calculateGrandTotal($order))

                    ->addColumn('tracking_number', fn($pr) => $pr->delivery->tracking_number ?? 'N/A')
                    ->addColumn('action', function ($pr) {
                        return '<button class="btn btn-sm btn-inverse-primary view-details-btn" data-id="' . $pr->delivery->id . '"><i class="link-icon" data-lucide="clock"></i> View History</button>';
                    })
                    //fix search
                    ->filter(function ($query) use ($request) {
                        if ($search = $request->get('search')['value']) {
                            $query->where(function ($q) use ($search) {
                                $q->where('order_number', 'like', "%{$search}%")
                                    ->orWhereHas('user', function ($sub) use ($search) {
                                        $sub->where('name', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('delivery', function ($sub) use ($search) {
                                        $sub->where('status', 'like', "%{$search}%");
                                    });
                            });
                        }
                    })
                    //hanggangdito
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('pages.admin.deliveryrider.v_deliveryHistory', [
                'page' => 'Delivery History'
            ]);
        }
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    // public function getDeliveryDetails(Order $order)
    // {
    //     $order->load([
    //         'user',
    //         'items.product',
    //         'delivery.histories' => fn($q) => $q->latest('logged_at')
    //     ]);

    //     $html = view('components.delivery-history-details', compact('order'))->render();

    //     return response()->json(['html' => $html]);
    // }

    public function getDeliveryDetails(Order $order)
    {
        $order->load([
            'user',
            'delivery.histories' => fn($q) => $q->latest('logged_at')
        ]);

        $purchaseRequestItems = collect();

        // Extract PR ID from order_number
        if ($order?->order_number) {
            preg_match('/REF (\d+)-/', $order->order_number, $matches);
            if (isset($matches[1])) {
                $purchaseRequestId = $matches[1];
                $purchaseRequestItems = PurchaseRequestItem::with('product')
                    ->where('purchase_request_id', $purchaseRequestId)
                    ->get();
            }
        }

        $html = view('components.delivery-history-details', compact('order', 'purchaseRequestItems'))->render();

        return response()->json(['html' => $html]);
    }


    public function deliveryLocation(Request $request)
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

        if ($user->role === 'deliveryrider') {

            $user = User::getCurrentUser();
            $status = $request->get('status');

            if ($request->ajax()) {
                $query = Order::with([
                    'user',
                    'b2bAddress',
                    'items.product',
                    'delivery'
                ])
                    //fix the pending
                    ->whereHas('delivery', function ($q) use ($user, $status) {
                        if ($status === 'pending') {
                            // Show all deliveries that are still pending (no assigned rider)
                            $q->whereNull('delivery_rider_id')
                                ->where('status', 'pending');
                        } else {
                            // For other statuses, show only deliveries assigned to this rider
                            $q->where('delivery_rider_id', $user->id);

                            if (!empty($status)) {
                                $q->where('status', $status);
                            }
                        }
                    })
                    ->latest();

                return datatables()->of($query)
                    ->addColumn('order_number', fn($order) => $order->order_number ?? 'N/A')
                    ->addColumn('customer_name', fn($order) => optional($order->user)->name ?? 'N/A')
                    ->addColumn('total_items', fn($order) => $order->items->sum('quantity') ?? 0)
                    //bagong lagay for phone number
                    ->addColumn('contact_number', function ($order) {
                        // Get from B2B details if available
                        $b2bDetail = B2BDetail::where('user_id', $order->user_id)->first();

                        return $b2bDetail->contact_number
                            ?? $b2bDetail->contact_person_number
                            ?? 'N/A';
                    })
                    ->addColumn('grand_total', fn($order) => $this->calculateGrandTotal($order))
                    ->addColumn('address', fn($order) => optional($order->b2bAddress)->full_address ?? 'N/A')
                    ->addColumn('address_notes', fn($order) => optional($order->b2bAddress)->address_notes ?? 'N/A')
                    ->addColumn('action', function ($order) {
                        $status = $order->delivery->status ?? 'unknown';

                        $messages = [
                            'pending' => 'Waiting for admin to assign a rider',
                            'assigned' => 'Waiting to be accepted by deliveryman',
                            'delivered' => 'Delivery completed',
                            'cancelled' => 'Delivery was cancelled',
                        ];

                        $badgeColors = [
                            'pending' => 'warning',
                            'assigned' => 'info',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                        ];

                        if ($status === 'on_the_way') {
                            $trackBtn = '<a href="' . route('deliveryrider.delivery.tracking', $order->delivery->id) . '" class="btn btn-sm btn-inverse-primary me-1">
                                        <i class="link-icon" data-lucide="truck"></i> Track
                                    </a>';

                            $markDeliveredBtn = '<button type="button" class="btn btn-sm btn-inverse-success mark-delivered-btn mx-1"
                                                data-id="' . $order->delivery->id . '">
                                                <i class="link-icon" data-lucide="check-circle"></i> Mark as Delivered
                                            </button>';

                            // $cancelBtn = '<button type="button" class="btn btn-sm btn-inverse-danger cancel-delivery-btn"
                            //                 data-id="' . $order->delivery->id . '">
                            //                 <i class="link-icon" data-lucide="x"></i> Cancel
                            //             </button>';

                            $cancelBtn = '';

                            return $trackBtn . $markDeliveredBtn . $cancelBtn;
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
                    //fix search
                    ->filter(function ($query) use ($request) {
                        if ($search = $request->get('search')['value']) {
                            $query->where(function ($q) use ($search) {
                                $q->where('order_number', 'like', "%{$search}%")
                                    ->orWhereHas('user', function ($sub) use ($search) {
                                        $sub->where('name', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('b2bAddress', function ($sub) use ($search) {
                                        $sub->where('full_address', 'like', "%{$search}%")
                                            ->orWhere('address_notes', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('delivery', function ($sub) use ($search) {
                                        $sub->where('status', 'like', "%{$search}%");
                                    });
                            });
                        }
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('pages.admin.deliveryrider.v_deliveryLocation', [
                'page' => 'Delivery Location',
            ]);
        }
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function deliveryTracking($id)
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

    public function uploadProof(Request $request)
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

            if ($delivery->order?->order_number) {
                preg_match('/REF (\d+)-/', $delivery->order->order_number, $matches);
                if (isset($matches[1])) {
                    $purchaseRequestId = $matches[1];
                    $purchaseRequest = PurchaseRequest::find($purchaseRequestId);
                    if ($purchaseRequest) {
                        $purchaseRequest->status = 'delivered';
                        $purchaseRequest->save();
                        PrReserveStock::completeReservation($purchaseRequestId);
                    }
                }
            }

            // foreach ($delivery->order->items as $item) {
            //     Inventory::create([
            //         'product_id' => $item->product_id,
            //         'type' => 'out',
            //         'quantity' => $item->quantity,
            //         'reason' => 'sold',
            //     ]);
            // }

            $user = $delivery->order?->user;
            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'delivery',
                    'message' => 'Your order #' . $delivery->order->order_number . ' has been delivered. <br><a href="' . route('b2b.delivery.index') . '">Visit Link</a>',
                ]);
            }

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

    public function cancelDelivery(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        if ($delivery->status === 'delivered') {
            return response()->json(['message' => 'This delivery has already been completed.'], 400);
        }

        $delivery->status = 'cancelled';
        $delivery->delivery_remarks = $request->remarks ?? 'Cancelled by rider.';
        $delivery->save();

        // foreach ($delivery->order->items as $item) {
        //     Inventory::create([
        //         'product_id' => $item->product_id,
        //         'type' => 'in',
        //         'quantity' => $item->quantity,
        //         'reason' => 'Return stock cancelled',
        //     ]);
        // }

        if ($delivery->order?->order_number) {
            preg_match('/REF (\d+)-/', $delivery->order->order_number, $matches);
            if (isset($matches[1])) {
                $purchaseRequestId = $matches[1];
                PrReserveStock::releaseReservedStock($purchaseRequestId, 'cancelled');
            }
        }

        return response()->json(['message' => 'Delivery has been cancelled.']);
    }

    public function deliveryRatings(Request $request)
    {
        if (!Auth::check()) {
            $page = 'Sign In';
            $companysettings = DB::table('company_settings')->first();

            return response()
                ->view('auth.login', compact('page', 'companysettings'))
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        }

        $user = Auth::user();

        if ($user->role === 'deliveryrider') {
            $user = auth()->user(); // Delivery Rider
            $riderId = $user->id;

            if ($request->ajax()) {
                // Only fetch deliveries assigned to the current rider
                $query = \App\Models\Delivery::with([
                    'order.user',
                    'order.items.product',
                    'rating'
                ])
                    ->where('delivery_rider_id', $riderId)
                    ->has('order') // Ensure it has an order
                    ->latest();

                return datatables()->of($query)
                    ->addColumn('order_number', fn($delivery) => $delivery->order->order_number ?? 'N/A')
                    ->addColumn('customer_name', fn($delivery) => optional($delivery->order->user)->name ?? 'N/A')
                    ->addColumn('total_items', fn($delivery) => $delivery->order->items->sum('quantity') ?? 0)
                    ->addColumn('grand_total', function ($delivery) {
                        $order = $delivery->order;

                        $subtotal = 0;
                        $vatRate = 0;
                        $deliveryFee = 0;

                        if ($order && preg_match('/REF (\d+)-/', $order->order_number, $matches)) {
                            $purchaseRequestId = $matches[1];

                            // ✅ Get subtotal directly from purchase_request_items (official computation)
                            $subtotal = \DB::table('purchase_request_items')
                                ->where('purchase_request_id', $purchaseRequestId)
                                ->sum('subtotal');

                            // ✅ Get VAT and delivery fee from the PurchaseRequest
                            $purchaseRequest = \App\Models\PurchaseRequest::find($purchaseRequestId);
                            if ($purchaseRequest) {
                                $vatRate = $purchaseRequest->vat ?? 0;
                                $deliveryFee = $purchaseRequest->delivery_fee ?? 0;
                            }
                        }

                        // ✅ Calculate grand total using the same logic as other tables
                        $vatAmount = $subtotal * ($vatRate / 100);
                        $grandTotal = $subtotal + $vatAmount + $deliveryFee;

                        return '₱' . number_format($grandTotal, 2);
                    })
                    ->addColumn('rating', function ($delivery) {
                        $rating = $delivery->rating->rating ?? null;

                        if (!$rating) {
                            return '<span class="text-muted">Not rated</span>';
                        }

                        return collect(range(1, 5))->map(function ($i) use ($rating) {
                            return '<i data-lucide="star' . ($i <= $rating ? '' : '-off') . '" class="' . ($i <= $rating ? 'text-warning' : 'text-muted') . '"></i>';
                        })->implode('');
                    })
                    //fix search
                    ->filter(function ($query) use ($request) {
                        if ($search = $request->get('search')['value']) {
                            $query->where(function ($q) use ($search) {
                                $q->whereHas('order', function ($sub) use ($search) {
                                    $sub->where('order_number', 'like', "%{$search}%")
                                        ->orWhereHas('user', function ($userSub) use ($search) {
                                            $userSub->where('name', 'like', "%{$search}%");
                                        });
                                })
                                    ->orWhere('status', 'like', "%{$search}%");
                            });
                        }
                    })
                    ->rawColumns(['rating'])
                    ->make(true);
            }

            return view('pages.admin.deliveryrider.v_deliveryRating', [
                'page' => 'My Delivery Rating',
            ]);
        }
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    protected function calculateGrandTotal(Order $order): string
    {
        $purchaseRequestId = null;

        if (!empty($order->order_number) && preg_match('/REF (\d+)-/', $order->order_number, $matches)) {
            $purchaseRequestId = $matches[1];
        }

        $subtotal = 0;
        $vatRate = 0;
        $deliveryFee = 0;

        if ($purchaseRequestId) {
            // Use official subtotal from purchase_request_items
            $subtotal = DB::table('purchase_request_items')
                ->where('purchase_request_id', $purchaseRequestId)
                ->sum('subtotal');

            $purchaseRequest = PurchaseRequest::find($purchaseRequestId);
            if ($purchaseRequest) {
                $vatRate = $purchaseRequest->vat ?? 0;
                $deliveryFee = $purchaseRequest->delivery_fee ?? 0;
            }
        }

        $vatAmount = $subtotal * ($vatRate / 100);
        $grandTotal = $subtotal + $vatAmount + $deliveryFee;

        return '₱' . number_format($grandTotal, 2);
    }

    public function show_sales_inv($prId, $customerId)
    {
        $page = "Sales Invoice";
        $b2bReqDetails = null;
        $b2bAddress = null;
        $salesOfficer = null;
        $paidPR = null;

        $superadmin = User::where('role', 'superadmin')->first();

        $quotation = PurchaseRequest::with(['customer', 'items.product'])
            ->where('customer_id', $customerId)
            ->findOrFail($prId);

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

        return view('pages.admin.deliveryrider.v_sales_invoice', compact('quotation', 'page', 'b2bReqDetails', 'b2bAddress', 'salesOfficer', 'superadmin', 'paidPR'));
    }
}
