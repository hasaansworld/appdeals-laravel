<?php

use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TwitterLoginController;
use Illuminate\Support\Facades\Route;

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

require __DIR__.'/auth.php';

Route::get('/login/google', [GoogleLoginController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/login/google/callback', [GoogleLoginController::class, 'handleGoogleCallback']);

Route::get('/login/twitter', [TwitterLoginController::class, 'redirectToTwitter'])->name('auth.twitter');
Route::get('/login/twitter/callback', [TwitterLoginController::class, 'handleTwitterCallback']);

Route::get('/', [TestController::class, 'home']);
