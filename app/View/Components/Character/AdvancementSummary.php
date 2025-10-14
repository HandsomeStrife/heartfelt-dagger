<?php

declare(strict_types=1);

namespace App\View\Components\Character;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdvancementSummary extends Component
{
    public array $advancements;
    public array $tierExperiences;
    public array $domainCards;
    public int $startingLevel;
    public string $variant;

    /**
     * Create a new component instance.
     */
    public function __construct(
        array $advancements = [],
        array $tierExperiences = [],
        array $domainCards = [],
        int $startingLevel = 1,
        string $variant = 'default'
    ) {
        $this->advancements = $advancements;
        $this->tierExperiences = $tierExperiences;
        $this->domainCards = $domainCards;
        $this->startingLevel = $startingLevel;
        $this->variant = $variant;
    }

    /**
     * Calculate tier based on level
     */
    public function getTier(int $level): int
    {
        return match (true) {
            $level >= 8 => 4,
            $level >= 5 => 3,
            $level >= 2 => 2,
            default => 1,
        };
    }

    /**
     * Get advancement type icon
     */
    public function getTypeIcon(string $type): string
    {
        return match ($type) {
            'trait_bonus' => '💪',
            'hit_point' => '❤️',
            'stress', 'stress_slot' => '🛡️',
            'evasion' => '🎯',
            'experience_bonus' => '⭐',
            'domain_card' => '🃏',
            'proficiency' => '📚',
            'subclass_upgrade' => '🔰',
            'multiclass' => '🔀',
            default => '•',
        };
    }

    /**
     * Get advancement type label
     */
    public function getTypeLabel(string $type): string
    {
        return match ($type) {
            'trait_bonus' => 'Trait Bonus',
            'hit_point' => 'Hit Point',
            'stress', 'stress_slot' => 'Stress Slot',
            'evasion' => 'Evasion',
            'experience_bonus' => 'Experience Bonus',
            'domain_card' => 'Domain Card',
            'proficiency' => 'Proficiency',
            'subclass_upgrade' => 'Subclass Upgrade',
            'multiclass' => 'Multiclass',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.character.advancement-summary');
    }
}


