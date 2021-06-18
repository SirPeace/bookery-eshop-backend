<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paginationCount = $request['count'] ?? 20;
        $user = User::query()->find(auth()->id());

        $products = Product::query()
            ->select(
                'products.id',
                'products.category_id',
                'products.old_price',
                'products.price',
                'products.discount',
                'products.title',
                'products.slug',
                'products.thumbnail_path',
                'products.description',
                'products.keywords'
            )
            ->when(
                $request['category'],
                fn (Builder $query) => $query->where(
                    'category_id',
                    $request['category']
                )
            )
            ->when(
                $request['min_price'],
                fn (Builder $query) => $query->where(
                    'price',
                    '>=',
                    $request['min_price']
                )
            )
            ->when(
                $request['max_price'],
                fn (Builder $query) => $query->where(
                    'price',
                    '<=',
                    $request['max_price']
                )
            )
            ->when(
                $request['with_thumbnail'],
                fn (Builder $query) => $query->where(
                    'thumbnail_path',
                    '<>',
                    'public/product-thumbnails/default.png'
                )
            )
            ->when(
                $request['attribute'] && $request['attribute_value'],
                fn (Builder $query) => $query
                    ->join(
                        'attribute_product',
                        'products.id',
                        '=',
                        'attribute_product.product_id'
                    )
                    ->join(
                        'attributes',
                        'attributes.id',
                        '=',
                        'attribute_product.attribute_id'
                    )
                    ->join(
                        'attribute_groups',
                        'attribute_groups.id',
                        '=',
                        'attributes.group_id'
                    )
                    ->where('attribute_groups.slug', $request['attribute'])
                    ->where('attributes.value', $request['attribute_value'])
            )
            ->when(
                !in_array($user?->role->slug, ['admin', 'manager']),
                fn (Builder $query) => $query->where('active', true)
            )
            ->when(
                $request['sort'],
                fn (Builder $query) => $query->orderBy(
                    "products.{$request['sort']}",
                    $request['order'] ?? 'asc'
                )
            )
            ->when(
                !$request['sort'],
                fn (Builder $query) => $query->latest('products.created_at')
            )
            ->when(
                $search = $request['search'],
                fn (Builder $query) => $query
                    ->join(
                        'attribute_product',
                        'products.id',
                        '=',
                        'attribute_product.product_id'
                    )
                    ->join(
                        'attributes',
                        'attributes.id',
                        '=',
                        'attribute_product.attribute_id'
                    )
                    ->join(
                        'attribute_groups',
                        'attribute_groups.id',
                        '=',
                        'attributes.group_id'
                    )
                    ->where('products.title', 'like', "%$search%")
                    ->orWhere(function (Builder $query) use ($search) {
                        $query->where('attributes.value', 'like', "%$search%")
                            ->whereNotIn(
                                'attribute_groups.slug',
                                ['weight', 'language']
                            );
                    })
                    ->orWhere('products.description', 'like', "%$search%")
                    ->orWhere('products.keywords', 'like', "%$search%")
            )
            ->paginate($paginationCount);

        return response()->json([
            "status" => "success",
            "data" => [
                "products" => $products,
            ]
        ]);
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
