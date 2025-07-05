<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use App\Models\PurchaseRequest;
use App\Models\B2BAddress;

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
                    $total = $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
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
                    </a>';
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

        $quotation = PurchaseRequest::with(['customer', 'items.product'])
            ->where('status', 'quotation_sent')
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

        return view('pages.b2b.v_quotation_show', compact('quotation', 'page'));
    }

    public function submit_quotation($id)
    {
        try {
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

            $purchaseRequest = PurchaseRequest::where('id', $id)
                ->where('customer_id', $userId)
                ->where('status', 'quotation_sent')
                ->firstOrFail();

            $purchaseRequest->status = 'po_submitted';
            $purchaseRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order successfully submitted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
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
