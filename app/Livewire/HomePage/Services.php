<?php

namespace App\Livewire;

use Livewire\Component;

class Services extends Component
{
    public $services = [];

    public function mount()
    {
        $this->services = [
            [
                'title' => 'Mineral Salt Scrub',
                'description' => 'Exfoliate and renew your skin with our luxurious mineral-rich salt scrub treatment.',
                'image' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?q=80&w=2070',
                'link' => '#',
            ],
            [
                'title' => 'Geothermal Spa',
                'description' => 'Immerse yourself in the healing waters of our geothermal spa for ultimate relaxation.',
                'image' => 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?q=80&w=2075',
                'link' => '#',
            ],
            [
                'title' => 'Mineral Baths',
                'description' => 'Experience the therapeutic benefits of our mineral-enriched bathing rituals.',
                'image' => 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?q=80&w=2070',
                'link' => '#',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.home.services');
    }
}
