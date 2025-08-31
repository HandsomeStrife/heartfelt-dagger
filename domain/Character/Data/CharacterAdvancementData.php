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
            description: "Gain an additional Hit Point slot",
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
            description: "Gain an additional Stress slot",
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
            description: "Your experiences now provide a +3 modifier instead of +2",
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
            description: "Take an upgraded subclass card",
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
}
