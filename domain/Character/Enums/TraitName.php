<?php

declare(strict_types=1);

namespace Domain\Character\Enums;

enum TraitName: string
{
    case AGILITY = 'agility';
    case STRENGTH = 'strength';
    case FINESSE = 'finesse';
    case INSTINCT = 'instinct';
    case PRESENCE = 'presence';
    case KNOWLEDGE = 'knowledge';

    public function label(): string
    {
        return match ($this) {
            self::AGILITY => 'Agility',
            self::STRENGTH => 'Strength',
            self::FINESSE => 'Finesse',
            self::INSTINCT => 'Instinct',
            self::PRESENCE => 'Presence',
            self::KNOWLEDGE => 'Knowledge',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AGILITY => 'Your speed, reflexes, and physical coordination',
            self::STRENGTH => 'Your physical power and athletic ability',
            self::FINESSE => 'Your precision, dexterity, and fine motor control',
            self::INSTINCT => 'Your intuition, awareness, and natural reactions',
            self::PRESENCE => 'Your charisma, force of personality, and social influence',
            self::KNOWLEDGE => 'Your education, reasoning ability, and memory',
        };
    }

    /**
     * Get all trait names as an array
     */
    public static function values(): array
    {
        return array_map(fn (TraitName $trait) => $trait->value, self::cases());
    }

    /**
     * Get all traits with labels
     */
    public static function options(): array
    {
        return array_map(
            fn (TraitName $trait) => ['value' => $trait->value, 'label' => $trait->label()],
            self::cases()
        );
    }
}
