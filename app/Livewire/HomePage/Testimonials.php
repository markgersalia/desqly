<?php

namespace App\Livewire;

use Livewire\Component;

class Testimonials extends Component
{
    public $testimonials = [];
    public $currentIndex = 0;

    public function mount()
    {
        $this->testimonials = [
            [
                'name' => 'Sarah Mitchell',
                'service' => 'Full Body Massage',
                'content' => 'An absolutely divine experience. The therapists at HBC Wellness are incredibly skilled and the atmosphere is so peaceful. I left feeling completely rejuvenated and can\'t wait to return.',
                'rating' => 5,
            ],
            [
                'name' => 'Emily Chen',
                'service' => 'Aromatherapy',
                'content' => 'The aromatherapy session was exactly what I needed. The blend of essential oils was perfect and the massage technique was exceptional. Highly recommend this sanctuary of wellness.',
                'rating' => 5,
            ],
            [
                'name' => 'Jessica Laurent',
                'service' => 'Geothermal Spa',
                'content' => 'Pure bliss! The geothermal spa experience was transformative. The staff is attentive, professional, and genuinely caring. This is my go-to place for self-care.',
                'rating' => 5,
            ],
            [
                'name' => 'Amanda Rodriguez',
                'service' => 'Facial Treatment',
                'content' => 'My skin has never looked better! The facial treatment was luxurious and the results are amazing. The attention to detail and quality of products used is outstanding.',
                'rating' => 5,
            ],
        ];
    }

    public function nextTestimonial()
    {
        $this->currentIndex = ($this->currentIndex + 1) % count($this->testimonials);
    }

    public function previousTestimonial()
    {
        $this->currentIndex = ($this->currentIndex - 1 + count($this->testimonials)) % count($this->testimonials);
    }

    public function render()
    {
        return view('livewire.home.testimonials');
    }
}
