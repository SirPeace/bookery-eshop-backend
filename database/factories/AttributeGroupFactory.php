<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\AttributeGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttributeGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->word();

        return [
            'title'       => $title,
            'slug'        => Str::slug($title),
            'description' => $this->faker->paragraphs(asText: true),
        ];
    }
}
