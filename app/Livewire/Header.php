<?php

namespace App\Livewire;

use Livewire\Component;

class Header extends Component
{
    public $isScrolled = false;
    public $mobileMenuOpen = false;

    public function toggleMobileMenu()
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
    }

    public function render()
    {
        return view('livewire.home.header');
    }
}
