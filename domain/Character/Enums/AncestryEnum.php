<?php

declare(strict_types=1);

namespace Domain\Character\Enums;

enum AncestryEnum: string
{
    case CLANK = 'clank';
    case DRAKONA = 'drakona';
    case DWARF = 'dwarf';
    case ELF = 'elf';
    case FAIRY = 'fairy';
    case FUNGRIL = 'fungril';
    case GALAPA = 'galapa';
    case GIANT = 'giant';
    case GOBLIN = 'goblin';
    case HUMAN = 'human';
    case KATARI = 'katari';
    case ORC = 'orc';
    case SIMIAH = 'simiah';
    // Playtest ancestries
    case EARTHKIN = 'earthkin';

    /**
     * Get ancestries that have numerical effects (HP, Evasion, Stress, Thresholds, Experience bonus)
     */
    public function hasNumericalEffects(): bool
    {
        return match ($this) {
            self::CLANK => true,     // Experience bonus selection (+1)
            self::GALAPA => true,    // +2 damage thresholds
            self::GIANT => true,     // +1 HP
            self::HUMAN => true,     // +1 Stress
            self::SIMIAH => true,    // +1 Evasion
            self::EARTHKIN => true,  // Playtest: +1 armor, +1 thresholds
            default => false
        };
    }

    /**
     * Check if this ancestry is from playtest content
     */
    public function isPlaytest(): bool
    {
        return match ($this) {
            self::EARTHKIN => true,
            default => false
        };
    }

    /**
     * Get the playtest version for playtest ancestries
     */
    public function getPlaytestVersion(): ?string
    {
        return $this->isPlaytest() ? 'v1.3' : null;
    }
}
