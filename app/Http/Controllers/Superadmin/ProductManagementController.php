<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class ProductManagementController extends Controller
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
        $page = 'Product Management';
        $pageCategory = 'Management';
        $user = User::getCurrentUser();
        
        $category_select = Category::select('name', 'id')->get();

        if ($request->ajax()) {
           $products = Product::with('inventories', 'category')->select(['id', 'sku', 'name', 'description', 'price', 'discount', 'discounted_price', 'maximum_stock', 'critical_stock_level', 'expiry_date', 'created_at', 'category_id']);

            return DataTables::of($products)
                ->addColumn('price', function ($row) {
                    if ($row->discount > 0) {
                        return $row->discounted_price;
                    }
                    return $row->price;
                })
                ->addColumn('current_stock', fn($row) => $row->current_stock)
                ->addColumn('category', fn($row) => optional($row->category)->name ?? 'N/A')
                ->addColumn('discount', function ($row) {
                    if ($row->discount) {
                        return $row->discount . '%';
                    }
                    return '--';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" class="btn btn-sm btn-info view-details p-2" data-id="' . $row->id . '"><i class="link-icon" data-lucide="eye"></i></button>
                        <button type="button" class="btn btn-sm btn-inverse-light mx-1 edit p-2" data-id="' . $row->id . '"><i class="link-icon" data-lucide="edit-3"></i></button>
                        <button type="button" class="btn btn-sm btn-inverse-danger delete p-2" data-id="' . $row->id . '"><i class="link-icon" data-lucide="trash-2"></i></button>
                    ';
                })
                ->rawColumns(['discount', 'price', 'action'])
                ->make(true);
        }

        return view('pages.superadmin.v_productManagement', compact('page', 'pageCategory', 'category_select'));}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'maximum_stock' => 'required|numeric',
            'critical_stock_level' => 'required|numeric',
            'category_id' => 'required|numeric',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:png,jpg,webp|max:2048',
        ]);

        $validated['sku'] = strtoupper(uniqid('SKU-'));

        if (!empty($validated['discount'])) {
            if ($validated['discount'] > 100) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Discount cannot exceed 100%.'
                ], 400);
            }
            $validated['discounted_price'] = $validated['price'] - ($validated['price'] * ($validated['discount'] / 100));
        }

        DB::transaction(function () use ($request, $validated) {
            $product = Product::create($validated);
            $mainImageIndex = $request->input('main_image_index', 0);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('assets/upload/products'), $filename);

                    $product->productImages()->create([
                        'image_path' => 'assets/upload/products/' . $filename,
                        'is_main' => $index == $mainImageIndex,
                    ]);
                }
            }
        });

        return response()->json([
            'type' => 'success',
            'message' => 'Product created successfully.'
        ]);
    }
    public function show($id)
    {
        $product = Product::with(['productImages', 'inventories'])->findOrFail($id);

        $totalStock = $product->inventories->sum(fn($inv) =>
            $inv->type === 'in' ? $inv->quantity : -$inv->quantity
        );

        return response()->json([
            'product' => $product,
            'images' => $product->productImages,
            'inventories' => $product->inventories,
            'stock' => $totalStock,
        ]);
    }

    public function edit($id)
    {
        $product = Product::with('productImages', 'inventories')->findOrFail($id);

        return response()->json([
            'product' => $product,
            'images' => $product->productImages,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'maximum_stock' => 'required|numeric',
            'critical_stock_level' => 'required|numeric',
            'discount' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'required|numeric',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:png,jpg,webp|max:2048',
            'main_image_index' => 'nullable|integer',
            'main_image_id' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $validated, $id) {
            $product = Product::findOrFail($id);
            
            $discount = $validated['discount'] ?? 0;
            $discountedPrice = $discount > 0
                ? $validated['price'] - ($validated['price'] * ($discount / 100))
                : $validated['price'];

            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'discount' => $validated['discount'],
                'discounted_price' => $discountedPrice,
                'maximum_stock' => $validated['maximum_stock'],
                'critical_stock_level' => $validated['critical_stock_level'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'] ?? null,
            ]);
            // ✅ Update pending PR items with the new unit price
            $product->purchaseRequestItems()
                ->whereHas('purchaseRequest', fn($q) => $q->whereNull('status')) // only pending PRs
                ->each(function ($item) use ($product) {
                    $price = $product->discount > 0 && $product->discounted_price
                        ? $product->discounted_price
                        : $product->price;
                    $item->unit_price = $price;
                    $item->subtotal = round($item->quantity * $price, 2);
                    $item->save();
                });

            if ($request->filled('main_image_id')) {
                $product->productImages()->update(['is_main' => false]);
                $product->productImages()
                    ->where('id', $request->main_image_id)
                    ->update(['is_main' => true]);
            }

            if ($request->hasFile('images')) {
                // Delete old images
                foreach ($product->productImages as $image) {
                    $path = public_path($image->image_path);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    $image->delete();
                }

                $mainImageIndex = $request->input('main_image_index', 0);

                foreach ($request->file('images') as $index => $image) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('assets/upload/products'), $filename);

                    $product->productImages()->create([
                        'image_path' => 'assets/upload/products/' . $filename,
                        'is_main' => $index == $mainImageIndex,
                    ]);
                }
            }
        });

        return response()->json([
            'type' => 'success',
            'message' => 'Product updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Product deleted successfully.'
        ]);
    }
}
