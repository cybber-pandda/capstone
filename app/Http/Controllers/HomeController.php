<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Exports\SalesSummaryExport;
use App\Exports\SalesSummaryManualExport;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Product;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\PaidPayment;
use App\Models\CreditPayment;
use App\Models\CreditPartialPayment;
use App\Models\ManualEmailOrder;

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

        $totalB2BAllTime = 0;
        $totalSalesOfficerAllTime = 0;
        $totalDeliveryRiderAllTime = 0;

        $data = null;
        $deliveries = [];

        $totalB2B = 0;
        $totalDeliveryRider = 0;
        $totalSalesOfficer = 0;

        $b2bChange = 0;
        $riderChange = 0;
        $salesChange = 0;

        $totalPendingPR = 0;
        $totalPOSubmittedPR = 0;
        $totalSalesOrderPR = 0;
        $totalDeliveredPR = 0;
        $totalcashsales = 0;

        $totalPendingPRChange = 0;
        $totalPOSubmittedPRChange = 0;
        $totalSalesOrderPRChange = 0;
        $totalDeliveredPRChange = 0;

        $totalpaynow = PaidPayment::where('status', 'paid')->sum('paid_amount');
        $totalpaylater = 0;
        $creditpayment = CreditPayment::where('status', 'paid')->sum('paid_amount');
        $creditpartialpayment = CreditPartialPayment::where('status', 'paid')->sum('paid_amount');


        $totalmanualorder = ManualEmailOrder::where('status', 'approve')->get()->sum(function ($pr) {
            $items = json_decode($pr->purchase_request, true) ?? [];
            return collect($items)->sum(fn($item) => ((int)($item['qty'] ?? 0)) * ((float)($item['price'] ?? 0)));
        });

        $role = $user->role ?? null;
        // -------------------------------
        // Global totals (compute once)
        // -------------------------------
        $creditpayment = CreditPayment::where('status', 'paid')->sum('paid_amount');
        $creditpartialpayment = CreditPartialPayment::where('status', 'paid')->sum('paid_amount');
        $totalpaylater = $creditpayment + $creditpartialpayment;

        $totalpaynow = PaidPayment::where('status', 'paid')->sum('paid_amount');

        $totalmanualorder = ManualEmailOrder::where('status', 'approve')->get()->sum(function ($pr) {
            $items = json_decode($pr->purchase_request, true) ?? [];
            $itemsSubtotal = collect($items)->sum(fn($item) => ((int)($item['qty'] ?? 0)) * ((float)($item['price'] ?? 0)));
            $itemsVAT = $itemsSubtotal * 0.12;
            $deliveryFee = (float) ($pr->delivery_fee ?? 0);
            return $itemsSubtotal + $itemsVAT + $deliveryFee;
        });

        $totalcashsales = $totalpaynow + $totalmanualorder;

        $totalDeliveryFeeManual = ManualEmailOrder::where('status', 'approve')->sum('delivery_fee');
        $totalDeliveryFeePR = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])->sum('delivery_fee');
        $totalDeliveryFeeAll = $totalDeliveryFeeManual + $totalDeliveryFeePR;

        $view = match ($role) {
            'b2b' => 'pages.b2b.index',
            'deliveryrider', 'salesofficer' => 'pages.admin.index',
            'superadmin' => 'pages.superadmin.index',
            default => 'pages.welcome',
        };
        //ito
        if ($role === 'superadmin') {
            $today = Carbon::today();

            // -------------------------------
            // All-time totals
            // -------------------------------
            $totalB2BAllTime = User::where('role', 'b2b')->count();
            $totalDeliveryRiderAllTime = User::where('role', 'deliveryrider')->count();
            $totalSalesOfficerAllTime = User::where('role', 'salesofficer')->count();

            // -------------------------------
            // Today's new users
            // -------------------------------
            $totalB2BToday = User::where('role', 'b2b')->whereDate('created_at', $today)->count();
            $totalDeliveryRiderToday = User::where('role', 'deliveryrider')->whereDate('created_at', $today)->count();
            $totalSalesOfficerToday = User::where('role', 'salesofficer')->whereDate('created_at', $today)->count();

            // -------------------------------
            // Daily percentage based on all-time total
            // -------------------------------
            $b2bChange = $totalB2BAllTime > 0 ? ($totalB2BToday / $totalB2BAllTime) * 100 : 0;
            $riderChange = $totalDeliveryRiderAllTime > 0 ? ($totalDeliveryRiderToday / $totalDeliveryRiderAllTime) * 100 : 0;
            $salesChange = $totalSalesOfficerAllTime > 0 ? ($totalSalesOfficerToday / $totalSalesOfficerAllTime) * 100 : 0;


            /*                $totalpaylater = $creditpayment + $creditpartialpayment;

                // Pay now payments (already only paid)
                $totalpaynow = PaidPayment::where('status', 'paid')->sum('paid_amount');

                // Approved manual orders
                $totalmanualorder = ManualEmailOrder::where('status', 'approve')->get()->sum(function ($pr) {
                    $items = json_decode($pr->purchase_request, true) ?? [];

                    // Items subtotal
                    $itemsSubtotal = collect($items)->sum(fn($item) => ((int)($item['qty'] ?? 0)) * ((float)($item['price'] ?? 0)));

                    // VAT on items (12%)
                    $itemsVAT = $itemsSubtotal * 0.12;

                    // Delivery fee inclusive
                    $deliveryFee = (float) ($pr->delivery_fee ?? 0);

                    // Total for this manual order
                    return $itemsSubtotal + $itemsVAT + $deliveryFee;
                });

                // Total cash sales = pay now + approved manual orders
                $totalcashsales = $totalpaynow + $totalmanualorder;


             // Total delivery fee for approved manual orders - bagoo
            $totalDeliveryFeeManual = ManualEmailOrder::where('status', 'approve')->sum('delivery_fee');
            $totalDeliveryFeePR = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])->sum('delivery_fee');
            $totalDeliveryFeeAll = $totalDeliveryFeeManual + $totalDeliveryFeePR; */
        }


        if ($role === 'b2b') {

            $products = Product::with('inventories', 'category', 'productImages')
                ->select(['id', 'category_id', 'sku', 'name', 'description', 'price', 'discount', 'discounted_price', 'created_at', 'expiry_date']);

            $search = trim($request->input('search', ''));

            if (!empty($search)) {
                $products->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
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

        if ($role === 'salesofficer' || $role === 'deliveryrider') {
            // Current month counts
            $totalPendingPR = PurchaseRequest::where('status', 'pending')->count();
            $totalPOSubmittedPR = PurchaseRequest::where('status', 'so_created')->count();
            $totalSalesOrderPR = PurchaseRequest::where('status', 'po_submitted')->count();
            $totalDeliveredPR = PurchaseRequest::where('status', 'delivered')->count();

            // Last month range
            $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
            $endLastMonth = Carbon::now()->subMonth()->endOfMonth();

            $prevPendingPR = PurchaseRequest::where('status', 'pending')
                ->whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();

            $prevPOSubmittedPR = PurchaseRequest::where('status', 'so_created')
                ->whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();

            $prevSalesOrderPR = PurchaseRequest::where('status', 'po_submitted')
                ->whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();

            $prevDeliveredPR = PurchaseRequest::where('status', 'delivered')
                ->whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();

            // Calculate percentage changes
            $totalPendingPRChange = $prevPendingPR > 0 ? (($totalPendingPR - $prevPendingPR) / $prevPendingPR) * 100 : 0;
            $totalPOSubmittedPRChange = $prevPOSubmittedPR > 0 ? (($totalPOSubmittedPR - $prevPOSubmittedPR) / $prevPOSubmittedPR) * 100 : 0;
            $totalSalesOrderPRChange = $prevSalesOrderPR > 0 ? (($totalSalesOrderPR - $prevSalesOrderPR) / $prevSalesOrderPR) * 100 : 0;
            $totalDeliveredPRChange = $prevDeliveredPR > 0 ? (($totalDeliveredPR - $prevDeliveredPR) / $prevDeliveredPR) * 100 : 0;

            // Pay now payments (already only paid)
            $totalpaynow = PaidPayment::where('status', 'paid')->sum('paid_amount');

            // Approved manual orders
            $totalmanualorder = ManualEmailOrder::where('status', 'approve')->get()->sum(function ($pr) {
                $items = json_decode($pr->purchase_request, true) ?? [];

                // Items subtotal
                $itemsSubtotal = collect($items)->sum(fn($item) => ((int)($item['qty'] ?? 0)) * ((float)($item['price'] ?? 0)));

                // VAT on items (12%)
                $itemsVAT = $itemsSubtotal * 0.12;

                // Delivery fee inclusive
                $deliveryFee = (float) ($pr->delivery_fee ?? 0);

                // Total for this manual order
                return $itemsSubtotal + $itemsVAT + $deliveryFee;
            });

            /*                // Total cash sales = pay now + approved manual orders
                $totalcashsales = $totalpaynow + $totalmanualorder;


             // Total delivery fee for approved manual orders - bagoo
            $totalDeliveryFeeManual = ManualEmailOrder::where('status', 'approve')->sum('delivery_fee');
            $totalDeliveryFeePR = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])->sum('delivery_fee');
            $totalDeliveryFeeAll = $totalDeliveryFeeManual + $totalDeliveryFeePR; */
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
            'salesChange',
            'totalB2BAllTime',           //ito
            'totalSalesOfficerAllTime',  //ito
            'totalDeliveryRiderAllTime', //ito
            'totalPendingPR',
            'totalPOSubmittedPR',
            'totalSalesOrderPR',
            'totalDeliveredPR',
            'totalPendingPRChange',
            'totalPOSubmittedPRChange',
            'totalSalesOrderPRChange',
            'totalDeliveredPRChange',
            'totalpaynow',
            'totalcashsales',
            'totalpaylater',
            'totalDeliveryFeeAll' // <-- Add this ---- bago
        ));
    }

    public function salesRevenueData(Request $request)
    {
        $filter = $request->input('filter', 'month');
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek();
        $startOfMonth = $today->copy()->startOfMonth();

        // Helper function for discount
        $calcPrice = fn($price, $discount) => $price * (1 - ($discount / 100));

        // ============= 1. Normal PR-based totals with discount =============
        $dailyPR = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])
            ->whereDate('created_at', $today)
            ->with('items.product')
            ->get()
            ->sum(fn($pr) => $pr->items->sum(fn($i) => $i->quantity * $calcPrice($i->product->price ?? 0, $i->product->discount ?? 0)));

        $weeklyPR = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])
            ->whereBetween('created_at', [$startOfWeek, $today])
            ->with('items.product')
            ->get()
            ->sum(fn($pr) => $pr->items->sum(fn($i) => $i->quantity * $calcPrice($i->product->price ?? 0, $i->product->discount ?? 0)));

        $monthlyPR = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])
            ->whereBetween('created_at', [$startOfMonth, $today])
            ->with('items.product')
            ->get()
            ->sum(fn($pr) => $pr->items->sum(fn($i) => $i->quantity * $calcPrice($i->product->price ?? 0, $i->product->discount ?? 0)));

        // ============= 2. Manual Email Orders totals with discount =============
        $calcManual = fn($pr) => collect(json_decode($pr->purchase_request, true) ?? [])->sum(function ($i) use ($calcPrice) {
            $qty = (int)($i['qty'] ?? 0);
            $price = (float)($i['price'] ?? 0);
            $discount = (float)($i['discount'] ?? 0);
            return $qty * $calcPrice($price, $discount);
        });

        $dailyManual = ManualEmailOrder::where('status', 'approve')
            ->whereDate('created_at', $today)
            ->get()
            ->sum($calcManual);

        $weeklyManual = ManualEmailOrder::where('status', 'approve')
            ->whereBetween('created_at', [$startOfWeek, $today])
            ->get()
            ->sum($calcManual);

        $monthlyManual = ManualEmailOrder::where('status', 'approve')
            ->whereBetween('created_at', [$startOfMonth, $today])
            ->get()
            ->sum($calcManual);

        // ============= 3. Combine Totals =============
        $dailyTotal = $dailyPR + $dailyManual;
        $weeklyTotal = $weeklyPR + $weeklyManual;
        $monthlyTotal = $monthlyPR + $monthlyManual;

        // ============= 4. Chart Data with discount applied =============
        $grouped = collect();

        switch ($filter) {
            case 'day':
                for ($i = 6; $i >= 0; $i--) {
                    $key = now()->subDays($i)->format('Y-m-d');
                    $grouped->put($key, ['label' => now()->subDays($i)->format('M d'), 'value' => 0]);
                }

                $prData = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])
                    ->whereDate('created_at', '>=', now()->subDays(6))
                    ->with('items.product')
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y-m-d'))
                    ->map(fn($grp) => $grp->sum(fn($pr) => $pr->items->sum(fn($i) => $i->quantity * $calcPrice($i->product->price ?? 0, $i->product->discount ?? 0))));

                $manualData = ManualEmailOrder::where('status', 'approve')
                    ->whereDate('created_at', '>=', now()->subDays(6))
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y-m-d'))
                    ->map(fn($grp) => $grp->sum($calcManual));

                $rawData = $prData->mergeRecursive($manualData)->map(fn($v) => collect($v)->sum());
                break;

            case 'week':
                for ($i = 7; $i >= 0; $i--) {
                    $start = now()->subWeeks($i)->startOfWeek();
                    $key = $start->format('W Y');
                    $grouped->put($key, ['label' => "Week " . $start->format('W'), 'value' => 0]);
                }

                $prData = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])
                    ->whereDate('created_at', '>=', now()->subWeeks(7)->startOfWeek())
                    ->with('items.product')
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->startOfWeek()->format('W Y'))
                    ->map(fn($grp) => $grp->sum(fn($pr) => $pr->items->sum(fn($i) => $i->quantity * $calcPrice($i->product->price ?? 0, $i->product->discount ?? 0))));

                $manualData = ManualEmailOrder::where('status', 'approve')
                    ->whereDate('created_at', '>=', now()->subWeeks(7)->startOfWeek())
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->startOfWeek()->format('W Y'))
                    ->map(fn($grp) => $grp->sum($calcManual));

                $rawData = $prData->mergeRecursive($manualData)->map(fn($v) => collect($v)->sum());
                break;

            case 'year':
                for ($i = 4; $i >= 0; $i--) {
                    $key = now()->subYears($i)->format('Y');
                    $grouped->put($key, ['label' => $key, 'value' => 0]);
                }

                $prData = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])
                    ->whereDate('created_at', '>=', now()->subYears(4)->startOfYear())
                    ->with('items.product')
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y'))
                    ->map(fn($grp) => $grp->sum(fn($pr) => $pr->items->sum(fn($i) => $i->quantity * $calcPrice($i->product->price ?? 0, $i->product->discount ?? 0))));

                $manualData = ManualEmailOrder::where('status', 'approve')
                    ->whereDate('created_at', '>=', now()->subYears(4)->startOfYear())
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y'))
                    ->map(fn($grp) => $grp->sum($calcManual));

                $rawData = $prData->mergeRecursive($manualData)->map(fn($v) => collect($v)->sum());
                break;

            default: // month
                for ($i = 11; $i >= 0; $i--) {
                    $key = now()->subMonths($i)->format('Y-m');
                    $grouped->put($key, ['label' => now()->subMonths($i)->format('M Y'), 'value' => 0]);
                }

                $prData = PurchaseRequest::whereIn('status', ['delivered', 'invoice_sent'])
                    ->whereDate('created_at', '>=', now()->subMonths(12)->startOfMonth())
                    ->with('items.product')
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y-m'))
                    ->map(fn($grp) => $grp->sum(fn($pr) => $pr->items->sum(fn($i) => $i->quantity * $calcPrice($i->product->price ?? 0, $i->product->discount ?? 0))));

                $manualData = ManualEmailOrder::where('status', 'approve')
                    ->whereDate('created_at', '>=', now()->subMonths(12)->startOfMonth())
                    ->get()
                    ->groupBy(fn($pr) => Carbon::parse($pr->created_at)->format('Y-m'))
                    ->map(fn($grp) => $grp->sum($calcManual));

                $rawData = $prData->mergeRecursive($manualData)->map(fn($v) => collect($v)->sum());
                break;
        }

        // Merge chart values
        $grouped = $grouped->map(function ($item, $key) use ($rawData) {
            if ($rawData->has($key)) $item['value'] = $rawData[$key];
            return $item;
        });

        return response()->json([
            'daily' => $dailyTotal,
            'weekly' => $weeklyTotal,
            'monthly' => $monthlyTotal,
            'chart_categories' => $grouped->pluck('label')->values(),
            'chart_values' => $grouped->pluck('value')->values(),
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

    public function monthlyTopPurchasedProducts()
    {
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $data = DB::table('purchase_request_items as pri')
            ->join('purchase_requests as pr', 'pr.id', '=', 'pri.purchase_request_id')
            ->join('products as p', 'p.id', '=', 'pri.product_id')
            ->select(
                DB::raw("DATE_FORMAT(pr.created_at, '%Y-%m') as month"),
                'p.name',
                DB::raw('SUM(pri.quantity) as total_quantity')
            )
            ->where('pr.status', 'delivered')
            ->whereBetween('pr.created_at', [$start, $end])
            ->groupBy('month', 'p.name')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $label = now()->subMonths($i)->format('M Y');
            $months[$label] = [];
        }

        foreach ($data as $month => $items) {
            $label = Carbon::parse($month . '-01')->format('M Y');
            $months[$label] = $items->sortByDesc('total_quantity')->take(5)->map(function ($item) {
                return [
                    'product' => $item->name,
                    'quantity' => (int) $item->total_quantity
                ];
            })->values();
        }

        return response()->json($months);
    }

    public function summary_sales()
    {
        $page = 'Summary List of Sales';

        $purchaseRequests = PurchaseRequest::with(['items'])
            ->whereIn('status', ['delivered', 'invoice_sent'])
            ->get();

    $subtotal = $purchaseRequests->sum(function ($pr) {
        return $pr->items->sum(function ($item) {
            // ✅ prioritize unit_price, then price, then product->price
            $price = $item->unit_price ?? $item->price ?? $item->product->price ?? 0;
            $discount = $item->discount ?? $item->product->discount ?? 0;

            $discountedPrice = $price - ($price * ($discount / 100));
            return $item->quantity * $discountedPrice;
        });
    });



    $vatAmount = $purchaseRequests->sum(function ($pr) {
        $vatRate = $pr->vat ?? 12; // ✅ default 12% if missing

        $prSubtotal = $pr->items->sum(function ($item) {
            $price = $item->unit_price ?? $item->price ?? 0;
            $discount = $item->discount ?? 0;

            $discountedPrice = $price - ($price * ($discount / 100));
            return $item->quantity * $discountedPrice;
        });

        return $prSubtotal * ($vatRate / 100);
    });


        // Delivery fee (gross, includes VAT)
        $deliveryFee = $purchaseRequests->sum(fn($pr) => $pr->delivery_fee ?? 0);

        // Compute VAT portion of delivery fee
        $deliveryVAT = $deliveryFee * (0.12 / 1.12);
        $deliveryExclusive = $deliveryFee - $deliveryVAT;

        // Total VAT (sales VAT + delivery VAT)
        $totalVAT = $vatAmount + $deliveryVAT;

        // Totals
        $vatExclusive = $subtotal;
        $total = $subtotal + $vatAmount;
        $grandTotal = $total + $deliveryFee;

        return view('pages.summary_sales', compact(
            'page',
            'purchaseRequests',
            'subtotal',
            'vatAmount',
            'deliveryVAT',
            'totalVAT',
            'vatExclusive',
            'deliveryExclusive',
            'deliveryFee',
            'total',
            'grandTotal'
        ));
    }

    public function summary_sales_api($date_from, $date_to)
    {
        $dateFrom = Carbon::parse($date_from)->startOfDay();
        $dateTo   = Carbon::parse($date_to)->addDay()->startOfDay();

        $query = PurchaseRequest::with(['customer', 'address', 'detail', 'items'])
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<', $dateTo)
            ->whereIn('status', ['delivered', 'invoice_sent'])
            ->get();

        return DataTables::of($query)
            ->addColumn('created_at', fn($pr) => $pr->created_at->format('F d, Y h:i A'))
            ->addColumn('invoice_no', fn($pr) => 'INV-' . str_pad($pr->id, 5, '0', STR_PAD_LEFT))
            ->addColumn('customer', fn($pr) => ($pr->detail->business_name ?? 'No Company Name') . '/' . (optional($pr->customer)->name ?? '-'))
            ->addColumn('tin', fn($pr) => $pr->detail->tin_number ?? 'No provided tin number')
            ->addColumn('address', fn($pr) => $pr->address->full_address ?? 'No provided address')
            ->addColumn('total_items', fn($pr) => $pr->items->sum('quantity'))
            ->addColumn('avg_price', function ($pr) {
                return number_format($pr->items->avg(function ($item) {
                    $price = $item->price ?? 0;       // ✅ use stored price
                    $discount = $item->discount ?? 0; // ✅ use stored discount
                    return $price - ($price * ($discount / 100));
                }), 2);
            })
            ->addColumn('subtotal', function ($pr) {
                $subtotal = $pr->items->sum(function ($item) {
                    $price = $item->unit_price ?? $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $discountedPrice = $price - ($price * ($discount / 100));
                    return $item->quantity * $discountedPrice;
                });
                return number_format($subtotal, 2);
            })
            ->addColumn('vat_amount', function ($pr) {
                $subtotal = $pr->items->sum(function ($item) {
                    $price = $item->unit_price ?? $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $discountedPrice = $price - ($price * ($discount / 100));
                    return $item->quantity * $discountedPrice;
                });
                $vatRate = $pr->vat ?? 12;
                return number_format($subtotal * ($vatRate / 100), 2);
            })
            ->addColumn('vat_inclusive', function ($pr) {
                $subtotal = $pr->items->sum(function ($item) {
                    $price = $item->unit_price ?? $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $discountedPrice = $price - ($price * ($discount / 100));
                    return $item->quantity * $discountedPrice;
                });
                $vatRate = $pr->vat ?? 12;
                return number_format($subtotal + ($subtotal * ($vatRate / 100)), 2);
            })
            ->addColumn('delivery_fee_exclusive', function ($pr) {
                $deliveryFee = $pr->delivery_fee ?? 0;
                return number_format($deliveryFee - ($deliveryFee * (0.12 / 1.12)), 2);
            })
            ->addColumn('delivery_vat', function ($pr) {
                $deliveryFee = $pr->delivery_fee ?? 0;
                return number_format($deliveryFee * (0.12 / 1.12), 2);
            })
            ->addColumn('delivery_fee_inclusive', fn($pr) => number_format($pr->delivery_fee ?? 0, 2))
            ->addColumn('total_vat', function ($pr) {
                $subtotal = $pr->items->sum(function ($item) {
                    $price = $item->unit_price ?? $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $discountedPrice = $price - ($price * ($discount / 100));
                    return $item->quantity * $discountedPrice;
                });
                $salesVAT = $subtotal * (($pr->vat ?? 0) / 100);
                $deliveryVAT = ($pr->delivery_fee ?? 0) * (0.12 / 1.12);
                return number_format($salesVAT + $deliveryVAT, 2);
            })
            ->addColumn('grand_total', function ($pr) {
                $subtotal = $pr->items->sum(function ($item) {
                    $price = $item->unit_price ?? $item->price ?? 0;
                    $discount = $item->discount ?? 0;
                    $discountedPrice = $price - ($price * ($discount / 100));
                    return $item->quantity * $discountedPrice;
                });
                $salesVAT = $subtotal * (($pr->vat ?? 0) / 100);
                $deliveryFee = $pr->delivery_fee ?? 0;
                return number_format($subtotal + $salesVAT + $deliveryFee, 2);
            })
            ->make(true);
    }

    public function export($date_from, $date_to)
    {
        return Excel::download(new SalesSummaryExport($date_from, $date_to), 'sales_summary.xlsx');
    }


    public function summary_sales_manualorder()
    {
        $page = 'Summary List of Sales Manual Order';

        // Get all manual email orders
        $purchaseRequestsManual = ManualEmailOrder::where('status', 'approve')->get();

        // Initialize totals
        $vatExclusive = 0;         // subtotal of items only
        $salesVAT = 0;             // VAT on items only
        $totalInclusive = 0;       // subtotal + VAT
        $deliveryExclusive = 0;    // delivery fee without VAT
        $deliveryVAT = 0;          // VAT portion of delivery
        $deliveryInclusive = 0;    // delivery fee inclusive of VAT
        $totalVAT = 0;             // total VAT (items + delivery)
        $grandTotal = 0;           // total inclusive + delivery inclusive

        foreach ($purchaseRequestsManual as $pr) {
            $items = json_decode($pr->purchase_request, true) ?? [];

            // Items subtotal
            $prSubtotal = collect($items)->sum(fn($item) => (int)($item['qty'] ?? 0) * (float)($item['price'] ?? 0));

            // VAT on items
            $prVAT = $prSubtotal * 0.12;

            // Items inclusive
            $prTotalInclusive = $prSubtotal + $prVAT;

            // Delivery fee
            $prDelivery = (float) ($pr->delivery_fee ?? 0);

            // Delivery breakdown
            $prDeliveryExclusive = $prDelivery / 1.12; // remove VAT
            $prDeliveryVAT = $prDelivery - $prDeliveryExclusive;
            $prDeliveryInclusive = $prDelivery; // original delivery fee

            // Total VAT for this order
            $prTotalVAT = $prVAT + $prDeliveryVAT;

            // Grand total = items inclusive + delivery inclusive
            $prGrandTotal = $prTotalInclusive + $prDeliveryInclusive;

            // Accumulate
            $vatExclusive += $prSubtotal;
            $salesVAT += $prVAT;
            $totalInclusive += $prTotalInclusive;
            $deliveryExclusive += $prDeliveryExclusive;
            $deliveryVAT += $prDeliveryVAT;
            $deliveryInclusive += $prDeliveryInclusive;
            $totalVAT += $prTotalVAT;
            $grandTotal += $prGrandTotal;
        }

        return view('pages.summary_sales_manual', compact(
            'page',
            'purchaseRequestsManual',
            'vatExclusive',
            'salesVAT',
            'totalInclusive',
            'deliveryExclusive',
            'deliveryVAT',
            'deliveryInclusive',
            'totalVAT',
            'grandTotal'
        ));
    }



    public function summary_sales_manualorder_api($date_from, $date_to)
    {
        $query = ManualEmailOrder::where('status', 'approve')
            ->whereBetween('order_date', [$date_from, $date_to])
            ->get();

        return DataTables::of($query)
            ->addColumn('created_at', function ($pr) {
                return Carbon::parse($pr->created_at)->format('F d, Y h:i A');
            })
            ->addColumn('invoice_no', function ($pr) {
                return 'INV-' . str_pad($pr->id, 5, '0', STR_PAD_LEFT);
            })
            ->addColumn('customer', function ($pr) {
                return $pr->customer_name ?? '-';
            })
            ->addColumn('customer_type', function ($pr) {
                return $pr->customer_type ?? '-';
            })
            ->addColumn('email', function ($pr) {
                return $pr->customer_email ?? '-';
            })
            ->addColumn('address', function ($pr) {
                return $pr->customer_address ?? '-';
            })
            ->addColumn('phone', function ($pr) {
                return $pr->customer_phone_number ?? '-';
            })
            ->addColumn('order_date', function ($pr) {
                return $pr->order_date ?? '-';
            })
            ->addColumn('delivery_date', function ($pr) {
                return $pr->delivery_date ?? '-'; // NEW COLUMN
            })
            ->addColumn('total_items', function ($pr) {
                $items = json_decode($pr->purchase_request, true) ?? [];
                return collect($items)->sum(fn($item) => (int) ($item['qty'] ?? 0));
            })
            ->addColumn('avg_price', function ($pr) {
                $items = json_decode($pr->purchase_request, true) ?? [];
                if (count($items) === 0) return '0.00';
                $avg = collect($items)->avg(fn($item) => (float) ($item['price'] ?? 0));
                return number_format($avg, 2);
            })
            ->addColumn('vat_exclusive', function ($pr) {
                $items = json_decode($pr->purchase_request, true) ?? [];
                $subtotal = collect($items)->sum(fn($item) => (int)($item['qty'] ?? 0) * (float)($item['price'] ?? 0));
                return number_format($subtotal, 2);
            })
            ->addColumn('vat_amount', function ($pr) {
                $items = json_decode($pr->purchase_request, true) ?? [];
                $subtotal = collect($items)->sum(fn($item) => (int)($item['qty'] ?? 0) * (float)($item['price'] ?? 0));
                $vatAmount = $subtotal * 0.12;
                return number_format($vatAmount, 2);
            })
            ->addColumn('total_inclusive', function ($pr) {
                $items = json_decode($pr->purchase_request, true) ?? [];
                $subtotal = collect($items)->sum(fn($item) => (int)($item['qty'] ?? 0) * (float)($item['price'] ?? 0));
                $vatAmount = $subtotal * 0.12;
                return number_format($subtotal + $vatAmount, 2);
            })
            ->addColumn('grand_total', function ($pr) {
                $items = json_decode($pr->purchase_request, true) ?? [];
                $subtotal = collect($items)->sum(fn($item) => (int)($item['qty'] ?? 0) * (float)($item['price'] ?? 0));
                $vatAmount = $subtotal * 0.12;
                $deliveryFee = (float) ($pr->delivery_fee ?? 0);
                $grandTotal = $subtotal + $vatAmount + $deliveryFee;
                return number_format($grandTotal, 2);
            })
            ->make(true);
    }
    public function export_manualorder($date_from, $date_to)
    {
        return Excel::download(new SalesSummaryManualExport($date_from, $date_to), 'sales_summary.xlsx');
    }
}
