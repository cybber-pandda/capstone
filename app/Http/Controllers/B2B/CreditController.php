<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; //ito


use App\Models\CreditPayment;
use App\Models\CreditPartialPayment;
use App\Models\Bank;
use App\Models\User;

class CreditController extends Controller
{
    // public function index(Request $request)
    // {
    //     $user = auth()->user();
    //     $userId = $user->id;

    //     $banks = Bank::get();

    //     if ($request->ajax()) {
    //         $creditPayments = CreditPayment::with(['purchaseRequest'])
    //             ->whereHas('purchaseRequest', function ($query) use ($userId) {
    //                 $query->where('customer_id', $userId);
    //             })
    //             ->latest()
    //             ->get();

    //         return DataTables::of($creditPayments)
    //             ->addColumn('credit_amount', function ($credit) {
    //                 return '₱' . number_format($credit->credit_amount, 2);
    //             })
    //             ->addColumn('paid_amount', function ($credit) {
    //                 return '₱' . number_format($credit->paid_amount, 2);
    //             })
    //             ->addColumn('due_date', function ($credit) {
    //                 return $credit->due_date->format('M d, Y');
    //             })
    //             ->addColumn('paid_date', function ($credit) {
    //                 return $credit->paid_date ? $credit->paid_date->format('M d, Y') : '-';
    //             })
    //             ->addColumn('status', function ($credit) {
    //                 $statusClass = [
    //                     'unpaid' => 'badge-danger',
    //                     'partially_paid' => 'badge-warning',
    //                     'paid' => 'badge-success',
    //                     'overdue' => 'badge-dark'
    //                 ][$credit->status] ?? 'badge-secondary';

    //                 return '<span class="badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $credit->status)) . '</span>';
    //             })
    //             ->addColumn('remaining_balance', function ($credit) {
    //                 $balance = $credit->credit_amount - $credit->paid_amount;
    //                 return '₱' . number_format($balance, 2);
    //             })
    //             ->addColumn('action', function ($credit) {
    //                 $buttons = '';

    //                 // Pay Button (if not fully paid)
    //                 if ($credit->status !== 'paid') {
    //                     $buttons .= '<button class="btn btn-sm btn-primary pay-btn" 
    //                                     data-id="' . $credit->id . '"
    //                                     data-amount="' . (number_format($credit->credit_amount - $credit->paid_amount, 2)) . '">
    //                                     Pay Now
    //                                 </button> ';
    //                 }

    //                 // $buttons .= '<a href="' . route('b2b.credit.details', $credit->id) . '" 
    //                 //                 class="btn btn-sm btn-info">
    //                 //                 Details
    //                 //             </a>';


    //                 return $buttons;
    //             })
    //             ->rawColumns(['status', 'action'])
    //             ->make(true);
    //     }

    //     return view('pages.b2b.v_credit', [
    //         'page' => 'My Credit',
    //         'banks' =>  $banks
    //     ]);
    // }

    public function index(Request $request)
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

            // 2️⃣ If user is logged in → check their role
            $user = Auth::user();

            // Example role logic (adjust 'role' and role names to match your database)
            
        if ($user->role === 'b2b') {

        $user = auth()->user();
        $userId = $user->id;

        $banks = Bank::get();

        if ($request->ajax()) {
            $paymentType = $request->get('payment_type');

            if ($paymentType === 'straight') {
                $query = CreditPayment::with([
                    'purchaseRequest.customer',
                    'purchaseRequest.items.product',
                    'bank'
                ])
                    ->whereHas('purchaseRequest', function ($q) use ($userId) {
                        $q->where('customer_id', $userId)
                         ->whereIn('status', ['delivered','invoice_sent']); // ito
                    })
                    ->latest();

                return DataTables::of($query)
                    ->addColumn('credit_amount', function ($credit) {
                        return '₱' . number_format($credit->purchaseRequest->credit_amount, 2);
                    })
                    ->addColumn('paid_amount', function ($credit) {
                        return '₱' . number_format($credit->paid_amount, 2);
                    })
                    ->addColumn('due_date', function ($credit) {
                        return optional($credit->due_date)->format('M d, Y');
                    })
                    ->addColumn('paid_date', function ($credit) {
                        return $credit->paid_date ? $credit->paid_date->format('M d, Y') : '-';
                    })
                    ->addColumn('status', function ($credit) {
                        $statusClass = [
                            'unpaid' => 'badge-danger',
                            'partially_paid' => 'badge-warning',
                            'paid' => 'badge-success',
                            'overdue' => 'badge-dark'
                        ][$credit->status] ?? 'badge-secondary';

                        return '<span class="badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $credit->status)) . '</span>';
                    })
                    // ->addColumn('remaining_balance', function ($credit) {
                    //     $balance = $credit->credit_amount - $credit->paid_amount;
                    //     return '₱' . number_format($balance, 2);
                    // })
                    ->addColumn('action', function ($credit) {
                        $buttons = '';
                        if ($credit->status !== 'paid') {
                            $buttons .= '<button class="btn btn-sm btn-primary pay-btn" 
                                        data-id="' . $credit->id . '"
                                        data-creditamount="' . $credit->purchaseRequest->credit_amount . '"
                                        data-amount="' . number_format($credit->purchaseRequest->credit_amount - $credit->paid_amount, 2) . '">
                                        Pay Now
                                    </button>';
                        }
                        return $buttons;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            if ($paymentType === 'partial') {
                $query = CreditPartialPayment::with([
                    'purchaseRequest.customer',
                    'purchaseRequest.items.product',
                    'bank'
                ])
                    ->whereHas('purchaseRequest', function ($q) use ($userId) {
                        $q->where('customer_id', $userId)
                        ->whereIn('status', ['delivered','invoice_sent']); // ito
                    })
                    ->selectRaw('purchase_request_id, bank_id, MAX(due_date) as last_due_date, SUM(amount_to_pay) as total_amount, status')
                    ->groupBy('purchase_request_id', 'bank_id', 'status') //ito
                    ->latest();

                return DataTables::of($query)
                    ->addColumn('total_amount', function ($payment) {
                        return '₱' . number_format($payment->total_amount, 2);
                    })
                    ->addColumn('due_date', function ($payment) {
                        return $payment->last_due_date
                            ? Carbon::parse($payment->last_due_date)->format('M d, Y') //ito
                            : '--';
                    })
                    ->addColumn('status', function ($payment) {
                        $statusClass = [
                            'unpaid' => 'badge-danger',
                            'partially_paid' => 'badge-warning',
                            'paid' => 'badge-success',
                            'overdue' => 'badge-dark'
                        ][$payment->status] ?? 'badge-secondary';

                        return '<span class="badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $payment->status)) . '</span>';
                    })
                    ->addColumn('action', function ($payment) {
                        return '<button type="button" class="btn btn-sm btn-inverse-dark partial-payment-list p-2" data-id="' . $payment->purchase_request_id . '" style="font-size:11px">
                                <i class="link-icon" data-lucide="view"></i> Show Payment List
                            </button>';
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
        }

        return view('pages.b2b.v_credit', [
            'page' => 'My Credit',
            'banks' => $banks
        ]);
    }
    //for returning to the dashboard
         return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function credit_payment(Request $request)
    {
        $user = auth()->user();

        $paymentMap = [
            'straight' => [
                'model' => \App\Models\CreditPayment::class,
                'table' => 'credit_payments',
            ],
            'partial' => [
                'model' => \App\Models\CreditPartialPayment::class,
                'table' => 'credit_partial_payments',
            ],
        ];

        if (!isset($paymentMap[$request->credit_payment_type])) {
            return response()->json(['message' => 'Invalid credit payment type'], 400);
        }

        // Common validation rules
        $rules = [
            'bank_id' => 'required|exists:banks,id',
            'proof_payment' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'reference_number' => 'required',
            'paid_amount' => 'required|numeric',
        ];

        // Add type-specific validation
        $rules['credit_payment_id'] = "required|exists:{$paymentMap[$request->credit_payment_type]['table']},id";

        $request->validate($rules);

        // Retrieve the correct model
        $modelClass = $paymentMap[$request->credit_payment_type]['model'];
        $creditPayment = $modelClass::findOrFail($request->credit_payment_id);

        // Verify the payment belongs to the authenticated user
        if ($creditPayment->purchaseRequest->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        // Handle file upload
        $path = null;
        if ($request->hasFile('proof_payment')) {
            $file = $request->file('proof_payment');
            $filename = 'payment_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/upload/proofpayment'), $filename);
            $path = 'assets/upload/proofpayment/' . $filename;
        }

        // Update payment
        $creditPayment->update([
            'bank_id' => $request->bank_id,
            'paid_amount' => $request->paid_amount,
            'paid_date' => now(),
            'proof_payment' => $path,
            'reference_number' => $request->reference_number,
            'status' => $creditPayment->status === 'reject' ? 'pending' : 'pending'
        ]);

        return response()->json([
            'message' => 'Payment submitted successfully',
            'status' => $creditPayment->status,
        ]);
    }

    public function partialPayments(Request $request)
    {
        $creditId = $request->get('credit_id');

        $payments = CreditPartialPayment::where('purchase_request_id', $creditId)->select([
            'id',
            'paid_amount',
            'due_date',
            'amount_to_pay',
            'paid_date',
            'status',
            'proof_payment'
        ]);

        return datatables()->of($payments)
            ->editColumn('proof_payment', function($row) {
                return $row->proof_payment ? asset($row->proof_payment) : null;
            })
            ->make(true);
    }
}
