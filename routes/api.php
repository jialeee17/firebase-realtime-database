<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Firebase APIs
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/rtdb')->name('rtdb.')->group(function () {
        Route::post('/store-message', [FirebaseController::class, 'storeMessage']);
        Route::post('/update-admin-name', [FirebaseController::class, 'updateAdminName']);
        Route::post('/update-read-status', [FirebaseController::class, 'updateReadStatus']);
        Route::post('/migrate-customer-messages', [FirebaseController::class, 'migrateCustomerMessages']);
        Route::delete('/delete-chat', [FirebaseController::class, 'deleteChat']);
    });
});