<?php

declare(strict_types=1);

namespace App\View\Components\CharacterViewer;

use Domain\Character\Data\CharacterData;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TopBanner extends Component
{
    public ?CharacterData $character;
    public bool $canLevelUp;
    public bool $canEdit;
    public ?string $characterKey;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?CharacterData $character = null,
        bool $canLevelUp = false,
        bool $canEdit = false,
        ?string $characterKey = null
    ) {
        $this->character = $character;
        $this->canLevelUp = $canLevelUp;
        $this->canEdit = $canEdit;
        $this->characterKey = $characterKey;
    }

    /**
     * Calculate tier based on character level
     */
    public function getTier(): int
    {
        $level = $this->character->level ?? 1;
        
        return match (true) {
            $level >= 8 => 4,
            $level >= 5 => 3,
            $level >= 2 => 2,
            default => 1,
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.character-viewer.top-banner');
    }
}


