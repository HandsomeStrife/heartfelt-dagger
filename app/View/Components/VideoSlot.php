<?php

namespace App\View\Components;

use Closure;
use Domain\Character\Data\CharacterData;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VideoSlot extends Component
{
    public int $slotId;

    public ?CharacterData $character;

    /**
     * Create a new component instance.
     */
    public function __construct(int $slotId, ?CharacterData $character)
    {
        $this->slotId = $slotId;
        $this->character = $character;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.video-slot');
    }
}
