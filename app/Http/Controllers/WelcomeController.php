<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shelter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\Product;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $page = "Welcome to TantucoCTC Hardware";

        $categories = Category::select(['id', 'name', 'image', 'description'])->get();

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

        return view('pages.welcome', compact('page', 'categories', 'data'));
    }

    public function product_details($id)
    {
        $product = Product::with('inventories', 'category', 'productImages')->select(['id', 'sku', 'name', 'description', 'price', 'expiry_date', 'created_at', 'category_id'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

}
