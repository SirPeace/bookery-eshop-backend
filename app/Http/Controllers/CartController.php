<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of products in the cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Cart $cart)
    {
        return response()->json([
            "status" => "success",
            "data" => [
                "cart" => $cart->getProducts()
            ]
        ]);
    }

    /**
     * Update the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Remove all products from the cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        //
    }
}
