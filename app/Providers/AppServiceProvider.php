<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

use App\Models\PurchaseRequest;
use App\Models\Category;
use App\Models\CreditPayment;
use App\Models\CreditPartialPayment;
use App\Models\B2BDetail;

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

            // 🧾 B2B-specific logic
            if ($user && $user->role === 'b2b') {
                $b2bDetails = B2BDetail::where('user_id', $user->id)->first();

                if ($b2bDetails && $b2bDetails->status === null) {
                    $showPendingRequirements = true;
                }

                $pendingRequestCount = PurchaseRequest::where('customer_id', $user->id)
                    ->whereNull('status')
                    ->count();

                $purchaseRequest = PurchaseRequest::where('customer_id', $user->id)
                    ->whereNull('status')
                    ->first();

                $sentQuotationCount = PurchaseRequest::where('customer_id', $user->id)
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
                'showPendingRequirements' => $showPendingRequirements
            ]);
        });
    }
}
