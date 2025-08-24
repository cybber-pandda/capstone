<?php

namespace App\Http\Controllers\SalesOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use App\Models\PurchaseRequestRefund;
use App\Models\PurchaseRequestReturn;

class ReturnRefundController extends Controller
{
    public function index()
    {
        return view('pages.admin.salesofficer.v_returnRefund', [
            'page' => 'Return & Refund'
        ]);
    }

    public function data(Request $request)
    {
        if ($request->type === 'return') {
            $query = PurchaseRequestReturn::with(['purchaseRequest.customer', 'product'])->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', fn($pr) => optional($pr->purchaseRequest->customer)->name)
                ->addColumn('product_name', fn($pr) => optional($pr->product)->name)
                ->editColumn('status', fn($pr) => ucfirst($pr->status ?? 'Pending'))
                ->editColumn('created_at', fn($pr) => Carbon::parse($pr->created_at)->format('F d, Y h:i A'))
                ->addColumn('action', fn($pr) => '<button class="btn btn-sm btn-primary review-return" data-id="'.$pr->id.'">Review</button>')
                ->rawColumns(['action'])
                ->make(true);

        } elseif ($request->type === 'refund') {
            $query = PurchaseRequestRefund::with(['purchaseRequest.customer', 'product'])->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', fn($pr) => optional($pr->purchaseRequest->customer)->name)
                ->addColumn('product_name', fn($pr) => optional($pr->product)->name)
                ->addColumn('amount', fn($pr) => '₱' . number_format($pr->amount, 2))
                ->addColumn('method', fn($pr) => ucfirst($pr->method))
                ->editColumn('status', fn($pr) => ucfirst($pr->status ?? 'Pending'))
                ->editColumn('created_at', fn($pr) => Carbon::parse($pr->created_at)->format('F d, Y h:i A'))
                ->addColumn('action', fn($pr) => '<button class="btn btn-sm btn-success process-refund" data-id="'.$pr->id.'">Process</button>')
                ->rawColumns(['action'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid type'], 400);
    }

    // Return details HTML
    public function returnDetails(Request $request, PurchaseRequestReturn $return)
    {
        $html = '
        <div class="modal-body">
            <p><strong>Customer:</strong> ' . optional($return->purchaseRequest->customer)->name . '</p>
            <p><strong>Product:</strong> ' . optional($return->product)->name . '</p>
            <p><strong>Reason:</strong> ' . $return->reason . '</p>
            <p><strong>Status:</strong> ' . ucfirst($return->status ?? 'Pending') . '</p>
            <p><strong>Date Requested:</strong> ' . $return->created_at->format('F d, Y h:i A') . '</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success approve-return" data-id="'.$return->id.'">Approve</button>
            <button class="btn btn-danger reject-return" data-id="'.$return->id.'">Reject</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>';

        return response()->json(['html' => $html]);
    }

    // Refund details HTML
    public function refundDetails(Request $request, PurchaseRequestRefund $refund)
    {
        $html = '
        <div class="modal-body">
            <p><strong>Customer:</strong> ' . optional($refund->purchaseRequest->customer)->name . '</p>
            <p><strong>Product:</strong> ' . optional($refund->product)->name . '</p>
            <p><strong>Amount:</strong> ₱' . number_format($refund->amount,2) . '</p>
            <p><strong>Method:</strong> ' . ucfirst($refund->method) . '</p>
            <p><strong>Date Processed:</strong> ' . $refund->created_at->format('F d, Y h:i A') . '</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success process-refund-confirm" data-id="'.$refund->id.'">Process</button>
            <button class="btn btn-danger reject-refund" data-id="'.$refund->id.'">Reject</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>';

        return response()->json(['html' => $html]);
    }

    public function approveReturn(PurchaseRequestReturn $return)
    {
        $return->status = 'approved';
        $return->save();
        return response()->json(['success' => true, 'message' => 'Return approved.']);
    }

    public function rejectReturn(PurchaseRequestReturn $return)
    {
        $return->status = 'rejected';
        $return->save();
        return response()->json(['success' => true, 'message' => 'Return rejected.']);
    }

    public function approveRefund(PurchaseRequestRefund $refund)
    {
        $refund->status = 'approved';
        $refund->save();
        return response()->json(['success' => true, 'message' => 'Refund approved.']);
    }

    public function rejectRefund(PurchaseRequestRefund $refund)
    {
        $refund->status = 'rejected';
        $refund->save();
        return response()->json(['success' => true, 'message' => 'Refund rejected.']);
    }
}
