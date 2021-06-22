<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

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

Route::resource('products', ProductController::class)
    ->scoped(['product' => 'slug'])
    ->except(['create', 'edit']);

Route::resource('orders', OrderController::class)
    ->except(['create', 'edit']);

Route::resource('categories', CategoryController::class)
    ->except(['create', 'edit']);

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::patch('/', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/', [CartController::class, 'destroy'])->name('cart.destroy');
});
