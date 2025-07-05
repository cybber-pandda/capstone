<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\b2bDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
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
        $data = null;

        $role = $user->role ?? null;

        $view = match ($role) {
            'b2b' => 'pages.b2b.index',
            'deliveryrider', 'salesofficer' => 'pages.admin.index',
            'superadmin' => 'pages.superadmin.index',
            default => 'pages.welcome', // fallback for guests or unknown roles
        };

        if ($role === 'b2b') {
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
        }

        return view($view, compact('page', 'data'));
    }

}
