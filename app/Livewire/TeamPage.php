<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TeamPage extends Component
{
    public function render()
    {
        return view('livewire.team-page', [
            'team' => config('foodhub.team'),
        ]);
    }
}
