<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

use App\Models\Product;

class InventoryManagementController extends Controller
{
    public function index(Request $request)
    {

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
            'product_select' =>  $product_select
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'type' => 'required|in:in,out',
            'reason' => 'nullable|string',
        ]);

        Product::findOrFail($validated['product_id'])
            ->inventories()
            ->create([
                'type' => $validated['type'],
                'quantity' => abs($validated['quantity']),
                'reason' => $validated['reason'],
            ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Inventory record created successfully.',
        ]);
    }
}
