<?php

namespace App\Livewire\Owner;

use App\Livewire\Admin\ChatsIndex as AdminChatsIndex;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.owner')]
class ChatsIndex extends AdminChatsIndex
{
    public function mount(): void
    {
        $this->readOnly = false;
    }
}
