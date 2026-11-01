<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LegalPage extends Component
{
    public string $page = 'privacy';

    public function mount(): void
    {
        $this->page = request()->routeIs('terms') ? 'terms' : 'privacy';
    }

    public function render()
    {
        $title = $this->page === 'privacy' ? 'Privacy Policy' : 'Terms & Conditions';
        $body = config('foodhub.legal.'.$this->page, '');

        return view('livewire.legal-page', [
            'title' => $title,
            'body' => $body,
        ]);
    }
}
