<?php

namespace App\Http\Controllers\SalesOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\PurchaseRequest;
use App\Models\CreditPayment;
use App\Models\Notification;
use App\Models\CreditPartialPayment;
use App\Models\PaidPayment;
use App\Models\User;
use App\Models\B2BAddress;
use App\Models\B2BDetail;

class ACPaymentController extends Controller
{

    public function paynow(Request $request)
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
        
        if ($user->role === 'salesofficer') {

        $codPR = PurchaseRequest::with('customer')
            ->where('cod_flg', 1)
            ->whereIn('status', ['delivered', 'invoice_sent']) // include both statuses
            ->get()
            ->mapWithKeys(function ($pr) {
                $formattedDate = Carbon::parse($pr->created_at)->format('M. j, Y');
                return [$pr->id => "{$pr->customer->name} - {$formattedDate}"];
            });


        if ($request->ajax()) {
            $query = PaidPayment::with([
                'purchaseRequest.customer',
                'purchaseRequest.items.product',
                'bank'
            ])->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', function ($payment) {
                    return optional($payment->purchaseRequest->customer)->name;
                })
                ->addColumn('bank_name', function ($payment) {
                    return '<p class="ms-3">' . (optional($payment->bank)->name ?? 'No bank (COD) Payment') . '</p>';
                })
                ->addColumn('paid_amount', function ($payment) {
                    return '₱' . number_format($payment->paid_amount, 2);
                })
                ->addColumn('paid_date', function ($payment) {
                    return optional($payment->paid_date)->format('F d, Y');
                })
                ->addColumn('proof_payment', function ($payment) {
                    return $payment->proof_payment
                        ? '<a href="' . asset($payment->proof_payment) . '" target="_blank">Show Proof Payment</a>'
                        : 'No proof (COD) Payment';
                })
                ->addColumn('reference_number', function ($payment) {
                    return '<p class="ms-3">' . ($payment->reference_number ?: 'No reference (COD) Payment') . '</p>';
                })
                ->addColumn('action', function ($payment) {
                    return '<span class="badge bg-info text-white">
                                <i class="link-icon" data-lucide="check"></i> Payment Approved
                            </span>';
                })
                //fix search
                ->filter(function ($query) use ($request) {
                    if ($search = $request->get('search')['value']) {
                        $query->whereHas('purchaseRequest.customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('bank', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                    }
                })
                //hanggang dito
                ->rawColumns(['bank_name', 'proof_payment', 'reference_number', 'action'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_paynow', [
            'page' => 'Pay-Now Payment Method',
            'cashDeliveries' => $codPR
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function manualPayment(Request $request)
    {
        $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'paid_amount' => 'required|integer',
            'paid_date' => 'required|date'
        ]);

        PaidPayment::create([
            'purchase_request_id' => $request->purchase_request_id,
            'paid_amount' => $request->paid_amount,
            'paid_date' => $request->paid_date,
            'status' => 'paid',
            'approved_date' => $request->paid_date,
            'approved_by' => auth()->id()
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Manual (COD) payment created successfully.',
        ]);
    }

    public function approvePayment($id)
    {
        $payment = PaidPayment::findOrFail($id);

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'This payment is already approved.'], 400);
        }

        $payment->status = 'paid';
        $payment->approved_at = Carbon::today();
        $payment->approved_by = auth()->id();
        $payment->save();

        return response()->json(['message' => 'Payment has been approved successfully.']);
    }

    public function paylater(Request $request)
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
        
        if ($user->role === 'salesofficer') {

        if ($request->ajax()) {
            $paymentType = $request->get('payment_type');

            if ($paymentType === 'straight') {
                $query = CreditPayment::with([
                    'purchaseRequest.customer',
                    'purchaseRequest.items.product',
                    'bank'
                ])->latest();

                return DataTables::of($query)
                    ->addColumn('customer_name', function ($payment) {
                        return optional($payment->purchaseRequest->customer)->name;
                    })
                    ->addColumn('bank_name', function ($payment) {
                        return '<p class="ms-3">' . (optional($payment->bank)->name ?? '--') . '</p>';
                    })
                    ->addColumn('paid_amount', function ($payment) {
                        return '₱' . number_format($payment->paid_amount, 2);
                    })
                    ->addColumn('paid_date', function ($payment) {
                        return '<p class="ms-3">' . (optional($payment->paid_date)->format('F d, Y') ?? '--') . '</p>';
                    })
                    ->addColumn('proof_payment', function ($payment) {
                        return $payment->proof_payment
                            ? '<a href="' . asset($payment->proof_payment) . '" target="_blank">Show Proof Payment</a>'
                            : '--';
                    })
                    ->addColumn('reference_number', function ($payment) {
                        return '<p class="ms-3">' . ($payment->reference_number ?: '--') . '</p>';
                    })
                    ->addColumn('status', function ($payment) {
                        return
                            '<span class="badge bg-warning text-dark">' . ucfirst($payment->status) . '</span>';
                    })
                    ->addColumn('action', function ($payment) {
                        return is_null($payment->proof_payment) && is_null($payment->reference_number)
                            ? '<span class="badge bg-danger text-white"> <i class="link-icon" data-lucide="clock"></i> Awaiting Payment</span>'
                            : ($payment->status === 'paid' ? '' :
                                '<button type="button" class="btn btn-sm btn-inverse-dark approve-payment p-2" data-id="' . $payment->id . '" style="font-size:11px">
                                    Approve
                                </button>
                                <button type="button" class="btn btn-sm btn-inverse-danger reject-payment p-2" data-id="' . $payment->id . '" data-paymenttype="straight" style="font-size:11px">
                                    Reject
                                </button>'
                            );
                    })
                    ->filter(function ($query) use ($request) {
                        if ($search = $request->input('search.value')) {
                            $query->whereHas('purchaseRequest.customer', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('bank', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhere('reference_number', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                        }
                    })
                    ->rawColumns(['bank_name', 'paid_date', 'proof_payment', 'reference_number', 'status', 'action'])
                    ->make(true);
            } elseif ($paymentType === 'partial') {
                $query = CreditPartialPayment::with([
                    'purchaseRequest.customer',
                    'purchaseRequest.items.product',
                    'bank'
                ])
                    ->selectRaw('purchase_request_id, bank_id, MAX(due_date) as last_due_date, SUM(amount_to_pay) as total_amount, status')
                    ->groupBy('purchase_request_id')
                    ->latest();

                return DataTables::of($query)
                    ->addColumn('customer_name', function ($payment) {
                        return optional($payment->purchaseRequest->customer)->name;
                    })
                    ->addColumn('total_amount', function ($payment) {
                        return '₱' . number_format($payment->total_amount, 2);
                    })
                    ->addColumn('due_date', function ($payment) {
                        return '<p>' . ($payment->last_due_date ? \Carbon\Carbon::parse($payment->last_due_date)->format('F d, Y') : '--') . '</p>';
                    })
                    ->addColumn('action', function ($payment) {
                        return '<button type="button" class="btn btn-sm btn-inverse-dark partial-payment-list p-2" data-id="' . $payment->purchase_request_id . '" style="font-size:11px">
                                <i class="link-icon" data-lucide="view"></i> Show Payment List</button>';
                    })
                    ->filter(function ($query) use ($request) {
                        if ($search = $request->input('search.value')) {
                            $query->whereHas('purchaseRequest.customer', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('bank', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhere('reference_number', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                        }
                    })
                    ->rawColumns(['total_amount', 'due_date', 'action'])
                    ->make(true);
            }
        }

        return view('pages.admin.salesofficer.v_paylater', [
            'page' => 'Pay-Later Payment Method',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function approvePaylaterPayment($id)
    {
        $payment = CreditPayment::findOrFail($id);

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'This payment is already approved.'], 400);
        }

        $customerPR = PurchaseRequest::findOrFail($payment->purchase_request_id);

        $payment->update([
            'status' => 'paid',
            'approved_at' => Carbon::today(),
            'approved_by' => auth()->id()
        ]);

        $user = User::findOrFail($customerPR->customer_id);
        $user->update([
            'credit_limit' => min($user->credit_limit + $payment->paid_amount, 300000)
        ]);

        return response()->json(['message' => 'Payment has been approved successfully.']);
    }

    public function paylaterPartial($id)
    {
        $payments = CreditPartialPayment::where('purchase_request_id', $id)
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount_to_pay' => $payment->amount_to_pay,
                    'due_date_formatted' => $payment->due_date
                        ? Carbon::parse($payment->due_date)->format('F j, Y')
                        : null,
                    'status' => $payment->status,
                    'date_paid' => $payment->paid_date
                        ? Carbon::parse($payment->paid_date)->format('F j, Y')
                        : null,
                    'paid_amount' => $payment->paid_amount,
                    'proof_payment' => $payment->proof_payment
                        ? asset($payment->proof_payment)
                        : null,
                    'reference_number' => $payment->reference_number,
                ];
            });

        return response()->json($payments);
    }

    public function reject_payment($id)
    {
        $payment = null;
        $paymentType = request()->input('paymentType');

        if ($paymentType === 'straight') {
            $payment = CreditPayment::findOrFail($id);
        } elseif ($paymentType === 'partial') {
            $payment = CreditPartialPayment::findOrFail($id);
        }

        if ($payment->status === 'reject') {
            return response()->json(['message' => 'This payment is already rejected.'], 400);
        }

        $payment->status = 'reject';
        $payment->notes = request()->input('reason');
        $payment->save();

        // ✅ Get the B2B user who owns the PR
    $purchaseRequest = PurchaseRequest::find($payment->purchase_request_id);

    if ($purchaseRequest && $purchaseRequest->customer_id) {
        $b2bUserId = $purchaseRequest->customer_id;

Notification::create([
    'user_id' => $b2bUserId,
    'type'    => 'payment_rejected',
    'message' => 'Your payment for your purchase request <strong>#' . 
        e($payment->purchase_request_id) . 
        '</strong> with the reference number <strong>' . 
        e($payment->reference_number ?? 'N/A') . 
        '</strong> has been rejected. <br><strong>Reason:</strong> ' . 
        e($payment->notes ?? $reason ?? 'No reason provided'),
]);
} else {
    \Log::warning('❌ Payment rejection notification failed: No valid B2B user for payment #' . $payment->id);
}
        return response()->json(['message' => 'Payment has been rejected successfully.']);
    }

    public function approvePartialPaylaterPayment($id)
    {
        $payment = CreditPartialPayment::findOrFail($id);

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Payment already approved.'], 400);
        }

        $customerPR = PurchaseRequest::findOrFail($payment->purchase_request_id);

        $payment->update([
            'status' => 'paid',
            'approved_at' => Carbon::today(),
            'approved_by' => auth()->id()
        ]);

        $user = User::findOrFail($customerPR->customer_id);
        $user->update([
            'credit_limit' => min($user->credit_limit + $payment->paid_amount, 300000)
        ]);

        return response()->json([
            'message' => 'Payment approved successfully.',
            'pp_id' => $payment->id
        ]);
    }

public function account_receivable(Request $request)
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
        
        if ($user->role === 'salesofficer') {

    $today = Carbon::today();
    
    // Statuses that represent an outstanding AR balance for the payment records
    $payment_statuses = array('pending', 'reject', 'unpaid');
    
    // Purchase Request statuses that trigger AR (after delivery/invoicing)
    $ar_trigger_statuses = ['delivered', 'invoice_sent'];

    // Filter to ensure the parent PurchaseRequest status is one of the AR trigger statuses
    $arTriggerPrFilter = fn($q) => $q->whereIn('status', $ar_trigger_statuses);

    // --- OVERALL TOTALS CALCULATION (Keep as is, since it was mostly correct) ---

    // Total Pending Straight
    $totalPendingStraight = CreditPayment::with('purchaseRequest:id,credit_amount')
        ->whereIn('status', $payment_statuses)
        ->whereHas('purchaseRequest', $arTriggerPrFilter)
        ->get()
        ->sum(function ($payment) {
            return $payment->purchaseRequest->credit_amount ?? 0;
        });
        
    // Total Pending Partial
    $totalPendingPartial = CreditPartialPayment::whereIn('status', $payment_statuses)
        ->whereHas('purchaseRequest', $arTriggerPrFilter)
        ->sum('amount_to_pay');

    $totalPending = $totalPendingStraight + $totalPendingPartial;

    // Total Overdue Straight
    $totalOverDueStraight = CreditPayment::with('purchaseRequest:id,credit_amount')
        ->where('status', 'overdue')
        ->whereDate('due_date', '<', $today)
        ->whereHas('purchaseRequest', $arTriggerPrFilter)
        ->get()
        ->sum(function ($payment) {
            return $payment->purchaseRequest->credit_amount ?? 0;
        });

    // Total Overdue Partial
    $totalOverDuePartial = CreditPartialPayment::where('status', 'overdue')
        ->whereDate('due_date', '<', $today)
        ->whereHas('purchaseRequest', $arTriggerPrFilter)
        ->sum('amount_to_pay');

    $totalOverDue = $totalOverDueStraight + $totalOverDuePartial;

    $totalBalance = $totalPending + $totalOverDue;

    // --- DATATABLES LOGIC: REPLACEMENT STARTS HERE ---
    
    if ($request->ajax()) {
        
        // Define the filter function to be used in 'with' and 'whereHas'
        $payLaterArFilter = function($q) use ($ar_trigger_statuses) {
            $q->whereIn('credit_payment_type', ['Straight Payment', 'Partial Payment'])
              ->whereIn('status', $ar_trigger_statuses);
        };

        // 1. Filter the User list: Only users who have AT LEAST ONE AR-ELIGIBLE PR
        // This ensures only relevant customers are queried.
        $customersQuery = User::whereHas('purchaseRequests', $payLaterArFilter)
        
        // 2. Eager Load only the relevant PRs (and their payments) for calculation
        // This is the CRUCIAL part: It filters the data retrieved by the 'with' clause.
        ->with(['purchaseRequests' => $payLaterArFilter, // <-- Filters the PRs collection
               'purchaseRequests.creditPayment', 
               'purchaseRequests.creditPartialPayments']);

        return DataTables::of($customersQuery)
            // The logic inside these addColumn closures now correctly operates ONLY 
            // on the filtered 'purchaseRequests' collection.
            ->addColumn('customer_name', function ($customer) {
                return $customer->name; // Changed from customer_name to name as per your model convention
            })
// INSIDE public function account_receivable(Request $request)
->addColumn('overdue', function ($customer) use ($today, $ar_trigger_statuses) {
    // The PRs here are ALREADY filtered by $payLaterArFilter

    // Overdue Straight: Uses the PR's credit_amount
    $overdueStraight = $customer->purchaseRequests
        ->filter(fn($pr) => 
            $pr->creditPayment && 
            $pr->creditPayment->status === 'overdue' && 
            $pr->creditPayment->due_date < $today
        )
        ->sum('credit_amount'); // ✅ Uses full credit_amount from PR

    // Overdue Partial: Uses the sum of 'amount_to_pay' from partial records
    $overduePartial = $customer->purchaseRequests
        ->flatMap(fn($pr) => $pr->creditPartialPayments)
        ->filter(fn($payment) => $payment->status === 'overdue' && $payment->due_date < $today)
        ->sum('amount_to_pay'); // ✅ Uses amount_to_pay from partials

    $totalOverdue = $overdueStraight + $overduePartial;

    return $totalOverdue;
})
// INSIDE public function account_receivable(Request $request)
->addColumn('balance', function ($customer) use ($today, $payment_statuses) {
    // NOTE: $customer->purchaseRequests collection ONLY contains AR-ELIGIBLE PRs.

    // 1. Pending Straight: Sum of PR credit_amount for pending straight payments
    $pendingStraight = $customer->purchaseRequests
        ->filter(fn($pr) => 
            $pr->credit_payment_type === 'Straight Payment' && // Explicitly check type
            $pr->creditPayment && 
            in_array($pr->creditPayment->status, $payment_statuses)
        )
        ->sum('credit_amount'); // Sum the full PR amount

    // 2. Pending Partial: Sum of amount_to_pay for pending partial payments
    $pendingPartial = $customer->purchaseRequests
        ->filter(fn($pr) => $pr->credit_payment_type === 'Partial Payment') // Explicitly check type
        ->flatMap(fn($pr) => $pr->creditPartialPayments)
        ->filter(fn($payment) => in_array($payment->status, $payment_statuses))
        ->sum('amount_to_pay');
    
    // 3. Overdue Straight
    $overdueStraight = $customer->purchaseRequests
        ->filter(fn($pr) => 
            $pr->credit_payment_type === 'Straight Payment' && // Explicitly check type
            $pr->creditPayment && 
            $pr->creditPayment->status === 'overdue' && 
            $pr->creditPayment->due_date < $today
        )
        ->sum('credit_amount');

    // 4. Overdue Partial
    $overduePartial = $customer->purchaseRequests
        ->filter(fn($pr) => $pr->credit_payment_type === 'Partial Payment') // Explicitly check type
        ->flatMap(fn($pr) => $pr->creditPartialPayments)
        ->filter(fn($payment) => $payment->status === 'overdue' && $payment->due_date < $today)
        ->sum('amount_to_pay');

    // Final Balance is the sum of all pending and overdue amounts
    $totalBalance = $pendingStraight + $pendingPartial + $overdueStraight + $overduePartial;

    return $totalBalance;
})
            ->addColumn('action', function ($customer) {
                return '<button class="btn btn-sm btn-inverse-dark view-details" data-userid="' . $customer->id . '" style="font-size:11px;">View Details</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    // --- DATATABLES LOGIC: REPLACEMENT ENDS HERE ---

    // --- NON-AJAX VIEW DATA (Still needs filtering) ---
    
    return view('pages.admin.salesofficer.v_accountreceivable', [ 
        'page' => 'Account Receivable',
        'totalOverDue' => $totalOverDue,
        'totalBalance' => $totalBalance,
    ]);}
    return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
}

    public function account_receivable_pr($userid, Request $request)
    {
        $type = $request->query('type', 'straight'); // default = straight
        $ar_trigger_statuses = ['delivered', 'invoice_sent'];
        $customer = User::find($userid);

        if (!$customer) {
            return response()->json(['prLists' => []]);
        }

        $prType = $type === 'straight' ? 'Straight Payment' : 'Partial Payment';

        $purchaseRequests = $customer->purchaseRequests()
            ->where('customer_id', $userid)
            ->where('credit_payment_type', $prType)
            ->whereIn('status', $ar_trigger_statuses) // ✅ This is the correct filter
            ->get();

        if ($purchaseRequests->isEmpty()) {
            return response()->json(['prLists' => []]);
        }

        $prLists = $purchaseRequests->map(function ($pr) {
            return [
                'pr_id' => $pr->id,
                'invoice_number' => 'INV-' . str_pad($pr->id, 5, '0', STR_PAD_LEFT),
                'credit_amount' => number_format($pr->credit_amount ?? 0),
                'status' => $pr->status ? str_replace('_', ' ', $pr->status) : '',
                'created_at' => $pr->created_at ? $pr->created_at->format('d F Y') : null,
            ];
        });

        return response()->json(['prLists' => $prLists]);
    }

    public function account_receivable_details($prid)
    {
        $today = Carbon::today();

        $purchaseRequest = PurchaseRequest::with(['customer', 'creditPayment', 'creditPartialPayments'])
            ->where('id', $prid)
            ->first();

        if (!$purchaseRequest) {
            return response()->json(['error' => 'PR not found'], 404);
        }

        $pendingStraight = 0;
        $overdueStraight = 0;
        $pendingPartial = 0;
        $overduePartial = 0;

        // Straight Payment
        if ($purchaseRequest->creditPayment) {
            if ($purchaseRequest->creditPayment->status === 'pending') {
                $pendingStraight = $purchaseRequest->credit_amount;
            }

            if (
                $purchaseRequest->creditPayment->status === 'overdue' &&
                $purchaseRequest->creditPayment->due_date < $today
            ) {
                $overdueStraight = $purchaseRequest->credit_amount;
            }
        }

        // Partial Payments
        if ($purchaseRequest->creditPartialPayments->count()) {
            $pendingPartial = $purchaseRequest->creditPartialPayments
                ->where('status', 'pending')
                ->sum('amount_to_pay');

            $overduePartial = $purchaseRequest->creditPartialPayments
                ->where('status', 'overdue')
                ->where('due_date', '<', $today)
                ->sum('amount_to_pay');
        }

        // Assign based on payment type
        $pending = 0;
        $overdue = 0;
        $balance = 0;

        if ($purchaseRequest->credit_payment_type === 'Straight Payment') {
            $pending = $pendingStraight;
            $overdue = $overdueStraight;
            $balance = $pending + $overdue;
        } elseif ($purchaseRequest->credit_payment_type === 'Partial Payment') {
            $pending = $pendingPartial;
            $overdue = $overduePartial;
            $balance = $pending + $overdue;
        }

        $customer = $purchaseRequest->customer;

        $customerAddress = B2BAddress::where('user_id', $customer->id)
            ->where('status', 'active')
            ->first();

        $customerRequirement = B2BDetail::where('user_id', $customer->id)
            ->where('status', 'approved')
            ->first();

        return response()->json([
            'customer' => [
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_creditlimit' => number_format($customer->credit_limit, 2),
                'pending' => number_format($pending, 2),
                'overdue' => number_format($overdue, 2),
                'balance' => number_format($balance, 2),
                'pr_id' => $purchaseRequest->id,
                'credit_payment_type' => $purchaseRequest->credit_payment_type,
            ],
            'customerAddress' => $customerAddress,
            'customerRequirements' => $customerRequirement
        ]);
    }

    public function account_receivable_payments($prid, Request $request)
    {
        $type = $request->query('type', 'straight');

        $purchaseRequest = PurchaseRequest::with(['customer'])->where('id', $prid)->first();

        if (!$purchaseRequest) {
            return response()->json(['payments' => []]);
        }

        if ($type === 'partial') {
            $payments = $purchaseRequest->creditPartialPayments()->get();
        } else {
            $payments = $purchaseRequest->creditPayment ? collect([$purchaseRequest->creditPayment]) : collect();
        }

        $invoiceNumber = 'INV-' . str_pad($purchaseRequest->id, 5, '0', STR_PAD_LEFT);

        $payments = $payments->map(function ($payment) use ($invoiceNumber) {
            $payment->invoice_number = $invoiceNumber;
            return $payment;
        });

        return response()->json(['payments' => $payments]);
    }
    public function rejectedIndex(Request $request) {
        // permission/role check same as your other methods
        if (!Auth::check() || Auth::user()->role !== 'salesofficer') {
            return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
        }

        return view('pages.admin.salesofficer.v_historypaylater', [
            'page' => 'Rejected Payments'
        ]);
    }
    public function rejectedData(Request $request) {
        if (!Auth::check() || Auth::user()->role !== 'salesofficer') {
            abort(403);
        }

        $paymentType = $request->get('payment_type', 'straight');

        if ($paymentType === 'straight') {
            $query = CreditPayment::with(['purchaseRequest.customer', 'bank'])
                ->where('status', 'reject')
                ->latest();
            
            return DataTables::of($query)
                ->addColumn('customer_name', fn($p) => optional($p->purchaseRequest->customer)->name)
                ->addColumn('bank_name', fn($p) => optional($p->bank)->name ?? '--')
                ->addColumn('paid_amount', fn($p) => '₱' . number_format($p->paid_amount, 2))
                ->addColumn('paid_date', fn($p) => optional($p->paid_date)->format('F d, Y') ?? '--')
                ->addColumn('proof_payment', function($p){
                    return $p->proof_payment ? '<a href="'.asset($p->proof_payment).'" target="_blank">Show Proof</a>' : '--';
                })
                ->addColumn('reference_number', fn($p) => $p->reference_number ?? '--')
                ->addColumn('status', fn($p) => '<span class="badge bg-danger">Rejected</span>')
                ->addColumn('rejection_reason', fn($p) => e($p->notes ?? '--'))
                ->rawColumns(['proof_payment','status'])
                    // ✅ This part enables searching by related model fields
                ->filter(function ($query) use ($request) {
                    $search = $request->get('search')['value'] ?? null;

                        if ($search) {
                            $query->where(function ($q) use ($search) {
                                $q->whereHas('purchaseRequest.customer', function ($c) use ($search) {
                                    $c->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('bank', function ($b) use ($search) {
                                    $b->where('name', 'like', "%{$search}%");
                                })
                                ->orWhere('reference_number', 'like', "%{$search}%")
                                ->orWhere('notes', 'like', "%{$search}%")
                                ->orWhere('paid_amount', 'like', "%{$search}%")
                                // ✅ moved inside $q closure
                                ->orWhereDate('paid_date', $search)
                                ->orWhereRaw("DATE_FORMAT(paid_date, '%M %d, %Y') LIKE ?", ["%{$search}%"]);
                            });
                        }
                    })
                ->make(true);
        } else { // partial
            $query = CreditPartialPayment::with(['purchaseRequest.customer', 'bank'])
                ->where('status', 'reject')
                ->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', fn($p) => optional($p->purchaseRequest->customer)->name)
                ->addColumn('bank_name', fn($p) => optional($p->bank)->name ?? '--')
                ->addColumn('amount_to_pay', fn($p) => '₱' . number_format($p->amount_to_pay, 2))
                ->addColumn('due_date', fn($p) => $p->due_date ? Carbon::parse($p->due_date)->format('F d, Y') : '--')
                ->addColumn('reference_number', fn($p) => $p->reference_number ?: '--')
                ->addColumn('proof_payment', function ($p) {
                    return $p->proof_payment
                        ? '<a href="' . asset($p->proof_payment) . '" target="_blank" class="text-primary">View Proof</a>'
                        : '<span class="text-muted">No proof uploaded</span>';
                })
                ->addColumn('rejection_reason', fn($p) => e($p->notes ?? 'No reason provided'))
                ->addColumn('status', fn() => '<span class="badge bg-danger">Rejected</span>')
                ->rawColumns(['proof_payment', 'status'])
                    // ✅ This part enables searching by related model fields
                ->filter(function ($query) use ($request) {
                    $search = $request->get('search')['value'] ?? null;

                        if ($search) {
                            $query->where(function ($q) use ($search) {
                                $q->whereHas('purchaseRequest.customer', function ($c) use ($search) {
                                    $c->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('bank', function ($b) use ($search) {
                                    $b->where('name', 'like', "%{$search}%");
                                })
                                ->orWhere('reference_number', 'like', "%{$search}%")
                                ->orWhere('notes', 'like', "%{$search}%")
                                ->orWhere('paid_amount', 'like', "%{$search}%")
                                // ✅ moved inside $q closure
                                ->orWhereDate('paid_date', $search)
                                ->orWhereRaw("DATE_FORMAT(paid_date, '%M %d, %Y') LIKE ?", ["%{$search}%"]);
                            });
                        }
                    })

                ->make(true);
        }
    }
    public function storeRejectedPayment(Request $request) {
        $request->validate([
            'payment_id' => 'required|integer',
            'payment_type' => 'required|string|in:straight,partial',
            'reason' => 'required|string|max:1000',
        ]);

        $paymentId = $request->payment_id;
        $paymentType = $request->payment_type;
        $reason = $request->reason;

        if ($paymentType === 'straight') {
            $payment = CreditPayment::find($paymentId);
        } else {
            $payment = CreditPartialPayment::find($paymentId);
        }

        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if ($payment->status === 'reject') {
            return response()->json(['message' => 'Payment already rejected.'], 400);
        }

        $payment->status = 'reject';
        $payment->notes = $reason;
        $payment->rejected_at = now();
        $payment->rejected_by = auth()->id();
        $payment->save();

        // create notification to B2B user if needed
        $purchaseRequest = PurchaseRequest::find($payment->purchase_request_id);
        if ($purchaseRequest && $purchaseRequest->customer_id) {
            Notification::create([
                'user_id' => $purchaseRequest->customer_id,
                'type' => 'payment_rejected',
                'message' => 'Your payment for PR #'.e($purchaseRequest->id).' (Ref: '.e($payment->reference_number ?? 'N/A').') was rejected. Reason: '.e($reason),
            ]);
        }

        return response()->json(['message' => 'Payment rejected successfully.']);
    }

}