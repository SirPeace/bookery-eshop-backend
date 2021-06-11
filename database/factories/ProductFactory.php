<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $oldPrice = $this->faker->randomFloat(2, min: 10, max: 10000);
        $price = $this->faker->randomFloat(
            nbMaxDecimals: 2,
            min: $oldPrice * 0.35,
            max: $oldPrice * 0.9
        );
        $discount = intval((1 - $price / $oldPrice) * 100);

        return [
            'price' => $price,
            'old_price' => $oldPrice,
            'category_id' => Category::factory(),
            'discount' => $discount,
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->paragraphs(asText: true),
            'keywords' => $this->faker->words(10, asText: true),
        ];
    }
}
