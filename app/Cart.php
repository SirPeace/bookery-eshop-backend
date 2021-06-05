<?php

namespace App;

use App\Models\User;
use App\Models\Product;
use App\Exceptions\CartException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Collection;

class Cart
{
    # Carts are stored in Redis with key: "cart:$userID"
    # Each cart record is a hash which represents [product_id => count]

    private User $user;
    private Collection $products;
    private string $redisHash;

    public function __construct()
    {
        if (auth()->guest()) {
            throw new CartException(
                "Trying to get Cart for an unauthenticated user"
            );
        }

        $this->user = auth()->user();

        $this->redisHash = env('APP_ENV') === 'testing'
            ? "cart:test:{$this->user->id}"
            : "cart:{$this->user->id}";
    }

    /**
     * Get collection of Product models in the cart
     * with count property set
     *
     * @return Illuminate\Database\Eloquent\Collection<\App\Models\Product>
     */
    public function getProducts(): Collection
    {
        if (!empty($this->products)) {
            return $this->products;
        }

        $products = Product::whereIn('id', Redis::hkeys($this->redisHash))
            ->get();

        foreach ($products as $product) {
            $product->count = Redis::hget($this->redisHash, $product->id);
        }

        $this->products = $products;

        return $products;
    }

    /**
     * Add product to the cart
     *
     * @param Product $product
     * @param int $quantity
     * @return void
     */
    public function addProduct(Product $product, int $quantity = 1): void
    {
        $productQuantity =
            array_key_exists($product->id, Redis::hkeys($this->redisHash))
            ? Redis::hget($this->redisHash, $product->id) + $quantity
            : $quantity;

        $product->count = $productQuantity;

        if (!empty($this->products)) {
            $this->products[] = $product;
        }

        Redis::hset($this->redisHash, $product->id, $productQuantity);
    }

    public function removeProduct(Product $product): void
    {
        if (!empty($this->products)) {
            $this->products->filter(fn ($item) => $item->id != $product->id);
        }

        Redis::hdel($this->redisHash, $product->id);
    }

    /**
     * Get plain cart hash (assoc array)
     *
     * @return array
     */
    public function getCart(): array
    {
        return Redis::hgetall($this->redisHash);
    }
}
