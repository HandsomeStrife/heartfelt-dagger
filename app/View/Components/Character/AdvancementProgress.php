<?php

declare(strict_types=1);

namespace App\View\Components\Character;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdvancementProgress extends Component
{
    public int $currentLevel;
    public int $startingLevel;
    public string $variant;

    public array $levelsToComplete;
    public int $totalLevels;
    public int $completedLevels;
    public float $progressPercentage;

    /**
     * Create a new component instance.
     */
    public function __construct(
        int $currentLevel = 1,
        int $startingLevel = 1,
        string $variant = 'default'
    ) {
        $this->currentLevel = $currentLevel;
        $this->startingLevel = $startingLevel;
        $this->variant = $variant;

        // Calculate progress data
        $this->levelsToComplete = range(2, $startingLevel);
        $this->totalLevels = count($this->levelsToComplete);
        $this->completedLevels = max(0, $currentLevel - 2);
        $this->progressPercentage = $this->totalLevels > 0 
            ? (($this->completedLevels / $this->totalLevels) * 100) 
            : 0;
    }

    /**
     * Check if a level is completed
     */
    public function isLevelCompleted(int $level): bool
    {
        return $level < $this->currentLevel;
    }

    /**
     * Check if a level is the current level
     */
    public function isLevelCurrent(int $level): bool
    {
        return $level === $this->currentLevel;
    }

    /**
     * Check if a level is pending
     */
    public function isLevelPending(int $level): bool
    {
        return $level > $this->currentLevel;
    }

    /**
     * Check if a level is a tier level (2, 5, 8)
     */
    public function isTierLevel(int $level): bool
    {
        return in_array($level, [2, 5, 8]);
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
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.character.advancement-progress');
    }
}


