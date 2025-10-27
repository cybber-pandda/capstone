<?php

namespace App\Http\Controllers\SalesOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\B2BAddress;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\B2BDetail;

class OrderController extends Controller
{
    public function index(Request $request)
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
                ->addColumn('status', function ($pr) {
                    return $pr->status === 'po_submitted'
                        ? '<span class="badge bg-warning text-dark">PO submitted, waiting for SO to be created.</span>'
                        : ucfirst($pr->status);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_submittedOrder', [
            'page' => 'Submitted Purchase Order'
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function sales_invoice(Request $request)
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
        $status = ['delivered'];
        $query = PurchaseRequest::with(['customer', 'items.product'])
                    ->whereIn('status', $status)
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
                ->addColumn('status', function ($pr) {
                    return '<span class="badge bg-success text-white">' . ucfirst($pr->status) . '</span>';
                })
                ->addColumn('action', function ($pr) {
                    $url = route('salesofficer.sales.invoice.show', $pr->id);
                    return '<a href="' . $url . '" class="btn btn-sm btn-inverse-dark p-2">
                                <i class="link-icon" data-lucide="eye"></i> Show Invoice
                            </a>';
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_salesInvoice', [
            'page' => 'B2B Sales Invoice'
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }


    public function show_sales_invoice($id)
    {   

        $status = array('so_created', 'delivery_in_progress', 'delivered');

        $quotation = PurchaseRequest::with(['items.product.productImages'])->whereIn('status', $status)->findOrFail($id);

        $page = 'B2B Sales Invoice';
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

        return view('pages.admin.salesofficer.v_showInvoice', compact('quotation', 'b2bReq', 'b2bAddress', 'salesOfficer', 'superadmin', 'page'));
    }

    public function submit_sales_invoice(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required', 
        ]);

        try {
            // Clean up order_number to extract only the number
            $cleanedOrderNum = preg_replace('/^REF\s*(\d+)-.*/', '$1', $request->quotation_id);

            // Find order
            $order = Order::whereRaw("order_number LIKE ?", ["REF {$cleanedOrderNum}-%"])->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'No order found with this quotation ID.',
                ], 404);
            }

            if ($order) {
                $delivery = Delivery::where('order_id', $order->id)->first();

                if ($delivery) {
                    $delivery->sales_invoice_flg = 1;
                    $delivery->save();

                    PurchaseRequest::where('id', $request->quotation_id)->update(['status' => 'invoice_sent']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice submitted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
    // ✅ Added new method for Sent Sales Invoice
    public function sent_sales_invoice(Request $request) {
            // 🔐 Redirect to login if user not authenticated
            if (!Auth::check()) {
                $page = 'Sign In';
                $companysettings = \DB::table('company_settings')->first();

                return response()
                    ->view('auth.login', compact('page', 'companysettings'))
                    ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
            }

            $user = Auth::user();

            // 🧩 Check if the user is a Sales Officer
            if ($user->role === 'salesofficer') {

                if ($request->ajax()) {
                    // ✅ Fetch only sent invoices (status = invoice_sent)
                    $query = PurchaseRequest::with(['customer', 'items.product'])
                        ->where('status', 'invoice_sent')
                        ->latest();

                    return DataTables::of($query)
                        ->addColumn('customer_name', fn($pr) => optional($pr->customer)->name)
                        ->addColumn('total_items', fn($pr) => $pr->items->sum('quantity'))
                        ->addColumn('sent_at', function ($row) {
                            // Show the date when invoice was marked as "invoice_sent"
                            if ($row->status === 'invoice_sent') {
                                return Carbon::parse($row->updated_at)->format('Y-m-d H:i');
                            }
                            return '—';
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
                        ->editColumn('created_at', fn($pr) => Carbon::parse($pr->created_at)->format('Y-m-d H:i:s'))
                        ->addColumn('status', fn($pr) => '<span class="badge bg-info text-dark">Invoice Sent</span>')
                        ->addColumn('action', function ($pr) {
                            $url = route('salesofficer.sent.sales.invoice.show', $pr->id);
                            return '<a href="' . $url . '" class="btn btn-sm btn-inverse-dark p-2">
                                        <i class="link-icon" data-lucide="eye"></i> View Invoice
                                    </a>';
                        })
                        ->rawColumns(['status', 'action'])
                        ->make(true);
                }

                // 📄 Load the new view for Sent Sales Invoices
                return view('pages.admin.salesofficer.v_sentSalesInvoice', [
                    'page' => 'Sent Sales Invoice'
                ]);
            }

            // 🚫 Redirect non-salesofficers
            return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
        }
    public function show_sent_sales_invoice($id) {
            $status = ['invoice_sent']; // Only show if invoice was sent

            $quotation = \App\Models\PurchaseRequest::with(['items.product.productImages'])
                ->whereIn('status', $status)
                ->findOrFail($id);

            $page = 'View Sent Sales Invoice';
            $b2bReq = null;
            $b2bAddress = null;
            $salesOfficer = null;
            $superadmin = \App\Models\User::where('role', 'superadmin')->first();

            if ($quotation->customer_id) {
                $b2bReq = \App\Models\B2BDetail::where('user_id', $quotation->customer_id)
                    ->where('status', 'approved')
                    ->first();

                $b2bAddress = \App\Models\B2BAddress::where('user_id', $quotation->customer_id)
                    ->where('status', 'active')
                    ->first();
            }

            if ($quotation->prepared_by_id) {
                $salesOfficer = \App\Models\User::find($quotation->prepared_by_id);
            }

            return view('pages.admin.salesofficer.v_showSentInvoice', compact(
                'quotation',
                'b2bReq',
                'b2bAddress',
                'salesOfficer',
                'superadmin',
                'page'
            ));
    }

}
