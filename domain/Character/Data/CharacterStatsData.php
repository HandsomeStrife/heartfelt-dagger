<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Models\Character;
use Spatie\LaravelData\Data;

class CharacterStatsData extends Data
{
    public function __construct(
        public int $evasion,
        public int $hit_points,
        public int $hope,
        public int $stress,
        public int $major_threshold,
        public int $severe_threshold,
        public int $armor_score,
    ) {}

    public static function fromModel(Character $character): self
    {
        // Calculate stats based on class, level, and equipment
        $class_data = $character->character_data['class_data'] ?? [];
        $equipment_data = $character->equipment()->armor()->get();

        $base_evasion = $class_data['starting_evasion'] ?? 10;
        $base_hit_points = $class_data['starting_hit_points'] ?? 5;

        // Calculate armor score from equipped armor
        $armor_score = $equipment_data->sum(function ($armor) {
            return $armor->equipment_data['baseScore'] ?? 0;
        });

        // Calculate thresholds based on level and armor
        $major_threshold = max(1, $armor_score + $character->level + 3);
        $severe_threshold = max(1, $armor_score + $character->level + 8);

        return new self(
            evasion: $base_evasion + $character->getTraitValue(\Domain\Character\Enums\TraitName::AGILITY),
            hit_points: $base_hit_points + $character->level,
            hope: 2, // Starting hope
            stress: 0, // Starting stress
            major_threshold: $major_threshold,
            severe_threshold: $severe_threshold,
            armor_score: $armor_score,
        );
    }

    public static function fromCharacterBuilder(CharacterBuilderData $builder, array $class_data): self
    {
        $base_evasion = $class_data['startingEvasion'] ?? 10;
        $base_hit_points = $class_data['startingHitPoints'] ?? 5;

        // Get agility modifier from assigned traits
        $agility_modifier = $builder->assigned_traits['agility'] ?? 0;

        // Calculate armor score from selected equipment
        $armor_score = 0;
        foreach ($builder->selected_equipment as $equipment) {
            if ($equipment['type'] === 'armor') {
                $armor_score += $equipment['data']['baseScore'] ?? 0;
            }
        }

        $major_threshold = max(1, $armor_score + 4); // Level 1 + 3
        $severe_threshold = max(1, $armor_score + 9); // Level 1 + 8

        return new self(
            evasion: $base_evasion + $agility_modifier,
            hit_points: $base_hit_points + 1, // Level 1
            hope: 2,
            stress: 0,
            major_threshold: $major_threshold,
            severe_threshold: $severe_threshold,
            armor_score: $armor_score,
        );
    }

    public function getHealthStatus(): string
    {
        if ($this->stress >= 4) {
            return 'critical';
        }
        if ($this->stress >= 2) {
            return 'wounded';
        }

        return 'healthy';
    }

    public function canTakeStress(): bool
    {
        return $this->stress < 4;
    }

    public function canTakeHitPoints(): bool
    {
        return $this->hit_points > 0;
    }
}
