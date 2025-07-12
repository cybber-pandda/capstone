<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\CreditPayment;
use App\Models\Bank;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;

        $banks = Bank::get();

        if ($request->ajax()) {
            $creditPayments = CreditPayment::with(['purchaseRequest'])
                ->whereHas('purchaseRequest', function ($query) use ($userId) {
                    $query->where('customer_id', $userId);
                })
                ->latest()
                ->get();

            return DataTables::of($creditPayments)
                ->addColumn('credit_amount', function ($credit) {
                    return '₱' . number_format($credit->credit_amount, 2);
                })
                ->addColumn('paid_amount', function ($credit) {
                    return '₱' . number_format($credit->paid_amount, 2);
                })
                ->addColumn('due_date', function ($credit) {
                    return $credit->due_date->format('M d, Y');
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
                ->addColumn('remaining_balance', function ($credit) {
                    $balance = $credit->credit_amount - $credit->paid_amount;
                    return '₱' . number_format($balance, 2);
                })
                ->addColumn('action', function ($credit) {
                    $buttons = '';

                    // Pay Button (if not fully paid)
                    if ($credit->status !== 'paid') {
                        $buttons .= '<button class="btn btn-sm btn-primary pay-btn" 
                                        data-id="' . $credit->id . '"
                                        data-amount="' . (number_format($credit->credit_amount - $credit->paid_amount, 2)) . '">
                                        Pay Now
                                    </button> ';
                    }

                    // $buttons .= '<a href="' . route('b2b.credit.details', $credit->id) . '" 
                    //                 class="btn btn-sm btn-info">
                    //                 Details
                    //             </a>';
                    

                    return $buttons;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pages.b2b.v_credit', [
            'page' => 'My Credit',
            'banks' =>  $banks
        ]);
    }

    public function credit_payment(Request $request)
    {   

        $user = auth()->user();

        $request->validate([
            'credit_payment_id' => 'required|exists:credit_payments,id',
            'bank_id' => 'required|exists:banks,id',
            'proof_payment' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'paid_amount' => 'required|numeric|min:0.01'
        ]);

        $creditPayment = CreditPayment::findOrFail($request->credit_payment_id);

        // Verify the payment belongs to the authenticated user
        if ($creditPayment->purchaseRequest->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        // Handle file upload
        if ($request->hasFile('proof_payment')) {
            $file = $request->file('proof_payment');
            $filename = 'payment_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/upload/proofpayment'), $filename);
            $path = 'assets/upload/proofpayment' . $filename;
        }

        // Update credit payment
        $creditPayment->paid_amount += $request->paid_amount;

        if ($creditPayment->paid_amount >= $creditPayment->credit_amount) {
            $creditPayment->bank_id = $request->bank_id;
            $creditPayment->status = 'paid';
            $creditPayment->paid_date = now();
            $creditPayment->proof_payment = $path;
        } else {
            $creditPayment->bank_id = $request->bank_id;
            $creditPayment->status = 'partially_paid';
            $creditPayment->proof_payment = $path;
        }

        $creditPayment->save();

        return response()->json([
            'message' => 'Payment submitted successfully',
            'status' => $creditPayment->status,
            'remaining_balance' => $creditPayment->credit_amount - $creditPayment->paid_amount
        ]);
    }
}
