<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public Cart $cart;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    /**
     * Display a listing of products in the cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            "status" => "success",
            "data" => [
                "cart" => $this->cart->getProducts()
            ]
        ]);
    }

    /**
     * Add given product to the cart.
     *
     * @param  \App\Models\Product  $product
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addProduct(Product $product, Request $request)
    {
        $this->cart->addProduct($product, $request['count'] ?: 1);

        return response()->json([
            "status" => "success",
            "data" => [
                "cart" => $this->cart->getProducts()
            ]
        ]);
    }

    /**
     * Remove given product from the cart.
     *
     * @param  \App\Models\Product  $product
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function removeProduct(Product $product, Request $request)
    {
        $this->cart->removeProduct($product, $request['count'] ?: 1);

        return response()->json([
            "status" => "success",
            "data" => [
                "cart" => $this->cart->getProducts()
            ]
        ]);
    }

    /**
     * Remove all products from the cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $this->cart->clear();

        return response()->json([
            "status" => "success"
        ]);
    }
}
