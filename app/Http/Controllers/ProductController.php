<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $availableFields = [
            'title',
            'category_id',
            'price',
            'discount',
            'description',
            'keywords',
        ];

        $product = Product::create($request->only($availableFields));

        if ($request->hasFile('thumbnail')) {
            $thumbPath = $request
                ->file('thumbnail')
                ->store('/public/product-thumbnails');

            if (!$thumbPath) {
                throw new \Exception('Thumbnail was not stored!');
            }

            $product->update(['thumbnail_path' => $thumbPath]);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ProductUpdateRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $availableFields = [
            'title',
            'category_id',
            'price',
            'discount',
            'description',
            'keywords',
        ];

        $product->update($request->only($availableFields));

        if ($request->hasFile('thumbnail')) {
            $defaultPath = 'public/product-thumbnails/default.png';

            if ($product->thumbnail_path !== $defaultPath) {
                Storage::delete($product->thumbnail_path);
            }

            $thumbPath = $request
                ->file('thumbnail')
                ->store('/public/product-thumbnails');

            if (!$thumbPath) {
                throw new \Exception('Thumbnail was not stored!');
            }

            $product->update(['thumbnail_path' => $thumbPath]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
