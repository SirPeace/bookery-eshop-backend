<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use PDOException;

class ProductRepository
{
    private Builder $query;
    private ?User $user;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->query = Product::query()->select(
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
        );

        $this->request = $request;

        $this->user = auth()->user();
    }

    /**
     * Apply filtering logic to the query
     *
     * @return self
     */
    public function withFilters(): self
    {
        $this->query = $this->query
            ->when(
                $this->request['category'],
                fn (Builder $query) => $query->where(
                    'category_id',
                    $this->request['category']
                )
            )
            ->when(
                $this->request['min_price'],
                fn (Builder $query) => $query->where(
                    'price',
                    '>=',
                    $this->request['min_price']
                )
            )
            ->when(
                $this->request['max_price'],
                fn (Builder $query) => $query->where(
                    'price',
                    '<=',
                    $this->request['max_price']
                )
            )
            ->when(
                $this->request['with_thumbnail'],
                fn (Builder $query) => $query->where(
                    'thumbnail_path',
                    '<>',
                    'public/product-thumbnails/default.png'
                )
            )
            ->when(
                $this->request['attribute'] && $this->request['attribute_value'],
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
                    ->where('attribute_groups.slug', $this->request['attribute'])
                    ->where('attributes.value', $this->request['attribute_value'])
            );

        return $this;
    }

    /**
     * Apply searching logic to the query.
     *
     * @return self
     */
    public function withSearch(): self
    {
        $this->query = $this->query->when(
            $search = $this->request['search'],
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
        );

        return $this;
    }

    /**
     * Apply sorting logic to the query
     *
     * @return self
     */
    public function withSort(): self
    {
        $this->query = $this->query->when(
            $sort = $this->request['sort'],
            function (Builder $query) use ($sort) {
                if ($sort === 'popularity') {
                    return $query
                        ->join(
                            'order_product',
                            'order_product.product_id',
                            '=',
                            'products.id'
                        )
                        ->orderBy(
                            'order_product.product_count',
                            $this->request['order'] ?? 'asc'
                        );
                }

                return $query->orderBy(
                    "products.{$this->request['sort']}",
                    $this->request['order'] ?? 'asc'
                );
            }
        );

        return $this;
    }

    /**
     * Get paginated products
     *
     * @param int $paginationCount
     * @return Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $paginationCount): LengthAwarePaginator
    {
        return $this
            ->getFinalQuery()
            ->paginate($paginationCount);
    }

    /**
     * Get the collection of products
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function get(): Collection
    {
        return $this
            ->getFinalQuery()
            ->get();
    }

    /**
     * Get query with the default logic applied
     *
     * @return Builder
     */
    private function getFinalQuery(): Builder
    {
        return $this->query
            ->when(
                !in_array($this->user?->role->slug, ['admin', 'manager']),
                fn (Builder $query) => $query->where('active', true)
            )
            ->when(
                !$this->request['sort'],
                fn (Builder $query) => $query->latest('products.created_at')
            );
    }
}
