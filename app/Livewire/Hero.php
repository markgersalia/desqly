<?php

namespace App\Livewire;

use Livewire\Component;

class Hero extends Component
{
    public $slides = [];

    public function mount()
    {
        $this->slides = [
            [
                'title' => 'Beauty Centre & Spa',
                'subtitle' => 'Your beauty truly matters to us',
                'description' => 'Welcome to HBC Wellness, a place of splendor made for you.',
                'image' => '/images/hero-1.jpg',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.home.hero');
    }
}
