<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_note' => $this->faker->paragraph(),
            'status_id' => OrderStatus::factory(),
            'user_id' => User::factory(),
        ];
    }
}
