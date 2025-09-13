<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Illuminate\View\View;

class ReferenceSearchNew extends Component
{
    public bool $is_sidebar = true;

    public function mount(bool $isSidebar = true): void
    {
        $this->is_sidebar = $isSidebar;
    }

    public function render(): View
    {
        return view('livewire.reference-search-new');
    }
}