<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanyWithUsersSeeder extends Seeder
{
    /**
     * Seed three companies with associated users.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Company 1',
                'slug' => 'company-1',
                'users' => [
                    ['name' => 'Company 1 Admin', 'email' => 'company1.admin@example.com', 'password' => 'password'],
                    ['name' => 'Company 1 Staff', 'email' => 'company1.staff@example.com', 'password' => 'password'],
                ],
            ],
            [
                'name' => 'Company 2',
                'slug' => 'company-2',
                'users' => [
                    ['name' => 'Company 2 Admin', 'email' => 'company2.admin@example.com', 'password' => 'password'],
                    ['name' => 'Company 2 Staff', 'email' => 'company2.staff@example.com', 'password' => 'password'],
                ],
            ],
            [
                'name' => 'Company 3',
                'slug' => 'company-3',
                'users' => [
                    ['name' => 'Company 3 Admin', 'email' => 'company3.admin@example.com', 'password' => 'password'],
                    ['name' => 'Company 3 Staff', 'email' => 'company3.staff@example.com', 'password' => 'password'],
                ],
            ],
        ];

        foreach ($companies as $companyData) {
            $company = Company::query()->updateOrCreate(
                ['slug' => $companyData['slug']],
                [
                    'name' => $companyData['name'],
                    'plan_code' => 'starter',
                    'subscription_status' => 'active',
                    'trial_ends_at' => now()->addDays(14),
                    'current_period_ends_at' => now()->addMonth(),
                    'is_active' => true,
                ],
            );

            foreach ($companyData['users'] as $index => $userData) {
                $user = User::query()->updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'company_id' => $company->id,
                        'name' => $userData['name'],
                        'password' => $userData['password'],
                        'email_verified_at' => now(),
                    ],
                );

                // Optional role mapping if Shield roles already exist.
                if (method_exists($user, 'assignRole')) {
                    $roleName = $index === 0 ? 'Admin' : 'Staff';

                    try {
                        $user->assignRole($roleName);
                    } catch (\Throwable $e) {
                        // Ignore when role table/records are not seeded yet.
                    }
                }
            }
        }
    }
}

