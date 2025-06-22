<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

use App\Models\Product;

class ReportController extends Controller
{

    public function user_report(Request $request)
    {
        return view('pages.superadmin.v_userReport', [
            'page' => 'User Report',
            'pageCategory' => 'Reports',
        ]);
    }

    public function delivery_report(Request $request)
    {
        return view('pages.superadmin.v_deliveryReport', [
            'page' => 'Delivery Report',
            'pageCategory' => 'Reports',
        ]);
    }

    public function inventory_report(Request $request)
    {
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
        ]);
    }
}
