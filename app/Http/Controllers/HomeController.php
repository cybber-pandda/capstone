<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\b2bDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\Order;
use App\Models\Inventory;
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

    public function salesRevenueData(Request $request)
    {
        $filter = $request->input('filter', 'month');
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();

        // Totals for cards
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

        $grouped = collect();

        if ($filter === 'day') {
            for ($i = 6; $i >= 0; $i--) {
                $key = now()->subDays($i)->format('Y-m-d');
                $label = now()->subDays($i)->format('M d');
                $grouped->put($key, ['label' => $label, 'value' => 0]);
            }

            $rawData = PurchaseRequest::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subDays(6))
                ->with('items.product')
                ->get()
                ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y-m-d'))
                ->map(fn($group) => $group->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0))));

        } elseif ($filter === 'week') {
            for ($i = 7; $i >= 0; $i--) {
                $start = now()->subWeeks($i)->startOfWeek();
                $label = $start->format('W Y');
                $key = $label; // Week number and year
                $grouped->put($key, ['label' => "Week {$start->format('W')}", 'value' => 0]);
            }

            $rawData = PurchaseRequest::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subWeeks(7)->startOfWeek())
                ->with('items.product')
                ->get()
                ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->startOfWeek()->format('W Y'))
                ->map(fn($group) => $group->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0))));

        } elseif ($filter === 'year') {
            for ($i = 4; $i >= 0; $i--) {
                $key = now()->subYears($i)->format('Y');
                $grouped->put($key, ['label' => $key, 'value' => 0]);
            }

            $rawData = PurchaseRequest::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subYears(4)->startOfYear())
                ->with('items.product')
                ->get()
                ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y'))
                ->map(fn($group) => $group->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0))));

        } else { // default to month
            for ($i = 11; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $label = now()->subMonths($i)->format('M Y');
                $grouped->put($key, ['label' => $label, 'value' => 0]);
            }

            $rawData = PurchaseRequest::where('status', 'delivered')
                ->whereDate('created_at', '>=', now()->subMonths(12)->startOfMonth())
                ->with('items.product')
                ->get()
                ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y-m'))
                ->map(fn($group) => $group->sum(fn($pr) => $pr->items->sum(fn($item) => $item->quantity * ($item->product->price ?? 0))));
        }

        // Merge actual values
        $grouped = $grouped->map(function ($item, $key) use ($rawData) {
            if ($rawData->has($key)) {
                $item['value'] = $rawData[$key];
            }
            return $item;
        });

        $chartCategories = $grouped->pluck('label')->values();
        $chartValues = $grouped->pluck('value')->values();

        return response()->json([
            'daily' => $dailyTotal,
            'weekly' => $weeklyTotal,
            'monthly' => $monthlyTotal,
            'chart_categories' => $chartCategories,
            'chart_values' => $chartValues
        ]);
    }

    public function inventoryPieData()
    {
        $reasons = ['restock', 'sold', 'returned', 'damaged', 'stock update', 'other'];

        $inventory = Inventory::select('reason', 'type', 'quantity')
            ->get()
            ->groupBy('reason')
            ->map(function ($items, $reason) {
                return $items->sum(function ($inv) {
                    return $inv->type === 'in' ? $inv->quantity : -$inv->quantity;
                });
            });

        // Ensure all reasons are present, even with zero values
        $data = collect($reasons)->mapWithKeys(function ($reason) use ($inventory) {
            return [$reason => $inventory[$reason] ?? 0];
        });

        return response()->json([
            'labels' => $data->keys()->values(),
            'values' => $data->values(),
        ]);
    }

}
