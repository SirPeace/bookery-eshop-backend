<?php

namespace Database\Factories;

use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->words(2, asText: true);

        return [
            'name' => $name,
            'alias' => ucwords($name)
        ];
    }
}
