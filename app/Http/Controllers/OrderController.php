<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\EmptyCartException;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Order::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role->slug === 'admin' || $user->role->slug === 'manager') {
            $orders = Order::all();
        } else {
            $orders = Order::where('user_id', $user->id)->get();
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'orders' => $orders
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\OrderStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderStoreRequest $request, Cart $cart)
    {
        if ($cart->isEmpty()) {
            throw new EmptyCartException("Can't create order with empty cart");
        }

        $order = Order::create($request->all());

        foreach ($cart->getProducts() as $product) {
            DB::table('order_product')->insert([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_title' => $product->title,
                'product_old_price' => $product->old_price,
                'product_price' => $product->price,
                'product_discount' => $product->discount,
                'product_count' => $product->count,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\OrderUpdateRequest  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        $order->update($request->all());

        return response()->json([
            'status' => 'success',
            'data'   => [
                'order' => $order
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['status' => 'success']);
    }
}
