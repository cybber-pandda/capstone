<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
Route::get('/product/details/{id}', [App\Http\Controllers\WelcomeController::class, 'product_details'])->name('product.details');

Route::get('/customer-email-order', [App\Http\Controllers\WelcomeController::class, 'manual_order'])->name('manual-order.process');
Route::get('/manual-order/products/{category}', [App\Http\Controllers\WelcomeController::class, 'getProductsByCategory'])->name('manual-order.products');
Route::post('/manual-order/store', [App\Http\Controllers\WelcomeController::class, 'store'])->name('manualorder.store');

// routes/web.php
Route::post('/login/ajax', [App\Http\Controllers\Auth\LoginController::class, 'ajaxLogin']);

Auth::routes(['verify' => true]);

Route::post('/custom-login', [App\Http\Controllers\Auth\LoginController::class, 'customLogin'])->name('custom.login');

Route::get('/secure-js-file/{filename}', [App\Http\Controllers\SecureController::class, 'serveJsFile'])->name('secure.js');
Route::post('/verify/code',  [App\Http\Controllers\Auth\VerificationController::class, 'otp_verify']);

Route::get('/google/redirect', [App\Http\Controllers\Auth\GoogleLoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [App\Http\Controllers\Auth\GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

Route::middleware(['prevent-back-history', 'auth', 'verified', 'check.status'])->group(function () { //check status

    /* Super Admin */

    // Management
    Route::resource('product-management', App\Http\Controllers\Superadmin\ProductManagementController::class);
    Route::resource('category-management', App\Http\Controllers\Superadmin\CategoryController::class);
    Route::resource('user-management', App\Http\Controllers\Superadmin\UserManagementController::class);
    Route::get('inventory-management', [App\Http\Controllers\Superadmin\InventoryManagementController::class, 'index'])->name('inventory');
    Route::post('inventory-management', [App\Http\Controllers\Superadmin\InventoryManagementController::class, 'store'])->name('inventory.store');
    Route::get('/inventory-management/fifo/{id}', [App\Http\Controllers\Superadmin\InventoryManagementController::class, 'getFIFO'])->name('inventory.fifo');
    Route::resource('bank-management', App\Http\Controllers\Superadmin\BankManagementController::class);

    // Account Creation
    Route::resource('b2b-creation', App\Http\Controllers\Superadmin\B2BController::class);
    Route::resource('deliveryrider-creation', App\Http\Controllers\Superadmin\DeliveryRiderController::class);
    Route::resource('salesofficer-creation', App\Http\Controllers\Superadmin\SalesOfficerController::class);

    // Report
    Route::get('user-report', [App\Http\Controllers\Superadmin\ReportController::class, 'userReport'])->name('user.report');
    Route::get('delivery-report', [App\Http\Controllers\Superadmin\ReportController::class, 'deliveryReport'])->name('delivery.report');
    Route::get('inventory-report', [App\Http\Controllers\Superadmin\ReportController::class, 'inventoryReport'])->name('inventory.report');
    Route::get('expired-product-report', [App\Http\Controllers\Superadmin\ReportController::class, 'expiredProductReport'])->name('expired.product.report');

    // Tracking
    Route::get('submitted_po', [App\Http\Controllers\Superadmin\TrackingController::class, 'submittedPO'])->name('tracking.submitted-po');
    Route::get('/purchase-requests/{id}', [App\Http\Controllers\Superadmin\TrackingController::class, 'show']);
    Route::put('/process-so/{id}', [App\Http\Controllers\Superadmin\TrackingController::class, 'processSO']);
    Route::get('/delivery/location', [App\Http\Controllers\Superadmin\TrackingController::class, 'deliveryLocation'])->name('tracking.delivery.location');
    Route::get('/delivery/tracking/{id}', [App\Http\Controllers\Superadmin\TrackingController::class, 'deliveryTracking'])->name('tracking.delivery.tracking');
    Route::post('/delivery/upload-proof', [App\Http\Controllers\Superadmin\TrackingController::class, 'uploadProof'])->name('tracking.delivery.upload-proof');
    Route::get('/delivery-personnel', [App\Http\Controllers\Superadmin\TrackingController::class, 'deliveryPersonnel'])->name('tracking.delivery-personnel');
    Route::post('/assign-delivery-personnel', [App\Http\Controllers\Superadmin\TrackingController::class, 'assignDeliveryPersonnel'])->name('tracking.assign-delivery-personnel');
    Route::get('/b2b/requirements', [App\Http\Controllers\Superadmin\TrackingController::class, 'b2bRequirements'])->name('tracking.b2b.requirement');
    Route::post('/b2b/requirement/update-status', [App\Http\Controllers\Superadmin\TrackingController::class, 'updateStatus']);
   
    
    /* Sales Officer */
    Route::prefix('salesofficer')->name('salesofficer.')->group(function () {
        Route::get('/purchase-requests/all', [App\Http\Controllers\SalesOfficer\PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
        Route::get('/purchase-requests/{id}', [App\Http\Controllers\SalesOfficer\PurchaseRequestController::class, 'show']);
        Route::put('/purchase-requests/s-q/{id}', [App\Http\Controllers\SalesOfficer\PurchaseRequestController::class, 'updateSendQuotation']);
        Route::put('/purchase-requests/r-q/{id}', [App\Http\Controllers\SalesOfficer\PurchaseRequestController::class, 'updateRejectQuotation']);
        Route::get('/send-quotations/all', [App\Http\Controllers\SalesOfficer\QuotationsController::class, 'index'])->name('send-quotations.index');
        Route::get('/submitted-order/all', [App\Http\Controllers\SalesOfficer\OrderController::class, 'index'])->name('submitted-order.index');
        Route::get('/sales-invoice/all', [App\Http\Controllers\SalesOfficer\OrderController::class, 'sales_invoice'])->name('sales.invoice');
        Route::get('/sales-invoice/{id}', [App\Http\Controllers\SalesOfficer\OrderController::class, 'show_sales_invoice'])->name('sales.invoice.show');
        Route::post('/submit-sales-invoice', [App\Http\Controllers\SalesOfficer\OrderController::class, 'submit_sales_invoice'])->name('sales.invoice.submit');
        Route::get('/paynow/all', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'paynow'])->name('paynow.index');
        Route::post('/paynow/manual', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'manualPayment'])->name('paynow.manual');
        Route::post('/paynow/approve/{id}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'approvePayment'])->name('paynow.approve');
        Route::get('/paylater/all', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'paylater'])->name('paylater.index');
        Route::post('/paylater/approve/{id}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'approvePaylaterPayment'])->name('paylater.approve');
        Route::get('/paylater/partial/all/{id}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'paylaterPartial'])->name('paylater.partial.index');
        Route::post('/paylater/partial-payment/approve/{id}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'approvePartialPaylaterPayment'])->name('paylater.partial.approve');
        Route::post('/paylater/reject/payment/{id}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'reject_payment'])->name('paylater.reject.payment');
        Route::get('/account-receivable/all', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'account_receivable'])->name('account-receivable.index');
        Route::get('/ar-pr-table/{userid}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'account_receivable_pr']);
        Route::get('/ar-details/{userid}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'account_receivable_details'])->name('account-receivable-details.index');
        Route::get('/ar-payments/{prid}', [App\Http\Controllers\SalesOfficer\ACPaymentController::class, 'account_receivable_payments']);
        Route::get('/email-manual-order', [App\Http\Controllers\SalesOfficer\EmailManualOrderController::class, 'index'])->name('email.manual.order');
        Route::post('/submit-email-manual-order', [App\Http\Controllers\SalesOfficer\EmailManualOrderController::class, 'submit_manual_order'])->name('submit.email-manual.order');
        Route::post('/manual-email-order/approve', [App\Http\Controllers\SalesOfficer\EmailManualOrderController::class, 'approve'])->name('manualemailorder.approve');
        Route::post('/manual-email-order/delivery-fee', [App\Http\Controllers\SalesOfficer\EmailManualOrderController::class, 'delivery_fee'])->name('manualemailorder.delivery.fee');
        Route::get('/return-refund/all', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'index'])->name('return-refund.index');
        Route::get('/return-refund/data', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'data'])->name('return-refund.data');
        Route::get('/return-details/{return}', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'returnDetails'])->name('return-refund.return-details');
        Route::get('/refund-details/{refund}', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'refundDetails'])->name('return-refund.refund-details');
        Route::post('/return/{return}/approve', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'approveReturn'])->name('return.approve');
        Route::post('/return/{return}/reject', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'rejectReturn'])->name('return.reject');
        Route::post('/refund/{refund}/approve', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'approveRefund'])->name('refund.approve');
        Route::post('/refund/{refund}/reject', [App\Http\Controllers\SalesOfficer\ReturnRefundController::class, 'rejectRefund'])->name('refund.reject');
    });

    /* Delivery */
    Route::prefix('deliveryrider')->name('deliveryrider.')->group(function () {
        Route::put('/delivery/pickup/{id}', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'deliveryPickup']);
        Route::get('/delivery/location', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'deliveryLocation'])->name('delivery.location');
        Route::get('/delivery/tracking/{id}', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'deliveryTracking'])->name('delivery.tracking');
        Route::get('/delivery/orders', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'deliveryOrders'])->name('delivery.orders');
        Route::get('/delivery/orders/{id}/items', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'getOrderItems'])->name('delivery.orderItems');
        Route::get('/delivery/histories', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'deliveryHistories'])->name('delivery.histories');
        Route::get('/delivery/history/{order}', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'getDeliveryDetails']);
        Route::post('/delivery/upload-proof', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'uploadProof'])->name('delivery.upload-proof');
        Route::post('/delivery/cancel/{id}', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'cancelDelivery'])->name('delivery.cancel');
        Route::get('/delivery/ratings', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'deliveryRatings'])->name('delivery.ratings');
        Route::get('/delivery/sales-inv/{prId}/{customerId}', [App\Http\Controllers\DeliveryRider\DeliveryController::class, 'show_sales_inv'])->name('delivery.sales.inv');
    });

    /* B2B */
    Route::prefix('b2b')->name('b2b.')->group(function () {

        Route::post('/business/requirement', [App\Http\Controllers\B2B\B2BController::class, 'business_requirement'])->name('business.requirement');

        Route::get('/profile', [App\Http\Controllers\B2B\B2BController::class, 'index'])->name('profile.index');
        Route::put('/profile/update', [App\Http\Controllers\B2B\B2BController::class, 'update'])->name('profile.update');
        Route::post('/profile/upload', [App\Http\Controllers\B2B\B2BController::class, 'upload'])->name('profile.upload');

        Route::get('/purchase-requests', [App\Http\Controllers\B2B\PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
        Route::post('/purchase-requests/store', [App\Http\Controllers\B2B\PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
        Route::put('/purchase-requests/item/{id}', [App\Http\Controllers\B2B\PurchaseRequestController::class, 'updateItem'])->name('purchase-requests.updateItem');
        Route::post('/purchase-requests/submit', [App\Http\Controllers\B2B\PurchaseRequestController::class, 'submitItem'])->name('purchase-requests.submitItem');
        Route::delete('/purchase-requests/items/{id}', [App\Http\Controllers\B2B\PurchaseRequestController::class, 'deleteItem'])->name('purchase-requests.deleteItem');

        Route::get('/quotations/review', [App\Http\Controllers\B2B\QuotationController::class, 'review'])->name('quotations.review');
        Route::get('/quotations/review/{id}', [App\Http\Controllers\B2B\QuotationController::class, 'show'])->name('quotations.show');
        Route::post('/quotations/cancel/{id}', [App\Http\Controllers\B2B\QuotationController::class, 'cancelQuotation'])->name('quotations.cancel');

        Route::post('/quotations/payment/paylater', [App\Http\Controllers\B2B\QuotationController::class, 'payLater'])->name('quotations.payment.paylater');
        Route::post('/quotations/payment/upload', [App\Http\Controllers\B2B\QuotationController::class, 'uploadPaymentProof'])->name('quotations.payment.upload');
        Route::get('/quotations/status/{id}', [App\Http\Controllers\B2B\QuotationController::class, 'checkStatus']);
        Route::get('/quotation/pdfdownload/{id}', [App\Http\Controllers\B2B\QuotationController::class, 'downloadQuotation'])->name('quotation.download');


        Route::resource('address', App\Http\Controllers\B2B\B2BAddressController::class);
        Route::get('/geocode', [App\Http\Controllers\B2B\B2BAddressController::class, 'geoCode']);
        Route::post('/address/set-default', [App\Http\Controllers\B2B\B2BAddressController::class, 'setDefault']);

        Route::get('/delivery', [App\Http\Controllers\B2B\DeliveryController::class, 'index'])->name('delivery.index');
        Route::get('/delivery/track/{id}', [App\Http\Controllers\B2B\DeliveryController::class, 'track_delivery'])->name('delivery.track.index');
        Route::get('/delivery/invoice/{id}', [App\Http\Controllers\B2B\DeliveryController::class, 'view_invoice'])->name('delivery.invoice');
        Route::get('/delivery/receipt/{id}', [App\Http\Controllers\B2B\DeliveryController::class, 'view_receipt'])->name('delivery.receipt');
        Route::get('/delivery/invoice/download/{id}', [App\Http\Controllers\B2B\DeliveryController::class, 'downloadInvoice'])->name('delivery.invoice.download');
        Route::get('/delivery/rider/rate/{id}', [App\Http\Controllers\B2B\DeliveryController::class, 'rate_page'])->name('delivery.rider.rate');
        Route::post('/delivery/rider/rate/{id}', [App\Http\Controllers\B2B\DeliveryController::class, 'save_rating'])->name('delivery.rider.rate.submit');

        Route::get('/delivery/product/rate/{order_number}', [App\Http\Controllers\B2B\DeliveryController::class, 'rate_product_page'])->name('delivery.product.rate');
        Route::post('/delivery/product/rate/{product}', [App\Http\Controllers\B2B\DeliveryController::class, 'submit_product_rating'])->name('delivery.product.rate.submit');
        Route::post('/delivery/product/rate/all/{orderId}', [App\Http\Controllers\B2B\DeliveryController::class, 'submit_all_product_ratings'])->name('delivery.product.rate.submit.all');
        Route::post('delivery/{order}/rate-all', [App\Http\Controllers\B2B\DeliveryController::class, 'submit_all_ratings'])->name('delivery.all.ratings.submit');


        Route::get('/purchase', [App\Http\Controllers\B2B\PurchaseController::class, 'index'])->name('purchase.index');
        Route::post('/purchase/return', [App\Http\Controllers\B2B\PurchaseController::class, 'requestReturn']);
        Route::post('/purchase/refund', [App\Http\Controllers\B2B\PurchaseController::class, 'requestRefund']);
        Route::get('/purchase/return-refund/data', [App\Http\Controllers\B2B\PurchaseController::class, 'purchaseReturnRefund'])->name('purchase.rr');

        Route::get('/purchase/credit', [App\Http\Controllers\B2B\CreditController::class, 'index'])->name('purchase.credit');
        Route::post('/purchase/credit/payment', [App\Http\Controllers\B2B\CreditController::class, 'credit_payment'])->name('purchase.credit.payment');
        Route::get('/purchase/partial-payments', [App\Http\Controllers\B2B\CreditController::class, 'partialPayments'])->name('purchase.partial-payments');

        Route::get('/my-purchase-order', [App\Http\Controllers\B2B\B2BController::class, 'my_purchase_order'])->name('purchase.order');
        Route::get('/my-purchase-order/show/{id}', [App\Http\Controllers\B2B\B2BController::class, 'show_po'])->name('purchase.order.show');
    });


    //Chat
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/users', [App\Http\Controllers\ChatController::class, 'getUsers']);
    Route::get('/chat/messages/{recipientId}', [App\Http\Controllers\ChatController::class, 'getMessages']);
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage']);
    Route::get('/recent-messages', [App\Http\Controllers\ChatController::class, 'recentMessage']);

    //Notification
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notification.index');
    Route::get('/notifications/api', [App\Http\Controllers\NotificationController::class, 'notificationsApi']);
    Route::post('/notifications/mark-all-selected', [App\Http\Controllers\NotificationController::class, 'markSelectedAsRead']);

    Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/api/sales-revenue-data', [App\Http\Controllers\HomeController::class, 'salesRevenueData']);
    Route::get('/api/monthly-top-products', [App\Http\Controllers\HomeController::class, 'monthlyTopPurchasedProducts']);
    Route::get('/api/inventory-pie-data', [App\Http\Controllers\HomeController::class, 'inventoryPieData']);
    Route::post('b2b-details-form', [App\Http\Controllers\HomeController::class, 'b2b_details_form']);
    Route::resource('terms', App\Http\Controllers\TermsController::class);

    Route::get('company/settings', [App\Http\Controllers\SettingsController::class, 'company'])->name('company.settings');
    Route::post('/company/update', [App\Http\Controllers\SettingsController::class, 'updateCompany'])->name('company.update');
    Route::get('profile/settings', [App\Http\Controllers\SettingsController::class, 'profile'])->name('profile.settings');
    Route::get('user-profile/settings', [App\Http\Controllers\SettingsController::class, 'getUserProfile']);
    Route::post('/profile/update', [App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('profile.update');

    Route::get('summary-sales', [App\Http\Controllers\HomeController::class, 'summary_sales'])->name('summary.sales');
    Route::get('/summary-sales-api/{date_from}/{date_to}', [App\Http\Controllers\HomeController::class, 'summary_sales_api'])->name('summary.sales.api');
    Route::get('/download/summary-sales/export/{date_from}/{date_to}', [App\Http\Controllers\HomeController::class, 'export'])->name('sales-summary.export');

    Route::get('summary-sales-manualorder', [App\Http\Controllers\HomeController::class, 'summary_sales_manualorder'])->name('summary.sales.manualorder');
    Route::get('/summary-sales-manualorder-api/{date_from}/{date_to}', [App\Http\Controllers\HomeController::class, 'summary_sales_manualorder_api'])->name('summary.sales.manualorder.api');
    Route::get('/download/summary-sales-manualorder/export/{date_from}/{date_to}', [App\Http\Controllers\HomeController::class, 'export_manualorder'])->name('sales-summary.manualorder.export');
});
