<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\PurchaseRequest;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register company settings as a singleton
        $this->app->singleton('companySettings', function () {
            return DB::table('company_settings')->first();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share company settings globally
        $companySettings = app('companySettings');
        View::share('companySettings', $companySettings);

        View::composer('*', function ($view) {
            $pendingRequestCount = 0;
            $sentQuotationCount = 0;
            $categories = Category::select(['id', 'name', 'image', 'description'])->get(); // ✅ always load
            $cartJson = json_encode([
                'items' => [],
                'total_quantity' => 0,
                'subtotal' => 0
            ]);

            if (Auth::check() && Auth::user()->role === 'b2b') {
                $pendingRequestCount = PurchaseRequest::where('customer_id', Auth::id())
                    ->where('status', 'pending')
                    ->count();

                $purchaseRequest = PurchaseRequest::where('customer_id', Auth::id())
                    ->where('status', 'pending')
                    ->first();

                $sentQuotationCount = PurchaseRequest::where('customer_id', Auth::id())
                    ->where('status', 'quotation_sent')
                    ->count();

                if ($purchaseRequest) {
                    $items = $purchaseRequest->items()->with('product.productImages')->get();

                    $mapped = $items->map(function ($item) {
                        $product = $item->product;
                        return [
                            'id' => $item->id,
                            'product_name' => $product->name,
                            'product_image' => asset(optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png'),
                            'quantity' => $item->quantity,
                            'price' => $product->price,
                            'subtotal' => $item->quantity * $product->price,
                        ];
                    });

                    $cartJson = json_encode([
                        'items' => $mapped->take(5),
                        'total_quantity' => $items->sum('quantity'),
                        'subtotal' => $items->sum(fn($i) => $i->quantity * $i->product->price),
                    ]);
                }

            }

            $view->with([
                'pendingRequestCount' => $pendingRequestCount,
                'sentQuotationCount' =>  $sentQuotationCount,
                'categories' => $categories,
                'cartJson' => $cartJson
            ]);
        });
    }
}
