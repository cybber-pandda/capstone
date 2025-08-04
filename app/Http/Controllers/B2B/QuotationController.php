<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use App\Models\PurchaseRequest;
use App\Models\B2BAddress;
use App\Models\Notification;
use App\Models\User;
use App\Models\Bank;

class QuotationController extends Controller
{
    public function review(Request $request)
    {
        $userId = auth()->id();

        if ($request->ajax()) {
            $query = PurchaseRequest::with(['customer', 'items.product'])
                ->whereIn('status', ['quotation_sent', 'po_submitted', 'so_created'])
                ->where('customer_id', $userId)
                ->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', function ($pr) {
                    return optional($pr->customer)->name;
                })
                ->addColumn('total_items', function ($pr) {
                    return $pr->items->sum('quantity');
                })
                ->addColumn('grand_total', function ($pr) {
                    $subtotal = $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
                    $vatRate = $pr->vat ?? 0; // VAT percentage
                    $vatAmount = $subtotal * ($vatRate / 100);
                    $deliveryFee = $pr->delivery_fee ?? 0;
                    $total = $subtotal + $vatAmount + $deliveryFee;

                    return '₱' . number_format($total, 2);
                })
                ->editColumn('created_at', function ($pr) {
                    return Carbon::parse($pr->created_at)->format('Y-m-d H:i:s');
                })
                ->addColumn('action', function ($pr) {
                    switch ($pr->status) {
                        case 'po_submitted':
                            return '<span class="badge bg-info text-dark p-2">Purchase Order Submitted — Sales Officer Reviewing Order</span>';
                        case 'so_created':
                            return '<span class="badge bg-info text-dark p-2">Sales Order Created — Processing Delivery</span>';
                        case 'quotation_sent':
                        default:
                            return '<a href="/b2b/quotations/review/' . $pr->id . '" class="btn btn-sm btn-primary review-pr">
                                        <i class="link-icon" data-lucide="eye"></i> Review Quotation
                                    </a>
                                    <button class="btn btn-sm btn-danger cancel-pr-btn" data-id="' . $pr->id . '">
                                        <i class="link-icon" data-lucide="x-circle"></i> Cancel
                                    </button>
                                    ';
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('pages.b2b.v_quotationList', [
            'page' => 'Sent Quotations'
        ]);
    }


    public function show($id)
    {
        $page = "Purchase Request Quotation";
        $banks = Bank::get();

        $quotation = PurchaseRequest::with(['customer', 'items.product'])
            ->where('status', 'quotation_sent')
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

        return view('pages.b2b.v_quotation_show', compact('quotation', 'page', 'banks'));
    }

    public function cancelQuotation(Request $request, $id)
    {
        $userId = auth()->id();

        $pr = PurchaseRequest::where('id', $id)
            ->where('customer_id', $userId)
            ->whereIn('status', ['quotation_sent', 'po_submitted'])
            ->first();

        if (!$pr) {
            return response()->json(['message' => 'This quotation cannot be cancelled.'], 404);
        }

        $pr->status = 'cancelled';
        $pr->pr_remarks = $request->remarks ?? 'Cancelled by customer.';
        $pr->save();

        // Optional: notify the sales officers
        $officers = User::where('role', 'salesofficer')->get();
        foreach ($officers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'type' => 'purchase_request',
                'message' => "A PR (ID: {$pr->id}) was cancelled by {$pr->customer->name}. <br><a href=\"" . route('salesofficer.purchase-requests.index', $pr->id) . "\">Visit Link</a>",
            ]);
        }

        return response()->json(['message' => 'Quotation cancelled successfully.']);
    }


    public function uploadPaymentProof(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|exists:purchase_requests,id',
            'bank_id' => 'required|exists:banks,id',
            'proof_payment' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $userId = auth()->id();

        $hasAddress = B2BAddress::where('user_id', $userId)->exists();
        if (!$hasAddress) {
            return response()->json([
                'success' => false,
                'message' => 'You must add an address before submitting a quotation.'
            ], 400);
        }

        $hasActiveAddress = B2BAddress::where('user_id', $userId)
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveAddress) {
            return response()->json([
                'success' => false,
                'message' => 'Please select or set a default address before submitting.'
            ], 400);
        }

        $pr = PurchaseRequest::where('id', $request->quotation_id)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        if ($pr->status !== 'quotation_sent') {
            return response()->json(['message' => 'Quotation cannot be paid now.'], 400);
        }

        if ($request->hasFile('proof_payment')) {
            $file = $request->file('proof_payment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('assets/upload/proofpayment');
            $file->move($destinationPath, $filename);

            $path = 'assets/upload/proofpayment/' . $filename;
        }

        $pr->update([
            'bank_id' => $request->bank_id,
            'proof_payment' => $path,
            'status' => 'po_submitted',
        ]);

        // Notify sales officers
        $officers = User::where('role', 'salesofficer')->get();
        foreach ($officers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'type' => 'purchase_request',
                'message' => "A PO (ID: {$pr->id}) was submitted by {$pr->customer->name}. <br><a href=\"" . route('salesofficer.submitted-order.index', $pr->id) . "\">Visit Link</a>",
            ]);
        }

        return response()->json(['message' => 'Payment uploaded successfully.']);
    }

    public function payLater(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|exists:purchase_requests,id',
        ]);

        $userId = auth()->id();
        $user = User::findOrFail($userId);

        $hasActiveAddress = B2BAddress::where('user_id', $userId)
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveAddress) {
            return response()->json([
                'success' => false,
                'message' => 'Please select or set a default address before submitting.'
            ], 400);
        }

        $pr = PurchaseRequest::where('id', $request->quotation_id)
            ->where('customer_id', $userId)
            ->firstOrFail();

        if ($pr->status !== 'quotation_sent') {
            return response()->json(['message' => 'Quotation cannot be processed now.'], 400);
        }

        // Calculate amounts with VAT and delivery
        $subtotal = $pr->items->sum(fn($item) => $item->quantity * $item->product->price);
        $vatRate = $pr->vat ?? 0;
        $vatAmount = $subtotal * ($vatRate / 100);
        $deliveryFee = $pr->delivery_fee ?? 0;
        $totalAmount = $subtotal + $vatAmount + $deliveryFee;

        if ($user->credit_limit < $totalAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Your credit limit is insufficient for this purchase.'
            ], 400);
        }

        // Deduct from credit limit
        $user->decrement('credit_limit', $totalAmount);

        $creditPayment = $pr->createCreditPayment(Carbon::now()->addMonth()->toDateString(), $totalAmount);

        $pr->update([
            'status' => 'po_submitted',
            'credit' => 1,
            'payment_method' => 'pay_later',
        ]);

        // Notify sales officers
        $officers = User::where('role', 'salesofficer')->get();
        foreach ($officers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'type' => 'purchase_request',
                'message' => "PO #{$pr->id} submitted  by {$pr->customer->name} with (Pay Later) - Total: ₱" . number_format($pr->total_amount, 2) . ". <br><a href=\"" . route('salesofficer.submitted-order.index') . "\">Visit Link</a>",
            ]);
        }

        return response()->json([
            'message' => 'Purchase order submitted with pay later option. You have 1 month to complete payment.',
            'credit_limit_remaining' => number_format($user->fresh()->credit_limit,2),
        ]);
    }

    public function checkStatus($id)
    {
        $userId = auth()->id();

        $purchaseRequest = PurchaseRequest::where('id', $id)
            ->where('customer_id', $userId)
            ->firstOrFail();

        return response()->json([
            'status' => $purchaseRequest->status,
        ]);
    }
}
