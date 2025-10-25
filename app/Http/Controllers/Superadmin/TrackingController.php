<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\B2BAddress;
use App\Models\PurchaseRequest;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Notification;
use App\Models\B2BDetail;

class TrackingController extends Controller
{
    public function submittedPO(Request $request)
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
        
        if ($user->role === 'superadmin') {
        if ($request->ajax()) {
            $query = PurchaseRequest::with(['customer', 'items.product'])
                ->where('status', 'po_submitted')
                ->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', function ($pr) {
                    return optional($pr->customer)->name;
                })
                ->addColumn('total_items', function ($pr) {
                    return $pr->items->sum('quantity');
                })
                ->addColumn('grand_total', function ($pr) {
                    // ✅ Use PR-specific subtotal if exists
                    $subtotal = $pr->items->sum(function($item) {
                        return ($item->subtotal ?? ($item->unit_price * $item->quantity));
                    });

                    $vat = $subtotal * (($pr->vat ?? 0)/100);
                    $deliveryFee = $pr->delivery_fee ?? 0;

                    return '₱' . number_format($subtotal + $vat + $deliveryFee, 2);
                })

                ->addColumn('is_credit', function ($pr) {
                    return $pr->credit ? 'Yes' : 'No';
                })
                ->addColumn('credit_amount', function ($pr) {
                    return is_null($pr->credit_amount) ? '0.00' : '₱'. $pr->credit_amount;
                })
                ->addColumn('payment_method', function ($pr) {
                    return '<span>' . ($pr->payment_method === 'pay_now' ? 'Pay-Now' : 'Pay-Later') . '</span>';
                })
                ->addColumn('is_cod', function ($pr) {
                    return $pr->cod_flg ? 'Yes' : 'No';
                })
                // ->editColumn('created_at', function ($pr) {
                //     return Carbon::parse($pr->created_at)->format('Y-m-d H:i:s');
                // })
                ->addColumn('action', function ($pr) {
                    return '
                        <button type="button" class="btn btn-sm btn-inverse-primary view-pr p-2" data-id="' . $pr->id . '" title="View Purchase Request">
                            <i class="link-icon" data-lucide="eye"></i> View PO
                        </button>
                        <button type="button" class="btn btn-sm btn-inverse-success process-so p-2" data-id="' . $pr->id . '" title="Create Sales Order">
                            <i class="link-icon" data-lucide="plus-square"></i> Create SO
                        </button>
                    ';
                })
                //fix the search
                ->filter(function ($query) use ($request) {
                    if ($search = $request->get('search')['value']) {
                        // Allow searching by related customer name or payment method
                        $query->whereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                    }
                })
                //hanggang dito
                ->rawColumns(['is_credit', 'payment_method', 'action'])
                ->make(true);
        }

        return view('pages.superadmin.v_submittedPO', [
            'page' => 'Submitted Order',
            'pageCategory' => 'Tracking',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function show($id)
    {
        $quotation = PurchaseRequest::with(['items.product.productImages'])->findOrFail($id);

        $page = 'Purchase Order';
        $pageCategory = 'Tracking';
        $b2bReq = null;
        $b2bAddress = null;
        $salesOfficer = null;

        $superadmin = User::where('role', 'superadmin')->first();

        if ($quotation->customer_id) {
            $b2bReq = B2BDetail::where('user_id', $quotation->customer_id)
                ->where('status', 'approved')
                ->first();

            $b2bAddress = B2BAddress::where('user_id', $quotation->customer_id)
                ->where('status', 'active')
                ->first();
        }

        if ($quotation->prepared_by_id) {
            $salesOfficer = User::where('id', $quotation->prepared_by_id)->first();
        }

        return view('pages.superadmin.v_showPurchaseOrder', compact('quotation', 'b2bReq', 'b2bAddress', 'salesOfficer', 'superadmin', 'page', 'pageCategory'));
    }

    public function processSO($id)
    {
        DB::beginTransaction();

        try {
            $pr = PurchaseRequest::with(['items.product'])->findOrFail($id);

            if ($pr->status !== 'po_submitted') {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Purchase Request is not in a processable state.'
                ], 400);
            }

            // Get active delivery address of the user
            $activeAddress = B2BAddress::where('user_id', $pr->customer_id)
                ->where('status', 'active')
                ->first();

            if (!$activeAddress) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'No active delivery address found for this customer.'
                ], 400);
            }

            $total = 0;
            foreach ($pr->items as $item) {
                $total += $item->quantity * ($item->product->price ?? 0);
            }

            $orderNumber = 'REF' . ' ' . $pr->id . '-' . strtoupper(uniqid());

            // Create the Order
            $order = Order::create([
                'user_id' => $pr->customer_id,
                'order_number' => $orderNumber,
                'total_amount' => $total,
                'b2b_address_id' => $activeAddress->id,
                'ordered_at' => now()
            ]);

            foreach ($pr->items as $item) {
                $price = $item->product->price ?? 0;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $price * $item->quantity,
                ]);
            }

            $totalQuantity = $pr->items->sum('quantity');

            Delivery::create([
                'order_id' => $order->id,
                'quantity' => $totalQuantity,
            ]);

            $pr->status = 'so_created';
            $pr->save();

            Notification::create([
                'user_id' => $pr->customer_id,
                'type' => 'order',
                'message' => 'Your submitted PO has been processed. A sales order #' . $order->order_number . ' was created. <br><a href="' . route('b2b.quotations.review', $order->id) . '">Visit Link</a>',
            ]);

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Sales Order created successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to process sales order. ' . $e->getMessage()
            ], 500);
        }
    }

    public function deliveryPersonnel(Request $request)
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
        
        if ($user->role === 'superadmin') {

        $deliveryman_select = User::select('name', 'id')->where('role', 'deliveryrider')->get();

        if ($request->ajax()) {
            $query = Order::with([
                'user',
                'b2bAddress',
                'delivery.deliveryUser',
                'items.product'
            ])->whereHas('delivery')->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', function ($order) {
                    return optional($order->user)->name;
                })
                ->addColumn('customer_address', function ($order) {
                    return optional($order->b2bAddress)->full_address ?? 'No Address';
                })
                ->addColumn('delivery_man', function ($order) {
                    return optional(optional($order->delivery)->deliveryUser)->name ?? '<span class="text-danger">Not Assigned</span>';
                })
                ->addColumn('order_number', function ($order) {
                    return $order->order_number ?? '-';
                })
                ->addColumn('total_amount', function ($order) {
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

                ->addColumn('total_items', function ($order) {
                    return $order->items->sum('quantity');
                })
                ->addColumn('action', function ($order) {
                    $delivery = optional($order->delivery);
                    $deliveryId = $delivery->id;
                    $orderNumber = $order->order_number;
                    $status = $delivery->status;

                    if ($status === 'pending') {
                        return '
                            <button type="button"
                                    class="btn btn-sm btn-inverse-success assign-delivery p-2"
                                    data-id="' . $deliveryId . '"
                                    data-order-number="' . $orderNumber . '"
                                    title="Assign Delivery">
                                <i class="link-icon" data-lucide="user-check"></i> Assign Delivery
                            </button>
                        ';
                    }

                    return '<span class="badge bg-secondary text-capitalize">' . e($status ?? 'N/A') . '</span>';
                })

                ->rawColumns(['action', 'delivery_man', 'customer_address'])
                ->make(true);
        }

        return view('pages.superadmin.v_deliveryPersonnel', [
            'page' => 'Delivery Personnel',
            'pageCategory' => 'Tracking',
            'deliveryman_select' => $deliveryman_select
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function assignDeliveryPersonnel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|exists:deliveries,id',
            'pr_id' => 'required|exists:purchase_requests,id',
            'delivery_rider_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $delivery = Delivery::findOrFail($request->delivery_id);
            $delivery->delivery_rider_id = $request->delivery_rider_id;
            $delivery->status = 'assigned';
            $delivery->save();

            $pr = PurchaseRequest::findOrFail($request->pr_id);
            $pr->status = 'delivery_in_progress';
            $pr->save();

            // Notify delivery rider
            Notification::create([
                'user_id' => $request->delivery_rider_id,
                'type' => 'assignment',
                'message' => 'You have been assigned to deliver order #' . $delivery->order->order_number . '. <br><a href="' . route('home') . '">Visit Link</a>',
            ]);

            // Notify customer
            Notification::create([
                'user_id' => $pr->customer_id,
                'type' => 'delivery',
                'message' => 'Your order #' . $delivery->order->order_number . ' is now assigned for delivery. <br><a href="' . route('b2b.delivery.index') . '">Visit Link</a>',
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Delivery personnel assigned successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Failed to assign delivery personnel. ' . $e->getMessage(),
            ], 500);
        }
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
        
        if ($user->role === 'superadmin') {

        $status = $request->get('status');

        if ($request->ajax()) {
            $query = Order::with([
                'user',
                'b2bAddress',
                'items.product',
                'delivery'
            ])
                ->whereHas('delivery', function ($q) use ($status) {
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
                        $trackBtn = '<a href="' . route('tracking.delivery.tracking', $order->delivery->id) . '" class="btn btn-sm btn-inverse-primary me-1">
                                        <i class="link-icon" data-lucide="truck"></i> Track
                                    </a>';

                        // $markDeliveredBtn = '<button type="button" class="btn btn-sm btn-inverse-success mark-delivered-btn"
                        //                         data-id="' . $order->delivery->id . '">
                        //                         <i class="link-icon" data-lucide="check-circle"></i> Mark as Delivered
                        //                     </button>';

                        // return $trackBtn . $markDeliveredBtn;
                        return $trackBtn;
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

        return view('pages.superadmin.v_deliveryTracking', [
            'page' => 'Track Deliveries',
            'pageCategory' => 'Tracking',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function deliveryTracking($id)
    {
        $delivery = Delivery::with(['order.b2bAddress'])->findOrFail($id);
        $customerLat = $delivery->order->b2bAddress->delivery_address_lat ?? null;
        $customerLng = $delivery->order->b2bAddress->delivery_address_lng ?? null;
        $deliveryManLat = $delivery->delivery_latitude;
        $deliveryManLng = $delivery->delivery_longitude;

        return view('pages.superadmin.v_deliveryShowTracks', [
            'page' => 'Location Tracking',
            'pageCategory' => 'Tracking',
            'delivery' => $delivery,
            'deliveryManLat' => $deliveryManLat,
            'deliveryManLng' => $deliveryManLng,
            'customerLat' => $customerLat,
            'customerLng' => $customerLng,
        ]);
    }

    public function b2bRequirements(Request $request)
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
        
        if ($user->role === 'superadmin') {

        if ($request->ajax()) {
            $b2bRequirements = B2BDetail::with('user')->get();

            return DataTables::of($b2bRequirements)
                ->addColumn('customer_name', fn($requirement) => $requirement->user->name ?? 'Anonymous')
                ->addColumn('certificate_registration', function ($row) {
                    $filePath = $row->certificate_registration;
                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                    if ($fileExtension === 'pdf') {
                        return '<a href="' . asset($filePath) . '" target="_blank" class="btn btn-sm btn-info">
                            <i data-lucide="file-text"></i> View PDF
                        </a>';
                    } else {
                        return '<img src="' . asset($filePath) . '" alt="Certificate" class="img-thumbnail" width="150">';
                    }
                })
                ->addColumn('business_permit', function ($row) {
                    $filePath = $row->business_permit;
                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                    if ($fileExtension === 'pdf') {
                        return '<a href="' . asset($filePath) . '" target="_blank" class="btn btn-sm btn-info">
                            <i data-lucide="file-text"></i> View PDF
                        </a>';
                    } else {
                        return '<img src="' . asset($filePath) . '" alt="Business Permit" class="img-thumbnail" width="150">';
                    }
                })
                ->addColumn('status_badge', function ($row) {
                    $status = $row->status ?? 'pending'; // Default to 'pending' if null
                    $badgeClass = [
                        'pending' => 'bg-warning',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger'
                    ][$status] ?? 'bg-secondary';

                    $icon = [
                        'pending' => 'clock',
                        'approved' => 'check-circle',
                        'rejected' => 'x-circle'
                    ][$status] ?? 'alert-circle';

                    return '<span class="badge ' . $badgeClass . '">
                        <i data-lucide="' . $icon . '" class="w-4 h-4 mr-1"></i>' . ' ' . ucfirst($status) . '
                    </span>';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    // Approval/Rejection buttons
                    if ($row->status != 'approved') {
                        $buttons .= '
                        <button class="btn btn-sm btn-inverse-success mx-1 approve-btn" data-id="' . $row->id . '" title="Approve">
                            <i class="link-icon" data-lucide="check"></i>
                        </button>';
                    }

                    if ($row->status != 'rejected') {
                        $buttons .= '
                        <button class="btn btn-sm btn-inverse-danger reject-btn" data-id="' . $row->id . '" title="Reject">
                            <i class="link-icon" data-lucide="x"></i>
                        </button>';
                    }

                    return '<div class="d-flex">' . $buttons . '</div>';
                })
                ->rawColumns(['certificate_registration', 'business_permit', 'status_badge', 'action'])
                ->make(true);
        }

        return view('pages.superadmin.v_businessRequirement', [
            'page' => 'B2B Requirements',
            'pageCategory' => 'Tracking',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function updateStatus(Request $request)
    {
        $requirement = B2BDetail::findOrFail($request->id);
        $previousStatus = $requirement->status;
        $requirement->status = $request->status;
        $requirement->save();

        $statusMessages = [
            'approved' => 'Your business requirements have been approved. You can now proceed with purchases.',
            'rejected' => 'Your submitted business requirements need revision. Please check and resubmit.',
            'pending' => 'Your business requirements are under review.'
        ];

        // Only send notification if status actually changed
        if ($previousStatus !== $request->status) {
            Notification::create([
                'user_id' => $requirement->user_id,
                'type' => 'Business Requirements',
                'message' => $statusMessages[$request->status] ?? 'Your business requirements status has been updated.',
                'metadata' => [
                    'requirement_id' => $requirement->id,
                    'new_status' => $request->status,
                    'updated_at' => now()->toDateTimeString()
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'new_status' => $request->status
        ]);
    }
}