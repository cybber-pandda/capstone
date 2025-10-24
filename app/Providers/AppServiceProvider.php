<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Category;
use App\Models\CreditPayment;
use App\Models\CreditPartialPayment;
use App\Models\B2BDetail;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\Delivery;

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
            return \App\Models\CompanySetting::first() ?? collect();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!App::runningInConsole()) {
            try {
                $companySettings = app('companySettings');
                View::share('companySettings', $companySettings);
            } catch (\Exception $e) {
                // Log or silently fail
                logger()->warning('companySettings binding failed', ['message' => $e->getMessage()]);
            }
        }

        // View::composer('*', function ($view) {
        //     $user = Auth::user();

        //     // Default Values
        //     $pendingRequestCount = 0;
        //     $sentQuotationCount = 0;
        //     $categories = Category::select(['id', 'name', 'image', 'description'])->get();
        //     $cartJson = json_encode([
        //         'items' => [],
        //         'total_quantity' => 0,
        //         'subtotal' => 0
        //     ]);

        //     $showPaymentModal = false;
        //     $overduePayment = null;
        //     $b2bDetails = null;
        //     $showPendingRequirements = false;

        //     // 🧾 B2B-specific logic
        //     if ($user && $user->role === 'b2b') {
        //         $b2bDetails = B2BDetail::where('user_id', $user->id)->first();

        //         if ($b2bDetails && $b2bDetails->status === null) {
        //            $showPendingRequirements = true;
        //         }

        //         $pendingRequestCount = PurchaseRequest::where('customer_id', $user->id)
        //             ->where('status', null)
        //             ->count();

        //         $purchaseRequest = PurchaseRequest::where('customer_id', $user->id)
        //             ->where('status', null)
        //             ->first();

        //         $sentQuotationCount = PurchaseRequest::where('customer_id', $user->id)
        //             ->where('status', 'quotation_sent')
        //             ->count();

        //         if ($purchaseRequest) {
        //             $items = $purchaseRequest->items()->with('product.productImages')->get();

        //             $mapped = $items->map(function ($item) {
        //                 $product = $item->product;
        //                 return [
        //                     'id' => $item->id,
        //                     'product_name' => $product->name,
        //                     'product_image' => asset(optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png'),
        //                     'quantity' => $item->quantity,
        //                     'price' => $product->price,
        //                     'subtotal' => $item->quantity * $product->price,
        //                 ];
        //             });

        //             $cartJson = json_encode([
        //                 'items' => $mapped->take(5),
        //                 'total_quantity' => $items->sum('quantity'),
        //                 'subtotal' => $items->sum(fn($i) => $i->quantity * $i->product->price),
        //             ]);
        //         }

        //         // Check for overdue payments through PurchaseRequest relationship
        //         $overduePayment = CreditPayment::with('purchaseRequest')
        //             ->whereHas('purchaseRequest', function ($query) use ($user) {
        //                 $query->where('customer_id', $user->id)->where('credit', 1)->where('payment_method', 'pay_later');
        //             })
        //             ->where(function ($query) {
        //                 $query->where('status', 'unpaid')
        //                     ->orWhere('status', 'partially_paid')
        //                     ->orWhere('status', 'overdue');
        //             })
        //             ->whereDate('due_date', '<', now())
        //             ->first();

        //         if ($overduePayment) {
        //             if ($overduePayment->status !== 'overdue') {
        //                 $overduePayment->update(['status' => 'overdue']);
        //                 $overduePayment->refresh();
        //             }

        //             $showPaymentModal = true;
        //         }
        //     }

        //     // Share globally
        //     $view->with([
        //         'pendingRequestCount' => $pendingRequestCount,
        //         'sentQuotationCount' =>  $sentQuotationCount,
        //         'categories' => $categories,
        //         'cartJson' => $cartJson,
        //         'showB2BModal' => null,
        //         'overduePayment' =>  $overduePayment,
        //         'showPaymentModal' => $showPaymentModal,
        //         'b2bDetails' =>  $b2bDetails,
        //         'showPendingRequirements' => $showPendingRequirements
        //     ]);
        // });

        View::composer('*', function ($view) {
            $user = Auth::user();
            
            $showDeliveryPopup = false;

            if ($user && $user->role === 'b2b') {
                $onTheWayDelivery = Delivery::whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->where('status', 'on_the_way')
                ->first();

                if ($onTheWayDelivery) {
                    $showDeliveryPopup = true;
                }
            }

            $view->with([
                'showDeliveryPopup' => $showDeliveryPopup,
            ]);

            $deliveryCount = 0;

            if ($user && $user->role === 'b2b') {
                $deliveryCount = Delivery::whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->where('status', 'on_the_way')
                ->count();
            }

            // Finally, share it globally
            $view->with([
                // ...existing shared data
                'deliveryCount' => $deliveryCount,
            ]);
            $notificationCount = 0;
            if ($user) {
                $notificationCount = \App\Models\Notification::where('user_id', $user->id)
                    ->where('read_at')
                    ->count();
            }

            // Default values
            $pendingRequestCount = 0;
            $sentQuotationCount = 0;
            $categories = Category::select(['id', 'name', 'image', 'description'])->get();
            $cartJson = json_encode([
                'items' => [],
                'total_quantity' => 0,
                'subtotal' => 0
            ]);

            $showPaymentModal = false;
            $overduePayment = null;
            $b2bDetails = null;
            $showPendingRequirements = false;

            $today = Carbon::today();

            $expiredBatches = StockBatch::whereNotNull('expiry_date')
                ->where('expiry_date', '<', $today)
                ->get(['id', 'inventory_id']);

            if ($expiredBatches->isNotEmpty()) {
                DB::table('stock_batches')
                    ->whereIn('id', $expiredBatches->pluck('id'))
                    ->update(['quantity' => 0]);

                DB::table('inventories')
                    ->whereIn('id', $expiredBatches->pluck('inventory_id')->filter())
                    ->update(['quantity' => 0]);
            }


            if ($user && $user->role === 'b2b') {
                $b2bDetails = B2BDetail::where('user_id', $user->id)->first();

                if ($b2bDetails && $b2bDetails->status === null) {
                    $showPendingRequirements = true;
                }

                $pendingRequestCount = PurchaseRequest::where('customer_id', $user->id)
                    ->whereNull('status')
                    ->count();

                $pendingRequests = PurchaseRequest::where('customer_id', $user->id)
                    ->whereNull('status')
                    ->get();

                $sentQuotationCount = PurchaseRequest::where('customer_id', $user->id)
                    ->where('status', 'quotation_sent')
                    ->count();

                if ($pendingRequests->isNotEmpty()) {
                    // Get items from ALL pending purchase requests
                    $items = PurchaseRequestItem::whereIn('purchase_request_id', $pendingRequests->pluck('id'))
                        ->with('product.productImages')
                        ->get();

                    // First take only 5 items, THEN map them
                    $displayItems = $items->take(5)->map(function ($item) {
                        $product = $item->product;
                        $price = $product->discount > 0 ? $product->discounted_price : $product->price;
                        return [
                            'id' => $item->id,
                            'product_name' => $product->name,
                            'product_image' => asset(optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png'),
                            'quantity' => $item->quantity,
                            'price' => $price,
                            'subtotal' => $item->quantity * $price,
                        ];
                    });

                    $cartJson = json_encode([
                        'items' => $displayItems,
                        'total_quantity' => $items->sum('quantity'),
                        'subtotal' => $items->sum(function ($item) {
                            $price = $item->product->discount > 0 ? $item->product->discounted_price : $item->product->price;
                            return $item->quantity * $price;
                        }),
                    ]);
                }

                // --- Overdue check for straight payments ---
                $overduePayment = CreditPayment::with('purchaseRequest')
                    ->whereHas('purchaseRequest', function ($query) use ($user) {
                        $query->where('customer_id', $user->id)
                            ->where('credit', 1)
                            ->where('payment_method', 'pay_later');
                    })
                    ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
                    ->whereDate('due_date', '<', now())
                    ->first();

                // --- Overdue check for partial payments ---
                $overduePartialPayment = CreditPartialPayment::with('purchaseRequest')
                    ->whereHas('purchaseRequest', function ($query) use ($user) {
                        $query->where('customer_id', $user->id)
                            ->where('credit', 1)
                            ->where('payment_method', 'pay_later');
                    })
                    ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
                    ->whereDate('due_date', '<', now())
                    ->first();

                // Prefer partial payment overdue if found, else straight
                if ($overduePartialPayment) {
                    $overduePayment = $overduePartialPayment;
                }

                // Update status if not already overdue
                if ($overduePayment && $overduePayment->status !== 'overdue') {
                    $overduePayment->update(['status' => 'overdue']);
                    $overduePayment->refresh();
                }

                // Show modal if there’s an overdue payment
                if ($overduePayment) {
                    $showPaymentModal = true;
                }
            } else if ($user && $user->role === 'superadmin') {
                $showCriticalStockModal = false;
                $criticalProducts = [];

                $products = Product::with('inventories')->get();

                foreach ($products as $product) {
                    $stockIn = $product->inventories->where('type', 'in')->sum('quantity');
                    $stockOut = $product->inventories->where('type', 'out')->sum('quantity');
                    $currentStock = $stockIn - $stockOut;

                    // Calculate % of critical level relative to maximum
                    $criticalPercent = 0;
                    if ($product->maximum_stock > 0) {
                        $criticalPercent = ($product->critical_stock_level / $product->maximum_stock) * 100;
                    }

                    // ✅ Trigger only if stock <= critical level
                    if ($currentStock <= $product->critical_stock_level) {
                        $criticalProducts[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'current_stock' => $currentStock,
                            'maximum_stock' => $product->maximum_stock,
                            'critical_stock_level' => $product->critical_stock_level,
                            'critical_percent' => number_format($criticalPercent, 2) . '%',
                        ];
                    }
                }


                if (count($criticalProducts) > 0) {
                    $showCriticalStockModal = true;
                }
            }

            // Share globally
            $view->with([
                'pendingRequestCount' => $pendingRequestCount,
                'sentQuotationCount' => $sentQuotationCount,
                'categories' => $categories,
                'cartJson' => $cartJson,
                'showB2BModal' => null,
                'overduePayment' => $overduePayment,
                'showPaymentModal' => $showPaymentModal,
                'b2bDetails' => $b2bDetails,
                'showPendingRequirements' => $showPendingRequirements,
                'showCriticalStockModal' => $showCriticalStockModal ?? false,
                'criticalProducts' => $criticalProducts ?? [],
                'notificationCount' => $notificationCount, // 👈 Add this line
            ]);
        });
    }
}
