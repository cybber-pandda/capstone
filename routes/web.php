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
Route::get('/products/{id}', [App\Http\Controllers\WelcomeController::class, 'show']);

// routes/web.php
Route::post('/login/ajax', [App\Http\Controllers\Auth\LoginController::class, 'ajaxLogin']);

Auth::routes(['verify' => true]);

Route::get('/secure-js-file/{filename}', [App\Http\Controllers\SecureController::class, 'serveJsFile'])->name('secure.js');
Route::post('/verify/code',  [App\Http\Controllers\Auth\VerificationController::class, 'otp_verify']);

Route::get('/google/redirect', [App\Http\Controllers\Auth\GoogleLoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [App\Http\Controllers\Auth\GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

Route::middleware(['prevent-back-history', 'auth', 'verified'])->group(function () {
    Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('b2b-details-form', [App\Http\Controllers\HomeController::class, 'b2b_details_form']);


    /* SUPERADMIN */
    Route::resource('product-management', App\Http\Controllers\Superadmin\ProductManagementController::class);
    Route::resource('user-management', App\Http\Controllers\Superadmin\UserManagementController::class);
    Route::get('inventory-management', [App\Http\Controllers\Superadmin\InventoryManagementController::class, 'index'])->name('inventory');
    Route::post('inventory-management', [App\Http\Controllers\Superadmin\InventoryManagementController::class, 'store'])->name('inventory.store');
    
    Route::resource('b2b-creation', App\Http\Controllers\Superadmin\B2BController::class);
    Route::resource('delivery-rider-creation', App\Http\Controllers\Superadmin\DeliveryRiderController::class);
    Route::resource('account-sales-creation', App\Http\Controllers\Superadmin\AccountSalesOfficerController::class);

    Route::get('user-report', [App\Http\Controllers\Superadmin\ReportController::class, 'user_report'])->name('user.report');
    Route::get('delivery-report', [App\Http\Controllers\Superadmin\ReportController::class, 'delivery_report'])->name('delivery.report');
    Route::get('inventory-report', [App\Http\Controllers\Superadmin\ReportController::class, 'inventory_report'])->name('inventory.report');

    //General Setting
    Route::get('generalsettings', [App\Http\Controllers\GeneralSettingsController::class, 'index'])->name('generalsettings');
    Route::post('generalsettings-company', [App\Http\Controllers\GeneralSettingsController::class, 'company']);
    Route::post('generalsettings-profile', [App\Http\Controllers\GeneralSettingsController::class, 'profile']);
    Route::post('generalsettings-account', [App\Http\Controllers\GeneralSettingsController::class, 'account']);
    Route::post('generalsettings-password', [App\Http\Controllers\GeneralSettingsController::class, 'password']);
});
