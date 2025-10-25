<?php

namespace App\Http\Controllers\SalesOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Exports\SalesSummaryExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;
use App\Models\B2BAddress;
use App\Models\B2BDetail;
use App\Models\PurchaseRequest;
use App\Models\PrReserveStock;

class PurchaseRequestController extends Controller
{
    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $query = PurchaseRequest::with(['customer', 'items.product'])
    //             ->where('status', 'pending')
    //             ->whereNotNull('transaction_uuid') // ADD THIS to exclude NULL transaction_uuid
    //             ->groupBy('transaction_uuid')
    //             ->latest();

    //         return DataTables::of($query)
    //             ->addColumn('customer_name', function ($pr) {
    //                 return optional($pr->customer)->name;
    //             })
    //             ->addColumn('total_items', function ($pr) {
    //                 // Get all PRs with the same transaction_uuid and sum their items
    //                 return PurchaseRequest::where('transaction_uuid', $pr->transaction_uuid)
    //                     ->where('status', 'pending')
    //                     ->with('items')
    //                     ->get()
    //                     ->sum(fn($pr) => $pr->items->sum('quantity'));
    //             })
    //             ->addColumn('grand_total', function ($pr) {
    //                 // Calculate total across all PRs with the same transaction_uuid
    //                 $allPrs = PurchaseRequest::where('transaction_uuid', $pr->transaction_uuid)
    //                     ->where('status', 'pending')
    //                     ->with('items.product')
    //                     ->get();

    //                 $subtotal = $allPrs->sum(function ($pr) {
    //                     return $pr->items->sum(function ($item) {
    //                         $price = $item->product->discount > 0 ? $item->product->discounted_price : $item->product->price;
    //                         return $item->quantity * ($price ?? 0);
    //                     });
    //                 });

    //                 // Use VAT and delivery fee from the first PR in the group
    //                 $firstPr = $allPrs->first();
    //                 $vatRate = $firstPr->vat ?? 0;
    //                 $vatAmount = $subtotal * ($vatRate / 100);
    //                 $deliveryFee = $firstPr->delivery_fee ?? 0;
    //                 $total = $subtotal + $vatAmount + $deliveryFee;

    //                 return '₱' . number_format($total, 2);
    //             })
    //             ->editColumn('created_at', function ($pr) {
    //                 // Get the earliest created_at in the transaction group
    //                 $earliest = PurchaseRequest::where('transaction_uuid', $pr->transaction_uuid)
    //                     ->where('status', 'pending')
    //                     ->min('created_at');

    //                 return Carbon::parse($earliest)->format('Y-m-d H:i:s');
    //             })
    //             ->addColumn('action', function ($pr) {
    //                 return '<button type="button" class="btn btn-sm btn-inverse-dark review-pr p-2" 
    //                     data-transaction-uuid="' . $pr->transaction_uuid . '">
    //                     <i class="link-icon" data-lucide="eye"></i> Review PR
    //                 </button>';
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     return view('pages.admin.salesofficer.v_purchaseList', [
    //         'page' => 'Pending Purchase Requests'
    //     ]);
    // }

public function index(Request $request)
{
    // 🧠 1️⃣ Auto-reject pending PRs older than 1 day
    $expiredPRs = PurchaseRequest::where('status', 'pending')
        ->where('created_at', '<', Carbon::now()->subDay())
        ->get();

    foreach ($expiredPRs as $pr) {
        $pr->update([
            'status' => 'reject_quotation',
            'pr_remarks' => 'PR got Expired',
        ]);

        // ✅ Release reserved stock
        PrReserveStock::releaseReservedStock($pr->id, 'cancelled');

        // ✅ Notify customer
        if ($pr->customer) {
            Notification::create([
                'user_id' => $pr->customer->id,
                'type' => 'quotation_rejected',
                'message' => 'Your purchase request #' . $pr->id . ' has expired and was automatically rejected. 
                    <br><a href="' . route('b2b.purchase-requests.index') . '">Visit Link</a>',
            ]);
        }
    }

    // 🧠 2️⃣ Auto-cancel quotations older than 2 days
    $expiredQuotations = PurchaseRequest::where('status', 'quotation_sent')
        ->where('date_issued', '<', Carbon::now()->subDays(2))
        ->get();

    foreach ($expiredQuotations as $quotation) {
        $quotation->update([
            'status' => 'cancelled',
            'pr_remarks_cancel' => 'Quotation has expired',
        ]);

        // ✅ Release reserved stock (same as customer cancel)
        PrReserveStock::releaseReservedStock($quotation->id, 'expired');

        // ✅ Notify customer
        if ($quotation->customer) {
            Notification::create([
                'user_id' => $quotation->customer->id,
                'type' => 'quotation_expired',
                'message' => 'Your quotation for PR #' . $quotation->id . ' has expired after 2 days and was automatically cancelled. 
                    <br><a href="' . route('b2b.purchase-requests.index') . '">Visit Link</a>',
            ]);
        }

        // ✅ Notify all sales officers
        $officers = \App\Models\User::where('role', 'salesofficer')->get();
        foreach ($officers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'type' => 'quotation_expired',
                'message' => 'A quotation (PR #' . $quotation->id . ') from ' . optional($quotation->customer)->name . ' expired and was auto-cancelled. 
                    <br><a href="' . route('salesofficer.purchase-requests.index') . '">Visit Link</a>',
            ]);
        }
    }

    // 🧠 3️⃣ If user not logged in → redirect to login
    if (!Auth::check()) {
        $page = 'Sign In';
        $companysettings = DB::table('company_settings')->first();

        return response()
            ->view('auth.login', compact('page', 'companysettings'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

            // 🧠 3️⃣ Check user role
            $user = Auth::user();

        // Example role logic (adjust 'role' and role names to match your database)

        if ($user->role === 'salesofficer') {

            if ($request->ajax()) {
                $query = PurchaseRequest::with(['customer', 'items.product'])
                    ->where('status', 'pending')
                    ->latest();

                return DataTables::of($query)
                    ->addColumn('customer_name', function ($pr) {
                        return optional($pr->customer)->name;
                    })
                    ->addColumn('total_items', function ($pr) {
                        return $pr->items->sum('quantity');
                    })
                    ->addColumn('grand_total', function ($pr) {
                        $subtotal = \DB::table('purchase_request_items')
                            ->where('purchase_request_id', $pr->id)
                            ->sum('subtotal');

                        $vatRate = $pr->vat ?? 0;
                        $vatAmount = $subtotal * ($vatRate / 100);
                        $deliveryFee = $pr->delivery_fee ?? 0;
                        $total = $subtotal + $vatAmount + $deliveryFee;

                        return '₱' . number_format($total, 2);
                    })
                    ->editColumn('created_at', function ($pr) {
                        return Carbon::parse($pr->created_at)->format('Y-m-d H:i:s');
                    })
                    ->addColumn('action', function ($pr) {
                        return '<button type="button" class="btn btn-sm btn-inverse-dark  review-pr p-2" data-id="' . $pr->id . '">
                            <i class="link-icon" data-lucide="eye"></i> Review PR
                        </button>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }

            return view('pages.admin.salesofficer.v_purchaseList', [
                'page' => 'Pending Purchase Requests'
            ]);
        }
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function show($id)
    {
        $pr = PurchaseRequest::with(['items.product.productImages'])->findOrFail($id);

        $b2bReq = null;
        $b2bAddress = null;

        // if ($pr->isNotEmpty()) {
        if ($pr->customer_id) {
            $b2bReq = B2BDetail::where('user_id', $pr->customer_id)
                ->where('status', 'approved')
                ->first();

            $b2bAddress = B2BAddress::where('user_id', $pr->customer_id)
                ->where('status', 'active')
                ->first();
        }

        $html = view('components.pr-items', compact('pr', 'b2bReq', 'b2bAddress'))->render();

        return response()->json(['html' => $html]);
    }

    // public function show($id)
    // {
    //     $prs = PurchaseRequest::with(['items.product.productImages', 'customer'])
    //         ->where('transaction_uuid', $id)
    //         ->get();

    //     if ($prs->isEmpty()) {
    //         return response()->json(['html' => 'Purchase request not found'], 404);
    //     }

    //     // Get customer from first PR (assuming all have same customer)
    //     $customerId = $prs->first()->customer_id;

    //     $b2bReq = B2BDetail::where('user_id', $customerId)
    //         ->where('status', 'approved')
    //         ->first();

    //     $b2bAddress = B2BAddress::where('user_id', $customerId)
    //         ->where('status', 'active')
    //         ->first();

    //     $html = view('components.pr-items', compact('prs', 'b2bReq', 'b2bAddress'))->render();

    //     return response()->json(['html' => $html]);
    // }

    public function updateSendQuotation(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $userid = auth()->user()->id;

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'type' => 'warning',
                'message' => 'Only pending requests can be converted to quotations.'
            ]);
        }

        // Optional: Validate vat and delivery_fee
        $validated = $request->validate([
            // 'vat' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
        ]);

      
        PrReserveStock::approveReservation($id);

        // Update purchase request
        $purchaseRequest->update([
            'prepared_by_id' => $userid,
            'status' => 'quotation_sent',
            'vat' => 12,
            'delivery_fee' => $validated['delivery_fee'] ?? null,
            'date_issued' => Carbon::today()
        ]);

        // Notify customer
        if ($purchaseRequest->customer) {
            Notification::create([
                'user_id' => $purchaseRequest->customer->id,
                'type' => 'quotation_sent',
                'message' => 'A quotation has been sent for your purchase request #' . $purchaseRequest->id . '. <br><a href="' . route('b2b.purchase-requests.index') . '">Visit Link</a>',
            ]);
        }

        return response()->json([
            'type' => 'success',
            'message' => 'Quotation sent successfully!',
            'prId' => $purchaseRequest->id,
        ]);
    }

    public function updateRejectQuotation(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $userid = auth()->user()->id;

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'type' => 'warning',
                'message' => 'Only pending requests can be rejected.'
            ]);
        }

        $purchaseRequest->prepared_by_id = $userid;
        $prefix = $request->type ? $request->type . ': ' : '';
        $purchaseRequest->pr_remarks .= $prefix . $request->rejection_reason;
        $purchaseRequest->status = 'reject_quotation';
        $purchaseRequest->save();

        PrReserveStock::releaseReservedStock($id, 'cancelled');

        // Notify customer
        if ($purchaseRequest->customer) {
            Notification::create([
                'user_id' => $purchaseRequest->customer->id,
                'type' => 'quotation_sent',
                'message' => 'A quotation has been rejected for your purchase request #' . $purchaseRequest->id . '. <br><a href="' . route('b2b.purchase-requests.index') . '">Visit Link</a>',
            ]);
        }

        return response()->json([
            'type' => 'success',
            'message' => 'Quotation rejected successfully!',
            'prId' => $purchaseRequest->id,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        return Excel::download(new SalesSummaryExport($startDate, $endDate), 'sales_summary.xlsx');
    }
}
