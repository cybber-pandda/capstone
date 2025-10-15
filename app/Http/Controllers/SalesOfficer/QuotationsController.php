<?php

namespace App\Http\Controllers\SalesOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Models\PurchaseRequest;

class QuotationsController extends Controller
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
                ->where('status', 'quotation_sent')
                ->latest();

            return DataTables::of($query)
                ->addColumn('customer_name', function ($pr) {
                    return optional($pr->customer)->name;
                })
                ->addColumn('total_items', function ($pr) {
                    return $pr->items->sum('quantity');
                })
        ->addColumn('grand_total', function ($pr) {
            $subtotal = $pr->items->sum(function ($item) {
                $price = $item->product->price ?? 0;
                $discountedPrice = $item->product->discounted_price ?? $price;
                $quantity = $item->quantity;

                // Use discounted price if product has a discount
                $finalPrice = ($item->product->discount > 0 ? $discountedPrice : $price) * $quantity;

                return $finalPrice;
            });

            $vatRate = $pr->vat ?? 0; // VAT percentage
            $vatAmount = $subtotal * ($vatRate / 100);

            $deliveryFee = $pr->delivery_fee ?? 0;

            $total = $subtotal + $vatAmount + $deliveryFee;

            return '₱' . number_format($total, 2);
        })
                ->editColumn('created_at', function ($pr) {
                    return Carbon::parse($pr->created_at)->format('Y-m-d H:i:s');
                })
                ->addColumn('status', function ($pr) {
                    return $pr->status === 'quotation_sent'
                        ? '<span class="badge bg-warning text-dark">Waiting PO to be submitted</span>'
                        : ucfirst($pr->status);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('pages.admin.salesofficer.v_sentQuotations', [
            'page' => 'Sent Quotations'
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }
}
