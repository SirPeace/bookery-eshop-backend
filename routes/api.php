<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeGroupController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('attributes', AttributeController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('attribute_groups', AttributeGroupController::class)
        ->parameters(['attribute_groups' => 'group'])
        ->scoped(['attribute_group' => 'slug'])
        ->only(['store', 'update', 'destroy']);
});

Route::apiResource('products', ProductController::class)
    ->scoped(['product' => 'slug']);

Route::apiResource('orders', OrderController::class);

Route::apiResource('categories', CategoryController::class);

Route::apiResource('attributes', AttributeController::class)
    ->except(['store', 'update', 'destroy']);

Route::apiResource('attribute_groups', AttributeGroupController::class)
    ->parameters(['attribute_groups' => 'group'])
    ->scoped(['attribute_group' => 'slug'])
    ->except(['store', 'update', 'destroy']);

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/{product}', [CartController::class, 'addProduct'])->name('cart.add_product');
    Route::delete('/{product}', [CartController::class, 'removeProduct'])->name('cart.remove_product');
    Route::delete('/', [CartController::class, 'destroy'])->name('cart.destroy');
});
