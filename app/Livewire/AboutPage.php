<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AboutPage extends Component
{
    public function render()
    {
        return view('livewire.about-page', [
            'about' => config('foodhub.about'),
            'site' => config('foodhub.site'),
        ]);
    }
}
