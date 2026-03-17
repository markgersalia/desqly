<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category; // make sure your model exists

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

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'description' => $category['description'],
                'slug' => Str::slug($category['name']),
            ]);
        }
    }
}
