<?php

namespace App\Http\Controllers\SalesOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use App\Models\PurchaseRequest;
use App\Models\CreditPayment;
use App\Models\CreditPartialPayment;
use App\Models\PaidPayment;
use App\Models\User;
use App\Models\B2BAddress;
use App\Models\B2BDetail;

class ACPaymentController extends Controller
{

    public function paynow(Request $request)
    {

        $codPR = PurchaseRequest::with('customer')
            ->where('cod_flg', 1)
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
                    return is_null($payment->proof_payment) && is_null($payment->reference_number)
                        ? '<button type="button" class="btn btn-sm btn-inverse-dark approve-payment p-2" data-id="' . $payment->id . '" style="font-size:11px">
                            <i class="link-icon" data-lucide="copy-check"></i> Approve Payment
                        </button>' : '<span class="badge bg-info text-white"> <i class="link-icon" data-lucide="check"></i> Payment Approved</span>';
                })
                ->rawColumns(['bank_name', 'proof_payment', 'reference_number', 'action'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_paynow', [
            'page' => 'Pay-Now Payment Method',
            'cashDeliveries' => $codPR
        ]);
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
                            ? '<span class="badge bg-danger text-white"> <i class="link-icon" data-lucide="clock"></i> Waiting for B2B Payment</span>'
                            : ($payment->status === 'paid' ? '' :
                                '<button type="button" class="btn btn-sm btn-inverse-dark approve-payment p-2" data-id="' . $payment->id . '" style="font-size:11px">
                                    Approve
                                </button>
                                <button type="button" class="btn btn-sm btn-inverse-danger reject-payment p-2" data-id="' . $payment->id . '" data-paymenttype="straight" style="font-size:11px">
                                    Reject
                                </button>'
                            );
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
                    ->rawColumns(['total_amount', 'due_date', 'action'])
                    ->make(true);
            }
        }

        return view('pages.admin.salesofficer.v_paylater', [
            'page' => 'Pay-Later Payment Method',
        ]);
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
        $today = Carbon::today();
        
        $status = array('pending', 'reject', 'unpaid');

        // Overall totals
        $totalPendingStraight = CreditPayment::whereIn('status', $status)->sum('paid_amount');
        $totalPendingPartial  = CreditPartialPayment::whereIn('status', $status)->sum('amount_to_pay');
        $totalPending = $totalPendingStraight + $totalPendingPartial;

        $totalOverDueStraight = CreditPayment::with('purchaseRequest:id,credit_amount')
            ->where('status', 'overdue')
            ->whereDate('due_date', '<', $today)
            ->get()
            ->sum(function ($payment) {
                return $payment->purchaseRequest->credit_amount ?? 0;
            });

        $totalOverDuePartial = CreditPartialPayment::where('status', 'overdue')
            ->whereDate('due_date', '<', $today)
            ->sum('amount_to_pay');

        $totalOverDue = $totalOverDueStraight + $totalOverDuePartial;

        $totalBalance = $totalPending + $totalOverDue;

        $customers = User::whereHas('purchaseRequests')
            ->with(['purchaseRequests.creditPayment', 'purchaseRequests.creditPartialPayments'])
            ->get()
            ->map(function ($customer) use ($today, $status) {

                $pendingStraight = $customer->purchaseRequests
                    ->filter(fn($pr) => $pr->creditPayment && in_array($pr->creditPayment->status, $status))
                    ->sum('credit_amount');

                $pendingPartial = $customer->purchaseRequests
                    ->flatMap(fn($pr) => $pr->creditPartialPayments)
                    ->filter(fn($payment) => in_array($payment->status, $status))
                    ->sum('amount_to_pay');

                $overdueStraight = $customer->purchaseRequests
                    ->filter(
                        fn($pr) =>
                        $pr->creditPayment &&
                            $pr->creditPayment->status === 'overdue' &&
                            $pr->creditPayment->due_date < $today
                    )
                    ->sum('credit_amount');

                $overduePartial = $customer->purchaseRequests
                    ->flatMap(fn($pr) => $pr->creditPartialPayments)
                    ->filter(
                        fn($payment) =>
                        $payment->status === 'overdue' &&
                            $payment->due_date < $today
                    )
                    ->sum('amount_to_pay');

                $pending = $pendingStraight + $pendingPartial;
                $overdue = $overdueStraight + $overduePartial;
                $balance = $pending + $overdue;
                $firstPrId = $customer->purchaseRequests->first()?->id;

                return [
                    'user_id'       => $customer->id,
                    'customer_name' => $customer->name,
                    'pending'       => $pending,
                    'overdue'       => $overdue,
                    'balance'       => $balance,
                    'pr_id'         => $firstPrId,
                ];
            });

        if ($request->ajax()) {
            return DataTables::of($customers)
                ->addColumn('pending', fn($row) => '₱' . number_format($row['pending'], 2))
                ->addColumn('overdue', fn($row) => '₱' . number_format($row['overdue'], 2))
                ->addColumn('balance', fn($row) => '₱' . number_format($row['balance'], 2))
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary view-details" data-userid="' . e($row['user_id']) . '" data-prid="' . e($row['pr_id']) . '">View Details</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_accountreceivable', [
            'page'          => 'Account Receivable',
            // 'totalPending'  => $totalPending,
            'totalOverDue'  => $totalOverDue,
            'totalBalance'  => $totalBalance,
            'customers'     => $customers,
        ]);
    }

    public function account_receivable_pr($userid, Request $request)
    {
        $type = $request->query('type', 'straight'); // default = straight
        $customer = User::find($userid);

        if (!$customer) {
            return response()->json(['prLists' => []]);
        }

        $prType = $type === 'straight' ? 'Straight Payment' : 'Partial Payment';

        $purchaseRequests = $customer->purchaseRequests()
            ->where('customer_id', $userid)
            ->where('credit_payment_type', $prType)
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
}
