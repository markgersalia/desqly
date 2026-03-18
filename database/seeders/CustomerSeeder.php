<?php

namespace Database\Seeders;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $companyId = Company::query()->value('id');

        for ($i = 1; $i <= 20; $i++) {
            DB::table('customers')->insert([
                'company_id' => $companyId,
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->optional()->phoneNumber,
                'address' => $faker->optional()->address,
                'is_vip' => $faker->boolean(20),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
