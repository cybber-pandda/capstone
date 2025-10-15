<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Delivery;
use App\Models\Product;

class ReportController extends Controller
{

    public function userReport(Request $request)
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
        
        if ($user->role === 'superadmin') {

        if ($request->ajax()) {
            $users = User::select(['id', 'name', 'username', 'email', 'role', 'status', 'created_at'])
                ->where('role', '!=', 'superadmin');

            return DataTables::of($users)
                ->addColumn('status', fn($row) => $row->status ? 'Active' : 'Inactive')
                ->make(true);
        }

        $userCounts = [
            'b2b' => User::where('role', 'b2b')->count(),
            'deliveryrider' => User::where('role', 'deliveryrider')->count(),
            'salesofficer' => User::where('role', 'salesofficer')->count(),
        ];

        return view('pages.superadmin.v_userReport', [
            'page' => 'User Report',
            'pageCategory' => 'Reports',
            'userCounts' => $userCounts
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function deliveryReport(Request $request)
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
        
        if ($user->role === 'superadmin') {
        if ($request->ajax()) {
            $deliveries = Delivery::with(['order', 'deliveryUser', 'latestHistory']);

            return DataTables::of($deliveries)
                ->addColumn('order_number', fn($d) => optional($d->order)->order_number)
                ->addColumn('rider', fn($d) => optional($d->deliveryUser)->name ?? 'Unassigned')
                ->addColumn('status', fn($d) => ucfirst(str_replace('_', ' ', $d->status)))
                ->addColumn('location', function ($d) {
                    if ($d->latestHistory) {
                        $lat = $d->latestHistory->latitude;
                        $lng = $d->latestHistory->longitude;
                        return "<a href='https://www.google.com/maps?q={$lat},{$lng}' target='_blank'>{$lat}, {$lng}</a>";
                    }
                    return 'N/A';
                })
                ->addColumn('remarks', fn($d) => optional($d->latestHistory)->remarks ?? 'N/A')
                ->addColumn('logged_at', fn($d) => optional($d->latestHistory)?->logged_at?->format('Y-m-d H:i:s') ?? 'N/A')
                ->addColumn('proof_delivery', function ($d) {
                    if ($d->proof_delivery) {
                        return '<a href="' . asset($d->proof_delivery) . '" target="_blank">View</a>';
                    }
                    return 'N/A';
                })
                ->rawColumns(['proof_delivery', 'location'])
                ->make(true);
        }

        return view('pages.superadmin.v_deliveryReport', [
            'page' => 'Delivery Report',
            'pageCategory' => 'Reports',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function inventoryReport(Request $request)
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
        
        if ($user->role === 'superadmin') {
        if ($request->ajax()) {
            $products = Product::with('inventories')->get();

            $data = $products->map(function ($product) {

                $grouped = $product->inventories->groupBy('reason')->map(function ($items) {
                    return $items->sum(function ($inv) {
                        return $inv->type === 'in' ? $inv->quantity : -$inv->quantity;
                    });
                });

                // Stock calculation
                $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
                $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
                $currentStock = $stockIn - $stockOut;

                return [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'created_at' => $product->created_at,
                    'price' => number_format($product->price, 2),
                    'stockIn' => $stockIn,
                    'stockOut' => $stockOut,
                    'current_stock' => $currentStock,
                    'inventory_breakdown' => $grouped,
                ];
            });

            return datatables()->of($data)->make(true);
        }

        return view('pages.superadmin.v_inventoryReport', [
            'page' => 'Inventory Report',
            'pageCategory' => 'Reports',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function expiredProductReport(Request $request)
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
        
        if ($user->role === 'superadmin') {
        if ($request->ajax()) {
            $today = now()->toDateString();

            $products = Product::with('inventories')
                ->whereNotNull('expiry_date')   // ensure expiry_date exists
                ->where('expiry_date', '<', $today) // only expired
                ->get();

            $data = $products->map(function ($product) {
                // Stock calculation
                $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
                $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
                $currentStock = $stockIn - $stockOut;

                return [
                    'sku'            => $product->sku,
                    'name'           => $product->name,
                    'expiry_date'    => $product->expiry_date,
                    'price'          => number_format($product->price, 2),
                    'stockIn'        => $stockIn,
                    'stockOut'       => $stockOut,
                    'current_stock'  => max($currentStock, 0), // avoid negative
                ];
            });

            return datatables()->of($data)->make(true);
        }

        return view('pages.superadmin.v_expiredProductReport', [
            'page' => 'Expired Product Report',
            'pageCategory' => 'Reports',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }
}
