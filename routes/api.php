<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::put('/deliveries/{id}/update-location', [App\Http\Controllers\APIController::class, 'logLocation']);
Route::get('/delivery/sse/tracking/{id}', [App\Http\Controllers\APIController::class, 'deliveryTrackingSSE']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});