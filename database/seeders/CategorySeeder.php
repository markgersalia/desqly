<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Massage',
                'description' => 'Relaxing and therapeutic massage services.',
            ],
            [
                'name' => 'Facial',
                'description' => 'Skincare and facial treatments for healthy skin.',
            ],
            [
                'name' => 'Hair Salon',
                'description' => 'Haircuts, styling, coloring, and treatments.',
            ],
            [
                'name' => 'Nail Care',
                'description' => 'Manicure, pedicure, and nail art services.',
            ],
            [
                'name' => 'Wellness',
                'description' => 'Wellness and holistic health services.',
            ],
        ];

        $companyId = Company::query()->value('id');

        foreach ($categories as $category) {
            Category::create([
                'company_id' => $companyId,
                'name' => $category['name'],
                'description' => $category['description'],
                'slug' => Str::slug($category['name']),
            ]);
        }
    }
}
