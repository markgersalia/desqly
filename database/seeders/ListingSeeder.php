<?php

namespace Database\Seeders;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListingSeeder extends Seeder
{
   
      /**
     * Run the database seeds.
     */
public function run(): void
{
    // Fetch all categories from database
    $categories = Category::all()->keyBy('name');
    
    // Comprehensive spa services data structure
    $spaServices = [
        'Massage' => [
            'titles' => [
                'Swedish Massage Therapy',
                'Deep Tissue Massage', 
                'Hot Stone Massage',
                'Couples Massage Experience',
                'Sports Therapy Massage',
                'Aromatherapy Massage'
            ],
            'types' => ['service', 'experience', 'medical'],
            'durations' => [60, 75, 90, 120],
            'prices' => [80, 150, 200, 400]
        ],
        'Facial' => [
            'titles' => [
                'Classic Hydrating Facial',
                'Deep Cleansing Facial',
                'Anti-Aging Treatment',
                'Luxury Hydrafacial',
                'Chemical Peel Treatment',
                'LED Light Therapy'
            ],
            'types' => ['service', 'experience', 'medical'],
            'durations' => [45, 60, 75],
            'prices' => [80, 150, 250, 400]
        ],
        'Hair Salon' => [
            'titles' => [
                'Women\'s Cut & Style',
                'Professional Hair Coloring',
                'Keratin Smooth Treatment',
                'Bridal Hair & Makeup',
                'Hair Extension Service',
                'Men\'s Premium Cut'
            ],
            'types' => ['service', 'experience', 'room'],
            'durations' => [45, 90, 120, 180, 240],
            'prices' => [60, 150, 300, 600]
        ],
        'Nail Care' => [
            'titles' => [
                'Spa Manicure',
                'Luxury Pedicure',
                'Gel Nail Application',
                'Custom Nail Art',
                'Acrylic Full Set',
                'Dip Powder Manicure'
            ],
            'types' => ['service', 'experience'],
            'durations' => [30, 45, 60, 90],
            'prices' => [25, 60, 100, 150]
        ],
        'Wellness' => [
            'titles' => [
                'Yoga Session',
                'Meditation Class',
                'Reiki Healing',
                'Acupuncture Treatment',
                'Salt Cave Therapy',
                'Detox Body Wrap'
            ],
            'types' => ['service', 'experience', 'medical', 'room'],
            'durations' => [45, 60, 75, 90],
            'prices' => [25, 80, 150, 300]
        ]
    ];

    // Create 25 spa-focused listings
    for ($i = 1; $i <= 25; $i++) {
        // Random category
        $categoryName = array_rand($spaServices);
        $categoryServices = $spaServices[$categoryName];
        $category = $categories->get($categoryName);
        
        // Random service details
        $title = $categoryServices['titles'][array_rand($categoryServices['titles'])];
        $type = $categoryServices['types'][array_rand($categoryServices['types'])];
        $duration = $categoryServices['durations'][array_rand($categoryServices['durations'])];
        $price = $categoryServices['prices'][array_rand($categoryServices['prices'])];

        DB::table('listings')->insert([
            'title' => $title,
            'description' => $this->generateSpaDescription($title, $categoryName),
            'type' => $type,
            'category_id' => $category->id,
            'duration' => $duration,
            'price' => $price + rand(-10, 10), // Add some variation
            'available_from' => Carbon::now()->addDays(rand(0, 30)),
            'available_to' => Carbon::now()->addDays(rand(31, 90)),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
    }

    /**
     * Generate professional spa descriptions based on service and category
     */
    private function generateSpaDescription($title, $categoryName): string
    {
        $descriptions = [
            'Massage' => [
                'Experience ultimate relaxation with our professional massage therapy. Our certified therapists use advanced techniques to relieve tension, improve circulation, and promote deep relaxation.',
                'Indulge in a rejuvenating massage experience designed to melt away stress and restore your body\'s natural balance. Using premium oils and personalized techniques.',
                'Transform your well-being with our therapeutic massage services. Perfect for relieving muscle tension, reducing stress, and enhancing overall physical and mental health.'
            ],
            'Facial' => [
                'Reveal your natural glow with our customized facial treatments. Using professional-grade products and advanced techniques for immediate visible results.',
                'Experience luxury skincare with our specialized facial services. Our expert aestheticians personalize each treatment to address your unique skin concerns.',
                'Restore and rejuvenate your skin with our comprehensive facial therapies. Combining proven techniques with premium skincare products for radiant results.'
            ],
            'Hair Salon' => [
                'Transform your look with our professional hair services. Our expert stylists create personalized styles using premium products and the latest techniques.',
                'Experience luxury hair care in our elegant salon environment. From precision cuts to stunning color transformations, we deliver exceptional results.',
                'Elevate your style with our comprehensive hair services. Our talented team combines artistic vision with technical expertise for your perfect look.'
            ],
            'Nail Care' => [
                'Pamper yourself with our luxurious nail care services. Using premium products and sanitary techniques for beautiful, long-lasting results.',
                'Indulge in our spa-quality nail treatments in a relaxing environment. From classic manicures to intricate nail art, we prioritize your nail health.',
                'Experience nail perfection with our professional nail services. Our skilled technicians ensure both beauty and hygiene for your complete satisfaction.'
            ],
            'Wellness' => [
                'Embark on a holistic wellness journey with our specialized treatments. Designed to balance mind, body, and spirit for optimal well-being.',
                'Transform your health with our comprehensive wellness services. Our practitioners combine traditional wisdom with modern techniques for healing.',
                'Discover inner peace and vitality through our wellness therapies. Each session is tailored to your unique needs for maximum benefit.'
            ]
        ];

        $categoryDescriptions = $descriptions[$categoryName] ?? $descriptions['Wellness'];
        
        return $categoryDescriptions[array_rand($categoryDescriptions)] . ' ' . $title . ' provides exceptional results in a serene, professional environment.';
    }
}
