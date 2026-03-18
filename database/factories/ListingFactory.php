<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'type' => $this->faker->randomElement(['service', 'room', 'event', 'misc']),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'duration' => $this->faker->randomElement([30, 60, 90, 120]),
        ];
    }
}
