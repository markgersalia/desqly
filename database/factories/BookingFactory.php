<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+7 days');
        $endTime = (clone $startTime)->modify('+1 hour');

        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory()->state(fn (array $attributes) => ['company_id' => $attributes['company_id']]),
            'customer_id' => Customer::factory()->state(fn (array $attributes) => ['company_id' => $attributes['company_id']]),
            'listing_id' => Listing::factory()->state(fn (array $attributes) => ['company_id' => $attributes['company_id']]),
            'booking_number' => 'BK-' . strtoupper($this->faker->unique()->numerify('######')),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'notes' => $this->faker->optional()->sentence(),
            'title' => $this->faker->sentence(3),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'type' => $this->faker->randomElement(['service', 'rental']),
            'location' => $this->faker->optional()->address(),
            'payment_status' => 'pending',
            'therapist_id' => null,
            'bed_id' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
