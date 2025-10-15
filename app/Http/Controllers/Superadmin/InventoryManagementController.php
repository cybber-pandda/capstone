<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;

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
                    'inventory_breakdown' => $breakdownHtml
                ];
            });

            return datatables()->of($data)->rawColumns(['inventory_breakdown'])->make(true);
        }

        return view('pages.superadmin.v_inventoryManagement', [
            'page' => 'Inventory Managment',
            'pageCategory' => 'Management',
            'product_select' => $product_select
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'quantity' => 'required|integer',
    //         'type' => 'required|in:in,out',
    //         'reason' => 'nullable|string',
    //     ]);

    //     Product::findOrFail($validated['product_id'])
    //         ->inventories()
    //         ->create([
    //             'type' => $validated['type'],
    //             'quantity' => abs($validated['quantity']),
    //             'reason' => $validated['reason'],
    //         ]);

    //     return response()->json([
    //         'type' => 'success',
    //         'message' => 'Inventory record created successfully.',
    //     ]);
    // }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'quantity' => 'required|integer|min:1',
    //         'type' => 'required|in:in,out',
    //         'reason' => 'nullable|string',
    //     ]);

    //     $product = Product::with('inventories')->findOrFail($validated['product_id']);

    //     $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
    //     $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
    //     $currentStock = $stockIn - $stockOut;

    //     if ($validated['type'] === 'in') {
    //         if ($currentStock + $validated['quantity'] > $product->maximum_stock) {
    //             return response()->json([
    //                 'type' => 'error',
    //                 'message' => 'Adding this stock would exceed the maximum stock limit (' . $product->maximum_stock . ').'
    //             ], 422);
    //         }
    //     } elseif ($validated['type'] === 'out') {
    //         if ($currentStock - $validated['quantity'] < 0) {
    //             return response()->json([
    //                 'type' => 'error',
    //                 'message' => 'Insufficient stock available. Current stock is ' . $currentStock . '.'
    //             ], 422);
    //         }
    //     }

    //     $product->inventories()->create([
    //         'type' => $validated['type'],
    //         'quantity' => abs($validated['quantity']),
    //         'reason' => $validated['reason'],
    //     ]);

    //     return response()->json([
    //         'type' => 'success',
    //         'message' => 'Inventory record created successfully.',
    //     ]);
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:in,out',
            'reason' => 'nullable|string',
        ]);

        $product = Product::with('inventories')->findOrFail($validated['product_id']);

        // Current stock calculation
        $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
        $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
        $currentStock = $stockIn - $stockOut;

        if ($validated['type'] === 'in') {
            // Check maximum stock limit
            if ($currentStock + $validated['quantity'] > $product->maximum_stock) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Adding this stock would exceed the maximum stock limit (' . $product->maximum_stock . ').'
                ], 400);
            }
        } elseif ($validated['type'] === 'out') {
            // Prevent negative stock
            if ($currentStock - $validated['quantity'] < 0) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Insufficient stock available. Current stock is ' . $currentStock . '.'
                ], 400);
            }
        }

        // Save inventory movement
        $product->inventories()->create([
            'type' => $validated['type'],
            'quantity' => abs($validated['quantity']),
            'reason' => $validated['reason'],
        ]);

        // Calculate % of critical stock relative to maximum
        $criticalPercent = 0;
        if ($product->maximum_stock > 0) {
            $criticalPercent = ($product->critical_stock_level / $product->maximum_stock) * 100;
        }

        // Check if new stock is below critical
        $newStock = $validated['type'] === 'in'
            ? $currentStock + $validated['quantity']
            : $currentStock - $validated['quantity'];

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
    }


}
