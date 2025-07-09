<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\b2bDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\Order;
use App\Models\Message;
use App\Models\PurchaseRequest;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $page = 'TantucoCTC';
        $user = User::getCurrentUser();
        $data = null;
        $deliveries = [];

        $totalB2B = 0;
        $totalDeliveryRider = 0;
        $totalSalesOfficer = 0;
        $b2bChange = 0;
        $riderChange = 0;
        $salesChange = 0;

        $role = $user->role ?? null;

        $view = match ($role) {
            'b2b' => 'pages.b2b.index',
            'deliveryrider', 'salesofficer' => 'pages.admin.index',
            'superadmin' => 'pages.superadmin.index',
            default => 'pages.welcome',
        };

        if ($role === 'superadmin') {
            // Current month
            $totalB2B = User::where('role', 'b2b')->count();
            $totalDeliveryRider = User::where('role', 'deliveryrider')->count();
            $totalSalesOfficer = User::where('role', 'salesofficer')->count();

            // Last month range
            $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
            $endLastMonth = Carbon::now()->subMonth()->endOfMonth();

            $prevB2B = User::where('role', 'b2b')
                ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
                ->count();

            $prevDeliveryRider = User::where('role', 'deliveryrider')
                ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
                ->count();

            $prevSalesOfficer = User::where('role', 'salesofficer')
                ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
                ->count();

            // Percentage changes
            $b2bChange = $prevB2B > 0 ? (($totalB2B - $prevB2B) / $prevB2B) * 100 : 0;
            $riderChange = $prevDeliveryRider > 0 ? (($totalDeliveryRider - $prevDeliveryRider) / $prevDeliveryRider) * 100 : 0;
            $salesChange = $prevSalesOfficer > 0 ? (($totalSalesOfficer - $prevSalesOfficer) / $prevSalesOfficer) * 100 : 0;
        }

        if ($role === 'b2b') {
            $products = Product::with('category', 'productImages')
                ->select(['id', 'category_id', 'sku', 'name', 'description', 'price', 'created_at', 'expiry_date']);

            if ($request->filled('search')) {
                $products->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('category_id')) {
                $products->where('category_id', $request->category_id);
            }

            $data = $products->paginate(8);

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('components.product-list', compact('data'))->render()
                ]);
            }
        }

        if ($role === 'deliveryrider') {

            $deliveries = Order::with([
                'user',
                'b2bAddress',
                'delivery.deliveryUser',
                'items.product'
            ])->whereHas('delivery', function ($q) use ($user) {
                $q->where('delivery_rider_id', $user->id)
                    ->where('status', 'assigned');
            })->get();

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('components.delivery-list', compact('deliveries'))->render()
                ]);
            }
        }

        return view($view, compact(
            'page',
            'data',
            'deliveries',
            'totalB2B',
            'totalSalesOfficer',
            'totalDeliveryRider',
            'b2bChange',
            'riderChange',
            'salesChange'
        ));
    }


    public function revenueData()
    {
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();

        $dailyTotal = PurchaseRequest::where('status', 'delivered')
            ->whereDate('created_at', $today)
            ->with('items.product')
            ->get()
            ->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0)));

        $weeklyTotal = PurchaseRequest::where('status', 'delivered')
            ->whereBetween('created_at', [$startOfWeek, $today])
            ->with('items.product')
            ->get()
            ->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0)));

        $monthlyTotal = PurchaseRequest::where('status', 'delivered')
            ->whereBetween('created_at', [$startOfMonth, $today])
            ->with('items.product')
            ->get()
            ->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0)));

        // Monthly grouped totals (last 6 months)
        $monthlyData = PurchaseRequest::where('status', 'delivered')
            ->whereDate('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->with('items.product')
            ->get()
            ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y-m'))
            ->map(fn($group) => $group->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0))))
            ->sortKeys();
            

        return response()->json([
            'daily' => $dailyTotal,
            'weekly' => $weeklyTotal,
            'monthly' => $monthlyTotal,
            'chart_categories' => $monthlyData->keys(),
            'chart_values' => $monthlyData->values(),
        ]);
    }
}
