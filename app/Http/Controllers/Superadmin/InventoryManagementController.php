<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;

class InventoryManagementController extends Controller
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

        if ($user->role === 'superadmin') {

            $product_select = Product::select('name', 'id')->get();

            if ($request->ajax()) {
                $products = Product::with('inventories')->get();

                $data = $products->map(function ($product) {

                    // Stock calculation
                    $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
                    $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
                    $currentStock = $stockIn - $stockOut;

                    // Group and format breakdown
                    $breakdown = $product->inventories
                        ->groupBy(['type', 'reason'])
                        ->map(function ($groupedByReason) {
                            return $groupedByReason->map(function ($items) {
                                return $items->sum('quantity');
                            });
                        });

                    $breakdownHtml = '';
                    foreach ($breakdown as $type => $reasons) {
                        $breakdownHtml .= "<strong>" . ucfirst($type) . "</strong><ul>";
                        foreach ($reasons as $reason => $qty) {
                            $breakdownHtml .= "<li>$reason: $qty</li>";
                        }
                        $breakdownHtml .= "</ul>";
                    }

                    return [
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'created_at' => $product->created_at,
                        'price' => number_format($product->price, 2),
                        'stockIn' => $stockIn,
                        'stockOut' => $stockOut,
                        'current_stock' => $currentStock,
                        'inventory_breakdown' => $breakdownHtml,
                        'action' => '<a href="' . route('inventory.fifo', $product->id) . '" 
                                        class="btn btn-sm btn-outline-light">
                                        🔍 View FIFO
                                    </a>',
                    ];
                });

                return datatables()->of($data)->rawColumns(['inventory_breakdown', 'action'])->make(true);
            }

            return view('pages.superadmin.v_inventoryManagement', [
                'page' => 'Inventory Managment',
                'pageCategory' => 'Management',
                'product_select' => $product_select
            ]);
        }
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function getFIFO(Request $request, $id)
    {
        // 1️⃣ If user is NOT logged in → redirect to login
        if (!Auth::check()) {
            $page = 'Sign In';
            $companysettings = DB::table('company_settings')->first();

            return response()
                ->view('auth.login', compact('page', 'companysettings'))
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        }

        // 2️⃣ If logged in → verify role
        $user = Auth::user();

        if ($user->role !== 'superadmin') {
            return redirect()->route('home')->with('info', 'Access denied.');
        }

        // 3️⃣ Get product and its batches
        $product = Product::with('stockBatches')->findOrFail($id);

        if ($request->ajax()) {
            $fifoData = $product->stockBatches->map(function ($batch, $index) {
                // Determine status based on remaining quantity
                $status = '🟢 Available';
                if ($batch->remaining_quantity <= 0) {
                    $status = '🔴 Depleted';
                } elseif ($batch->remaining_quantity < $batch->quantity) {
                    $status = '🟡 Partially Sold';
                }

                return [
                    'batch_no' => $index + 1,
                    'quantity' => $batch->quantity,
                    'remaining' => $batch->remaining_quantity,
                    // 'cost' => number_format($batch->cost_price, 2),
                    'received_date' => $batch->received_date
                        ? Carbon::parse($batch->received_date)->format('Y-m-d')
                        : '-',
                    'note' => $batch->note ?? '-',
                    'status' => $status,
                ];
            });

            return datatables()->of($fifoData)->make(true);
        }

        return view('pages.superadmin.v_fifoBreakdown', [
            'page' => 'FIFO Breakdown',
            'pageCategory' => 'Management',
            'product' => $product
        ]);
    }


    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'quantity' => 'required|integer|min:1',
    //         'type' => 'required|in:in,out',
    //         'reason' => 'nullable|string',
    //     ]);

    //     $product = Product::with('inventories')->findOrFail($validated['product_id']);

    //     // Current stock calculation
    //     $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
    //     $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
    //     $currentStock = $stockIn - $stockOut;

    //     if ($validated['type'] === 'in') {
    //         // Check maximum stock limit
    //         if ($currentStock + $validated['quantity'] > $product->maximum_stock) {
    //             return response()->json([
    //                 'type' => 'error',
    //                 'message' => 'Adding this stock would exceed the maximum stock limit (' . $product->maximum_stock . ').'
    //             ], 400);
    //         }
    //     } elseif ($validated['type'] === 'out') {
    //         // Prevent negative stock
    //         if ($currentStock - $validated['quantity'] < 0) {
    //             return response()->json([
    //                 'type' => 'error',
    //                 'message' => 'Insufficient stock available. Current stock is ' . $currentStock . '.'
    //             ], 400);
    //         }
    //     }

    //     // Save inventory movement
    //     $inventory = $product->inventories()->create([
    //         'type' => $validated['type'],
    //         'quantity' => abs($validated['quantity']),
    //         'reason' => $validated['reason'],
    //     ]);

    //     // ✅ If stock IN → create new batch
    //     if ($validated['type'] === 'in') {
    //         StockBatch::create([
    //             'product_id' => $product->id,
    //             'quantity' => $validated['quantity'],
    //             'remaining_quantity' => $validated['quantity'],
    //             'cost_price' => $request->input('cost_price', null),
    //             'received_date' => now(),
    //         ]);
    //     }

    //     // ✅ If stock OUT → update FIFO batches
    //     if ($validated['type'] === 'out') {
    //         $neededQty = $validated['quantity'];
    //         $batches = StockBatch::where('product_id', $product->id)
    //             ->where('remaining_quantity', '>', 0)
    //             ->orderBy('received_date', 'asc')
    //             ->get();

    //         foreach ($batches as $batch) {
    //             if ($neededQty <= 0) break;

    //             $takeQty = min($batch->remaining_quantity, $neededQty);
    //             $batch->decrement('remaining_quantity', $takeQty);
    //             $neededQty -= $takeQty;
    //         }
    //     }

    //     // Calculate % of critical stock relative to maximum
    //     $criticalPercent = 0;
    //     if ($product->maximum_stock > 0) {
    //         $criticalPercent = ($product->critical_stock_level / $product->maximum_stock) * 100;
    //     }

    //     // Check if new stock is below critical
    //     $newStock = $validated['type'] === 'in'
    //         ? $currentStock + $validated['quantity']
    //         : $currentStock - $validated['quantity'];

    //     $warning = null;
    //     if ($newStock <= $product->critical_stock_level) {
    //         $warning = "⚠ Stock level is at or below critical threshold (" .
    //             $product->critical_stock_level . " units, ~" .
    //             number_format($criticalPercent, 2) . "% of maximum stock).";
    //     }

    //     return response()->json([
    //         'type' => 'success',
    //         'message' => 'Inventory record created successfully.',
    //         'current_stock' => $newStock,
    //         'critical_percent' => number_format($criticalPercent, 2) . '%',
    //         'warning' => $warning,
    //     ]);
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
            'type' => 'required|in:in,out',
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::with('inventories')->findOrFail($validated['product_id']);

            // Compute stock
            $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
            $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
            $currentStock = $stockIn - $stockOut;

            if ($validated['type'] === 'in') {
                if ($currentStock + $validated['quantity'] > $product->maximum_stock) {
                    return response()->json([
                        'type' => 'error',
                        'message' => 'Adding this stock would exceed the maximum stock limit (' . $product->maximum_stock . ').'
                    ], 400);
                }
            } elseif ($validated['type'] === 'out') {
                if ($currentStock - $validated['quantity'] < 0) {
                    return response()->json([
                        'type' => 'error',
                        'message' => 'Insufficient stock available. Current stock is ' . $currentStock . '.'
                    ], 400);
                }
            }

            // Save inventory
            $inventory = $product->inventories()->create([
                'type' => $validated['type'],
                'quantity' => abs($validated['quantity']),
                'expiry_date' => $validated['expiry_date'],
                'reason' => $validated['reason'],
            ]);

            // ✅ Stock IN: create new batch
            if ($validated['type'] === 'in') {
                $batch = StockBatch::create([
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'remaining_quantity' => $validated['quantity'],
                    'cost_price' => $request->input('cost_price', $product->price),
                    'received_date' => Carbon::now(),
                    'note' => $validated['reason'] ?? 'Stock added manually',
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'type' => 'in',
                    'quantity' =>  $validated['quantity'],
                    'reference' => $validated['reason'] ?? 'Stock added manually'
                ]);
            }

            // ✅ Stock OUT: FIFO deduction
            if ($validated['type'] === 'out') {
                $neededQty = $validated['quantity'];
                $batches = StockBatch::where('product_id', $product->id)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('received_date', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($neededQty <= 0) break;

                    $takeQty = min($batch->remaining_quantity, $neededQty);
                    $batch->decrement('remaining_quantity', $takeQty);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'batch_id' => $batch->id,
                        'type' => 'out',
                        'quantity' =>  $validated['quantity'],
                        'reference' => $validated['reason'] ?? 'Stock added manually'
                    ]);

                    if (!empty($validated['reason'])) {
                        $batch->note = trim(($batch->note ? $batch->note . ' | ' : '') . $validated['reason']);
                        $batch->save();
                    }

                    $neededQty -= $takeQty;
                }

                // If not enough batch stock
                if ($neededQty > 0) {
                    DB::rollBack();
                    return response()->json([
                        'type' => 'error',
                        'message' => 'Not enough batch stock to fulfill this quantity.',
                    ], 400);
                }
            }

            DB::commit();

            // Stock summary
            $newStock = $validated['type'] === 'in'
                ? $currentStock + $validated['quantity']
                : $currentStock - $validated['quantity'];

            $criticalPercent = $product->maximum_stock > 0
                ? ($product->critical_stock_level / $product->maximum_stock) * 100
                : 0;

            $warning = null;
            if ($newStock <= $product->critical_stock_level) {
                $warning = "⚠ Stock level is at or below critical threshold (" .
                    $product->critical_stock_level . " units, ~" .
                    number_format($criticalPercent, 2) . "% of maximum stock).";
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Inventory record created successfully.',
                'current_stock' => $newStock,
                'critical_percent' => number_format($criticalPercent, 2) . '%',
                'warning' => $warning,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'type' => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
