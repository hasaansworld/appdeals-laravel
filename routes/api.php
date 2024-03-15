<?php

use App\Http\Controllers\ListingsController;
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

// add 'verified' if you want to get email verification error 409
Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/create-listing', [ListingsController::class, 'createListing']);
    Route::get('/can-submit', [ListingsController::class, 'canSubmit']);
});

Route::get('/all-listings', [ListingsController::class, 'getAllListings']);
Route::get('/get-listing/{name_id}', [ListingsController::class, 'getListing']);
Route::get('/random-listings', [ListingsController::class, 'getRandomListings']);
Route::get('/listings-count', [ListingsController::class, 'getListingsCount']);
