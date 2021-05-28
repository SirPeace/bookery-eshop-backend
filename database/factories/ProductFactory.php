<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
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
        return [
            'price' => $this->faker->randomFloat(2, max: 10000),
            'category_id' => ProductCategory::factory(),
            'discount' => $this->faker->randomNumber(2),
            'title' => $this->faker->words(asText: true),
            'description' => $this->faker->paragraphs(asText: true),
            'keywords' => $this->faker->words(10, asText: true),
        ];
    }
}
