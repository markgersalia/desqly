<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->optional()->address(),
            'is_vip' => false,
        ];
    }

    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_vip' => true,
        ]);
    }
}
