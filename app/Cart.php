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
    private string $hash;

    public function __construct()
    {
        if (auth()->guest()) {
            throw new CartException(
                "Trying to get Cart for an unauthenticated user"
            );
        }

        $this->user = auth()->user();

        $this->hash = env('APP_ENV') === 'testing'
            ? "cart:test:{$this->user->id}"
            : "cart:{$this->user->id}";

        $this->setupProducts();
    }

    /**
     * Get collection of Product models in the cart
     * with count property set
     *
     * @return Illuminate\Database\Eloquent\Collection<\App\Models\Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
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
            array_key_exists($product->id, Redis::hkeys($this->hash))
            ? Redis::hget($this->hash, $product->id) + $quantity
            : $quantity;

        $product->count = $productQuantity;
        $product->price = (float) $product->price;
        $product->old_price = (float) $product->old_price;
        $product->discount = (float) $product->discount;

        $this->products[] = $product;

        Redis::hset($this->hash, $product->id, $productQuantity);
    }

    /**
     * Remove product from the cart
     *
     * @param Product $product
     * @param int $quantity
     * @return void
     */
    public function removeProduct(Product $product, int $quantity = 1): void
    {
        $cartProduct = $this->products->find($product->id);
        if (!$cartProduct) return;

        if ($quantity >= $cartProduct->count) {
            $this->products = $this->products->filter(
                fn ($item) => $item->id != $product->id
            );

            Redis::hdel($this->hash, $product->id);
            return;
        }

        $cartProduct->count -= $quantity;
        Redis::hset($this->hash, $cartProduct->id, $cartProduct->count);
    }

    /**
     * Check if cart is empty
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return $this->getProducts()->isEmpty();
    }

    /**
     * Clear the cart (destroy the hash)
     *
     * @return void
     */
    public function clear(): void
    {
        Redis::del($this->hash);
    }

    /**
     * Get plain cart hash (assoc array)
     *
     * @return array
     */
    public function getCart(): array
    {
        return Redis::hgetall($this->hash);
    }

    /**
     * Sync cart instance with Redis hash
     *
     * @return void
     */
    public function refresh(): void
    {
        $this->setupProducts();
    }

    /**
     * Load Redis hash in the products property as a collection of products
     *
     * @return void
     */
    private function setupProducts(): void
    {
        $products = Product::whereIn('id', Redis::hkeys($this->hash))->get();

        foreach ($products as $product) {
            $product->count = (int) Redis::hget($this->hash, $product->id);
            $product->price = (float) $product->price;
            $product->old_price = (float) $product->old_price;
            $product->discount = (float) $product->discount;
        }

        $this->products = $products;
    }
}
