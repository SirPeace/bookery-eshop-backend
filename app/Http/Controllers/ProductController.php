<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, ProductRepository $productRepository)
    {
        $paginationCount = $request['count'] ?? 20;

        $products = $productRepository
            ->withFilters()
            ->withSearch()
            ->withSort()
            ->paginate($paginationCount);

        $response = [
            "status" => "success",
            "data" => [
                "products" => $products,
            ]
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = Product::create($request->all());

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
            'status' => 'success',
            'data'   => [
                'product' => $product
            ]
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
        return response()->json([
            'status' => 'success',
            'data'   => [
                'product' => $product
            ]
        ]);
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
        $this->authorize('update', $product);

        $product->update($request->all());

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

        return response()->json([
            'status' => 'success',
            'data'   => [
                'product' => $product
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $defaultPath = 'public/product-thumbnails/default.png';

        if ($product->thumbnail_path !== $defaultPath) {
            Storage::delete($product->thumbnail_path);
        }

        $product->delete();

        return response()->json(['status' => 'success']);
    }
}
