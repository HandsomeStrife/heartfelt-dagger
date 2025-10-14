<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Spatie\LaravelData\Data;

class CharacterAdvancementData extends Data
{
    public function __construct(
        public int $tier,
        public int $advancement_number,
        public string $advancement_type,
        public array $advancement_data,
        public string $description,
    ) {}

    public static function traitBonus(int $tier, int $advancement_number, array $traits, int $bonus = 1): self
    {
        $trait_names = count($traits) === 1 ? $traits[0] : implode(' and ', $traits);
        $mark_text = count($traits) === 1 ? 'mark them.' : 'and mark them.';

        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'trait_bonus',
            advancement_data: [
                'traits' => $traits,
                'bonus' => $bonus,
            ],
            description: "Gain a +{$bonus} bonus to {$trait_names} {$mark_text}",
        );
    }

    public static function hitPoint(int $tier, int $advancement_number, int $bonus = 1): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'hit_point',
            advancement_data: [
                'bonus' => $bonus,
            ],
            description: 'Gain an additional Hit Point slot',
        );
    }

    public static function stress(int $tier, int $advancement_number, int $bonus = 1): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'stress',
            advancement_data: [
                'bonus' => $bonus,
            ],
            description: 'Gain an additional Stress slot',
        );
    }

    public static function experienceBonus(int $tier, int $advancement_number, int $bonus = 1): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'experience_bonus',
            advancement_data: [
                'bonus' => $bonus,
            ],
            description: 'Your experiences now provide a +3 modifier instead of +2',
        );
    }

    public static function domainCard(int $tier, int $advancement_number, int $level): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'domain_card',
            advancement_data: [
                'level' => $level,
            ],
            description: "Take a level {$level} domain card from your class domains",
        );
    }

    public static function evasion(int $tier, int $advancement_number, int $bonus = 1): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'evasion',
            advancement_data: [
                'bonus' => $bonus,
            ],
            description: "Permanently gain a +{$bonus} bonus to your Evasion",
        );
    }

    public static function subclass(int $tier, int $advancement_number, string $type): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'subclass',
            advancement_data: [
                'type' => $type,
            ],
            description: 'Take an upgraded subclass card',
        );
    }

    public static function proficiency(int $tier, int $advancement_number, int $bonus = 1): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'proficiency',
            advancement_data: [
                'bonus' => $bonus,
            ],
            description: "Increase your Proficiency by +{$bonus}",
        );
    }

    public static function multiclass(int $tier, int $advancement_number, string $class_key): self
    {
        return new self(
            tier: $tier,
            advancement_number: $advancement_number,
            advancement_type: 'multiclass',
            advancement_data: [
                'class' => $class_key,
            ],
            description: "Multiclass: Choose {$class_key} as an additional class for your character, then cross out an unused 'Take an upgraded subclass card' and the other multiclass option on this sheet.",
        );
    }

    /**
     * Create advancement data from character creation selection
     *
     * @param int $level The character level this advancement is for
     * @param int $advancement_number The advancement slot (1 or 2)
     * @param array $selection The advancement selection from CharacterBuilderData
     * @return self
     */
    public static function fromCreationSelection(int $level, int $advancement_number, array $selection): self
    {
        // Calculate tier from level
        $tier = match (true) {
            $level === 1 => 1,
            $level >= 2 && $level <= 4 => 2,
            $level >= 5 && $level <= 7 => 3,
            $level >= 8 && $level <= 10 => 4,
            default => 1,
        };

        $type = $selection['type'] ?? 'generic';
        $description = $selection['description'] ?? '';

        // Route to appropriate factory method based on type
        return match ($type) {
            'trait_bonus' => self::traitBonus(
                $tier,
                $advancement_number,
                $selection['traits'] ?? []
            ),
            'hit_point' => self::hitPoint($tier, $advancement_number),
            'stress_slot', 'stress' => self::stress($tier, $advancement_number), // Accept both variants
            'experience_bonus' => self::experienceBonus($tier, $advancement_number),
            'domain_card' => self::domainCard($tier, $advancement_number, $level),
            'evasion' => self::evasion($tier, $advancement_number),
            'subclass_upgrade' => self::subclass($tier, $advancement_number, $selection['type'] ?? 'upgrade'),
            'proficiency' => self::proficiency($tier, $advancement_number),
            'multiclass' => self::multiclass($tier, $advancement_number, $selection['class'] ?? ''),
            default => new self(
                tier: $tier,
                advancement_number: $advancement_number,
                advancement_type: $type,
                advancement_data: $selection['data'] ?? [],
                description: $description,
            ),
        };
    }
}
