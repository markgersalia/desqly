<?php

namespace App\Livewire;

use Livewire\Component;

class Treatments extends Component
{
    public $treatments = [];

    public function mount()
    {
        $this->treatments = [
            [
                'name' => 'Aromatherapy',
                'price' => 48,
                'description' => 'Essential oil-based massage therapy for deep relaxation and stress relief.',
            ],
            [
                'name' => 'Sauna Relax',
                'price' => 36,
                'description' => 'Traditional sauna experience with guided relaxation techniques.',
            ],
            [
                'name' => 'Geothermal Spa',
                'price' => 42,
                'description' => 'Natural hot spring therapy for muscle tension and detoxification.',
            ],
            [
                'name' => 'Finnish Sauna',
                'price' => 36,
                'description' => 'Authentic Finnish sauna ritual with cooling intervals.',
            ],
            [
                'name' => 'Face Masks',
                'price' => 48,
                'description' => 'Customized facial masks using premium natural ingredients.',
            ],
            [
                'name' => 'Full Body Massage',
                'price' => 85,
                'description' => 'Comprehensive massage therapy tailored to your needs.',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.home.treatments');
    }
}
