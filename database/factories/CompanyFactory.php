<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'plan_code' => 'starter',
            'subscription_status' => 'active',
            'trial_ends_at' => now()->addDays(14),
            'current_period_ends_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }
}

