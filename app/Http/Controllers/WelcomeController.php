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
use App\Models\Inventory;
use App\Models\PrReserveStock;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $page = "Welcome to TantucoCTC Hardware";
        //for user auth
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $categories = Category::select(['id', 'name', 'image', 'description'])->get();

        $products = Product::with('category', 'productImages')
            ->select(['id', 'category_id', 'sku', 'name', 'description', 'price', 'discount', 'discounted_price', 'created_at', 'expiry_date']);

        if ($request->filled('search')) {
            $products->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
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
        // for cache and returning the dashboard
        return response()
            ->view('pages.welcome', compact('page', 'categories', 'data'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        //return view('pages.welcome', compact('page', 'categories', 'data'));
    }

    public function product_details($id)
    {
        $product = Product::with([
            'inventories',
            'category',
            'productImages',
            'prReserveStocks',
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

        // Calculate Reserve Stock (only pending and approved)
        $reserveStock = $product->prReserveStocks()
            ->whereIn('status', ['pending', 'approved'])
            ->sum('qty');

        // Calculate average rating and total number of ratings
        $averageRating = $product->ratings->avg('rating');
        $totalRatings  = $product->ratings->count();

        return response()->json([
            'success' => true,
            'product' => $product,
            'average_rating' => $averageRating ? round($averageRating, 1) : 0,
            'total_ratings'  => $totalRatings,
            'reserve_stock' => $reserveStock,
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
            ->select('id', 'name', 'price', 'discounted_price')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => ($product->discounted_price > 0) ? $product->discounted_price : $product->price,
                    'image' => $product->productImages->first()->image_path ?? null
                ];
            });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_type'          => 'required',
            'customer_name'          => 'required|string|max:255',
            'customer_address'       => 'required|string|max:255',
            'customer_phone_number'  => ['required', 'regex:/^09\d{9}$/'],
            'order_date'             => 'required|date',
            'remarks'                => 'nullable|string|max:255',
            'products'               => 'required|array|min:1',
            'products.*.category_id' => 'required|integer',
            'products.*.product_id'  => 'required|integer',
            'products.*.qty'         => 'required|integer|min:1',
        ], [
            'customer_phone_number.required' => 'Please enter the customer phone number.',
            'customer_phone_number.regex'    => 'Phone number must be 11 digits and start with 09.',
            'products.required'                => 'Please add at least one product.',
            'products.*.category_id.required'  => 'Please select a category for each product row.',
            'products.*.product_id.required'   => 'Please select a product for each product row.',
            'products.*.qty.required'          => 'Please enter quantity for each product row.',
            'products.*.qty.min'               => 'Quantity must be at least 1 in each product row.',
        ]);
        // foreach ($validated['products'] as &$product) {
        //     $dbProduct = Product::find($product['product_id']);
        //     $product['price'] = ($dbProduct->discounted_price > 0) ? $dbProduct->discounted_price : $dbProduct->price;

        //     // check each purchase remaing stock  verify if this is oaky 
        //     $availableStock = $product->stockBatches()->sum('remaining_quantity')
        //         - PrReserveStock::getTotalReservedStock($product->id);

        //     if ($availableStock < $validated['qty']) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Insufficient stock for product: ' . $product->name
        //         ], 400);
        //     }
        // }

        foreach ($validated['products'] as &$product) {
            $dbProduct = Product::find($product['product_id']);

            $product['price'] = ($dbProduct->discounted_price > 0)
                ? $dbProduct->discounted_price
                : $dbProduct->price;

            // Correct stock check using $dbProduct
            $availableStock = $dbProduct->stockBatches()->sum('remaining_quantity')
                - PrReserveStock::getTotalReservedStock($dbProduct->id);

            if ($availableStock < $product['qty']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for product: ' . $dbProduct->name
                ], 400);
            }
        }


        $status = $request->customer_type === 'walkin' ? 'approve' : 'pending';
        $customer_type = $request->customer_type === 'walkin' ? 'Walk-In' : 'Manual Order';


        if ($request->filled('order_id')) {
            // Update existing order
            $manualOrder = ManualEmailOrder::findOrFail($request->order_id);
            $manualOrder->update([
                'customer_name'         => $validated['customer_name'],
                'customer_type'         => $customer_type,
                'customer_address'      => $validated['customer_address'],
                'customer_phone_number' => $validated['customer_phone_number'],
                'order_date'            => $validated['order_date'],
                'remarks'               => $validated['remarks'] ?? null,
                'purchase_request'      => json_encode($validated['products']),
                'status'                => $status
            ]);

            $message = 'Manual purchase request updated successfully!';
        } else {
            $manualOrder = ManualEmailOrder::create([
                'customer_name'         => $validated['customer_name'],
                'customer_type'         => $customer_type,
                'customer_email'        => $request->customer_email ?? null,
                'customer_address'      => $validated['customer_address'],
                'customer_phone_number' => $validated['customer_phone_number'],
                'order_date'            => $validated['order_date'],
                'remarks'               => $validated['remarks'] ?? null,
                'purchase_request'      => json_encode($validated['products']),
                'status'                =>  $status
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
