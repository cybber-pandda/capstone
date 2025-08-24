<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shelter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Category;
use App\Models\Product;
use App\Models\ManualEmailOrder;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $page = "Welcome to TantucoCTC Hardware";

        if (Auth::check()) {
            return redirect()->route('home');
        }

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
        $product = Product::with([
            'inventories',
            'category',
            'productImages',
            'ratings.user:id,name' // Only fetch id and name for efficiency
        ])
        ->select([
            'id',
            'sku',
            'name',
            'description',
            'price',
            'discount',
            'discounted_price',
            'expiry_date',
            'created_at',
            'category_id'
        ])
        ->findOrFail($id);

        // Calculate average rating and total number of ratings
        $averageRating = $product->ratings->avg('rating'); 
        $totalRatings  = $product->ratings->count();

        return response()->json([
            'success' => true,
            'product' => $product,
            'average_rating' => $averageRating ? round($averageRating, 1) : 0,
            'total_ratings'  => $totalRatings,
        ]);
    }

    public function manual_order()
    {
        $page = "Purchase Request";

        $categories = Category::select(['id', 'name', 'image', 'description'])->get();

        $products = Product::with('category', 'productImages')
            ->select(['id', 'category_id', 'sku', 'name', 'description', 'price', 'created_at', 'expiry_date']);

        return view('pages.manual_order', compact('page', 'categories', 'products'));
    }

    public function getProductsByCategory($categoryId)
    {
        $products = Product::with('productImages')
            ->where('category_id', $categoryId)
            ->select('id', 'name', 'price')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->productImages->first()->image_path ?? null
                ];
            });

        return response()->json($products);
    }

     public function store(Request $request)
    {
         $validated = $request->validate([
            'customer_name'          => 'required|string|max:255',
            'customer_address'       => 'required|string|max:255',
            'customer_phone_number'  => ['required', 'regex:/^09\d{9}$/'],
            'order_date'             => 'required|date',
            'remarks'                => 'nullable|string|max:255',
            'products'               => 'required|array|min:1',
            'products.*.category_id' => 'required|integer',
            'products.*.product_id'  => 'required|integer',
            'products.*.qty'         => 'required|integer|min:1',
            'products.*.price'       => 'required|numeric|min:0',
        ],[
            'customer_phone_number.required' => 'Please enter the customer phone number.',
            'customer_phone_number.regex'    => 'Phone number must be 11 digits and start with 09.',
            'products.required'                => 'Please add at least one product.',
            'products.*.category_id.required'  => 'Please select a category for each product row.',
            'products.*.product_id.required'   => 'Please select a product for each product row.',
            'products.*.qty.required'          => 'Please enter quantity for each product row.',
            'products.*.qty.min'               => 'Quantity must be at least 1 in each product row.',
            'products.*.price.required'        => 'Please enter price for each product row.',
            'products.*.price.min'             => 'Price cannot be negative in any product row.',
        ]);

        if ($request->filled('order_id')) {
            // Update existing order
            $manualOrder = ManualEmailOrder::findOrFail($request->order_id);
            $manualOrder->update([
                'customer_name'         => $validated['customer_name'],
                'customer_address'      => $validated['customer_address'],
                'customer_phone_number' => $validated['customer_phone_number'],
                'order_date'            => $validated['order_date'],
                'remarks'               => $validated['remarks'] ?? null,
                'purchase_request'      => json_encode($validated['products']),
                'status'                => 'waiting'
            ]);

            $message = 'Manual purchase request updated successfully!';
        } else {
            // Create new order
            $manualOrder = ManualEmailOrder::create([
                'customer_name'         => $validated['customer_name'],
                // 'customer_email'        => $request->customer_email ?? null,
                'customer_address'      => $validated['customer_address'],
                'customer_phone_number' => $validated['customer_phone_number'],
                'order_date'            => $validated['order_date'],
                'remarks'               => $validated['remarks'] ?? null,
                'purchase_request'      => json_encode($validated['products']),
                'status'                => 'waiting'
            ]);

            $message = 'Manual purchase request saved successfully!';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $manualOrder
        ]);
    }

}
