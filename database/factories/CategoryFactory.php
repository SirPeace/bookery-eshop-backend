<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->words(asText: true);

        return [
            'title' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(asText: true),
            'keywords' => $this->faker->words(10, asText: true),
        ];
    }
}
