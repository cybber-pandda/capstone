<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Product;

class ProductManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = 'Product Management';
        $pageCategory = 'Management';
        $user = User::getCurrentUser();

        if ($request->ajax()) {
            $products = Product::with('inventories')->select(['id', 'sku', 'name', 'description', 'price', 'expiry_date', 'created_at']);

            return DataTables::of($products)
                ->addColumn('current_stock', function ($row) {
                    return $row->current_stock;
                })
                ->addColumn('action', function ($row) {
                    return '
                    <button type="button" class="btn btn-sm btn-info view-details p-2" data-id="' . $row->id . '"><i class="link-icon" data-lucide="eye"></i></button>
                    <button type="button" class="btn btn-sm btn-inverse-light mx-1 edit p-2" data-id="' . $row->id . '"><i class="link-icon" data-lucide="edit-3"></i></button>
                    <button type="button" class="btn btn-sm btn btn-inverse-danger delete p-2" data-id="' . $row->id . '"> <i class="link-icon" data-lucide="trash-2"></i></button>
                    ';
                })
                ->make(true);
        }

        return view('pages.superadmin.v_productManagement', compact('page', 'pageCategory'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:png,jpg,webp|max:2048',
            'quantity' => 'nullable|integer|min:0',
            'reason' => 'required|string',
        ]);

        $validated['sku'] = strtoupper(uniqid('SKU-'));

        DB::transaction(function () use ($request, $validated) {
            $product = Product::create($validated);

            $mainImageIndex = $request->input('main_image_index', 0);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('assets/upload/products'), $filename);

                    $product->productImages()->create([
                        'image_path' => 'assets/upload/products/' . $filename,
                        'is_main' => $index == $mainImageIndex ? true : false,
                    ]);
                }
            }

            if ($request->quantity > 0) {
                $product->inventories()->create([
                    'type' => 'in',
                    'quantity' => $request->quantity,
                    'reason' => $request->reason,
                ]);
            }
        });

        return response()->json([
            'type' => 'success',
            'message' => 'Product created successfully.'
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::with(['productImages', 'inventories'])->findOrFail($id);

        $totalStock = $product->inventories->sum(function ($inv) {
            return $inv->type === 'in' ? $inv->quantity : -$inv->quantity;
        });

        return response()->json([
            'product' => $product,
            'images' => $product->productImages,
            'inventories' => $product->inventories,
            'stock' => $totalStock,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::with('productImages', 'inventories')->findOrFail($id);

        $totalQuantity = $product->inventories->sum(function ($inventory) {
            return $inventory->type === 'in' ? $inventory->quantity : -$inventory->quantity;
        });

        $latestInventory = $product->inventories->sortByDesc('created_at')->first();

        return response()->json([
            'product' => $product,
            'images' => $product->productImages,
            'quantity' => $totalQuantity,
            'reason' => optional($latestInventory)->reason,
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:png,jpg,webp|max:2048',
            'quantity' => 'nullable|integer|min:0',
            'main_image_index' => 'nullable|integer',
            'main_image_id' => 'nullable|integer',
            'reason' => 'required|string'
        ]);

        DB::transaction(function () use ($request, $validated, $id) {
            $product = Product::findOrFail($id);

            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'expiry_date' => $validated['expiry_date'],
                'description' => $validated['description'] ?? null,
            ]);

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

            if ($request->filled('quantity')) {
                $currentStock = $product->inventories()->sum(DB::raw("CASE WHEN type = 'in' THEN quantity WHEN type = 'out' THEN -quantity ELSE 0 END"));

                $difference = $request->quantity - $currentStock;

                if ($difference != 0) {
                    $product->inventories()->create([
                        'type' => $difference > 0 ? 'in' : 'out',
                        'quantity' => abs($difference),
                        'reason' => $request->reason,
                    ]);
                }
            }
        });

        return response()->json([
            'type' => 'success',
            'message' => 'Product updated successfully.'
        ]);
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
