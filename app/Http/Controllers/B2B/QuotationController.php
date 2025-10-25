<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\PurchaseRequest;
use App\Models\PaidPayment;
use App\Models\B2BAddress;
use App\Models\B2BDetail;
use App\Models\Notification;
use App\Models\User;
use App\Models\Bank;
use App\Models\PrReserveStock;

class QuotationController extends Controller
{
    public function review(Request $request)
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
    
   if ($user->role === 'b2b') {
        $userId = auth()->id();

        $hasAddress = B2BAddress::where('user_id', $userId)->exists();

        if ($request->ajax()) {
            $type = $request->get('type', 'processing');

            $query = PurchaseRequest::with(['customer', 'items.product'])
                ->where('customer_id', auth()->id());

            if ($type === 'processing') {
                $query->whereIn('status', ['quotation_sent', 'po_submitted', 'so_created']);
            } elseif ($type === 'rejected') {
                $query->where('status', 'reject_quotation');
            } elseif ($type === 'cancelled') {
                $query->where('status', 'cancelled');
            }

            return DataTables::of($query)
                ->addColumn('customer_name', function ($pr) {
                    return optional($pr->customer)->name;
                })
                ->addColumn('total_items', function ($pr) {
                    return $pr->items->sum('quantity');
                })
                ->addColumn('grand_total', function ($pr) {
                    $subtotal = $pr->items->sum(function($item) {
                        return ($item->subtotal ?? ($item->unit_price * $item->quantity));
                    });

                    $vatRate = floatval($pr->vat ?? 0);
                    $vatAmount = $subtotal * ($vatRate / 100);
                    $deliveryFee = floatval($pr->delivery_fee ?? 0);
                    $total = $subtotal + $vatAmount + $deliveryFee;

                    return '₱' . number_format($total, 2, '.', ',');    
                })

                ->editColumn('created_at', function ($pr) {
                    return Carbon::parse($pr->created_at)->format('Y-m-d H:i:s');
                })
                ->addColumn('action', function ($pr) {
                    switch ($pr->status) {
                        case 'po_submitted':
                            return '<span style="color:blue;font-weight:bold;">PO Submitted</span>';
                        case 'so_created':
                            return '<span style="color:blue;font-weight:bold;">SO Created</span>';
                        case 'reject_quotation':
                            return '
                            <div style="display: flex; flex-direction: column;">
                                <span style="color:red;font-weight:bold;">
                                    Rejected
                                </span>
                                <div style="margin-top: 10px; font-size: 14px;">
                                    <strong>Remarks:</strong><br>
                                    ' . nl2br(e($pr->pr_remarks)) . '
                                </div>
                            </div>';
                        case 'cancelled':
                            return '
                            <div style="display: flex; flex-direction: column;">
                                <span style="color:red;font-weight:bold;">
                                    Cancelled
                                </span>
                                <div style="margin-top: 10px; font-size: 14px;">
                                    <strong>Remarks:</strong><br>
                                    ' . nl2br(e($pr->pr_remarks_cancel)) . '
                                </div>
                            </div>';
                        case 'quotation_sent':
                        default:
                            return '<a href="/b2b/quotations/review/' . $pr->id . '" class="btn btn-sm btn-primary review-pr">
                                        <i class="link-icon" data-lucide="eye"></i> Review Quotation
                                    </a>
                                    <button class="btn btn-sm btn-danger cancel-pr-btn" style="display:none;" data-id="' . $pr->id . '">
                                        <i class="link-icon" data-lucide="x-circle"></i> Cancel
                                    </button>
                                    ';
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('pages.b2b.v_quotationList', [
            'page' => 'Sent Quotations',
            'hasAddress' => $hasAddress
        ]);
    }
    //for returning to the dashboard
         return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function show($id)
    {
        $page = "Purchase Request Quotation";
        $banks = Bank::get();
        $b2bReqDetails = null;
        $b2bAddress = null;
        $salesOfficer = null;

        $superadmin = User::where('role', 'superadmin')->first();

        $quotation = PurchaseRequest::with(['customer', 'items.product'])
            ->where('status', 'quotation_sent')
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

        if ($quotation->customer_id) {
            $b2bReqDetails = B2BDetail::where('user_id', $quotation->customer_id)->first();
            $b2bAddress = B2BAddress::where('user_id', $quotation->customer_id)->where('status', 'active')->first();
        }

        if ($quotation->prepared_by_id) {
            $salesOfficer = User::where('id', $quotation->prepared_by_id)->first();
        }

        return view('pages.b2b.v_quotation_show', compact('quotation', 'page', 'banks', 'b2bReqDetails', 'b2bAddress', 'salesOfficer', 'superadmin'));
    }

    public function downloadQuotation($id)
    {
        $b2bReqDetails = null;
        $b2bAddress = null;
        $salesOfficer = null;

        $superadmin = User::where('role', 'superadmin')->first();

        $quotation = PurchaseRequest::with(['customer', 'items.product'])
            ->where('status', 'quotation_sent')
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

        if ($quotation->customer_id) {
            $b2bReqDetails = B2BDetail::where('user_id', $quotation->customer_id)->first();
            $b2bAddress = B2BAddress::where('user_id', $quotation->customer_id)->where('status', 'active')->first();
        }

        if ($quotation->prepared_by_id) {
            $salesOfficer = User::where('id', $quotation->prepared_by_id)->first();
        }

        $pdf = Pdf::loadView('components.quotation-items-pdf', compact('quotation', 'b2bReqDetails', 'b2bAddress', 'salesOfficer', 'superadmin'));
        return $pdf->download($quotation->customer->name . '_quotation_' . $quotation->id . '-' . Carbon::parse($quotation->created_at)->format('Ymd') . '.pdf');
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
        $pr->pr_remarks_cancel = $request->remarks ?? 'Cancelled by customer.';
        $pr->save();

        PrReserveStock::releaseReservedStock($id, 'cancelled');

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
        $rules = [
            'quotation_id' => 'required|exists:purchase_requests,id',
            'cod_flg' => 'required|integer|in:0,1',
        ];

        // If NOT COD (Bank Transfer), require these fields
        if ($request->cod_flg == 0) {
            $rules = array_merge($rules, [
                'bank_id' => 'required|exists:banks,id',
                'paid_amount' => 'required|numeric|min:1',
                'proof_payment' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'reference_number' => 'required|string|max:30',
            ]);
        }

        $request->validate($rules);

        $userId = auth()->id();

        // Ensure the user has an active address
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

        $path = null;
        if ($request->cod_flg == 0 && $request->hasFile('proof_payment')) {
            $file = $request->file('proof_payment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('assets/upload/proofpayment');
            $file->move($destinationPath, $filename);

            $path = 'assets/upload/proofpayment/' . $filename;
        }

        $pr->update([
            'status' => 'po_submitted',
            'payment_method' => 'pay_now',
            'cod_flg' => $request->cod_flg,
        ]);

        // Insert payment only if not COD
        if ($request->cod_flg == 0) {
            PaidPayment::create([
                'purchase_request_id' => $pr->id,
                'bank_id' => $request->bank_id,
                'paid_amount' => $request->paid_amount,
                'paid_date' => Carbon::today(),
                'proof_payment' => $path,
                'reference_number' => $request->reference_number,
            ]);
        }

        // Notify sales officers or superadmins
        $superadmin = User::where('role', 'superadmin')->get();
        foreach ($superadmin as $sa) {
            Notification::create([
                'user_id' => $sa->id,
                'type' => 'purchase_request',
                'message' => "A PO (ID: {$pr->id}) was submitted by {$pr->customer->name} with (Pay Now). <a href=\"" . route('home', $pr->id) . "\" class='d-none'>Visit Link</a>",
            ]);
        }

        return response()->json(['message' => 'Payment uploaded successfully.']);
    }

    public function payLater(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|exists:purchase_requests,id',
            'payment_type' => 'required|string|max:8',
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
        $subtotal = $pr->items->sum('subtotal'); 
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

        $pr->update([
            'status' => 'po_submitted',
            'credit' => 1,
            'credit_amount' => $totalAmount,
            'payment_method' => 'pay_later',
            'credit_payment_type' => ucfirst($request->payment_type) . ' Payment',
        ]); // after this update 

        if($request->payment_type === 'straight') {
            $pr->createStraightCreditPayment(Carbon::now()->addMonth()->toDateString());
        } elseif ($request->payment_type === 'partial') {
            $pr->createPartialCreditPayments(Carbon::now(), $totalAmount);
        }

        // Notify sales officers
        // $officers = User::where('role', 'salesofficer')->get();
        // foreach ($officers as $officer) {
        //     Notification::create([
        //         'user_id' => $officer->id,
        //         'type' => 'purchase_request',
        //         'message' => "PO #{$pr->id} submitted  by {$pr->customer->name} with (Pay Later) - Total: ₱" . number_format($pr->total_amount, 2) . ". <br><a href=\"" . route('salesofficer.submitted-order.index') . "\">Visit Link</a>",
        //     ]);
        // }

        // Notify sales officers or superadmins
        $superadmin = User::where('role', 'superadmin')->get();

        foreach ($superadmin as $sa) {
            Notification::create([
                'user_id' => $sa->id,
                'type' => 'purchase_request',
                'message' => "PO #{$pr->id} submitted by {$pr->customer->name} with (Pay Later) - Total: ₱" 
                    . number_format($pr->total_amount, 2),
            ]);
        }

        
        return response()->json([
            'message' => 'Purchase order submitted with pay later option. You have 1 month to complete payment.',
            'credit_limit_remaining' => number_format($user->fresh()->credit_limit, 2),
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
