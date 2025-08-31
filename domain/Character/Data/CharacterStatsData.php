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
        // Load class data from JSON file (use default if no class set)
        $class_data = $character->class ? self::getClassData($character->class) : ['startingEvasion' => 10, 'startingHitPoints' => 5];
        $equipment_data = $character->equipment()->armor()->get();

        $base_evasion = $class_data['startingEvasion'] ?? 10;
        $base_hit_points = $class_data['startingHitPoints'] ?? 5;

        // Calculate armor score from equipped armor (with base armor score of 1)
        $base_armor_score = 1; // Base armor score for all characters
        $equipment_armor_score = $equipment_data->sum(function ($armor) {
            return $armor->equipment_data['armor_score'] ?? $armor->equipment_data['baseScore'] ?? 0;
        });
        $armor_score = $base_armor_score + $equipment_armor_score;

        // Get bonuses from ancestry, subclass, and advancements
        $evasion_bonuses = $character->getTotalEvasionBonuses();
        $hit_point_bonuses = $character->getTotalHitPointBonuses();
        $stress_bonuses = $character->getTotalStressBonuses();
        $damage_threshold_bonus = $character->getAncestryDamageThresholdBonus() + $character->getSubclassDamageThresholdBonus();
        $severe_threshold_bonus = $character->getSubclassSevereThresholdBonus();

        // Calculate total bonuses
        $total_evasion_bonus = array_sum($evasion_bonuses);
        $total_hit_point_bonus = array_sum($hit_point_bonuses);
        $total_stress_bonus = array_sum($stress_bonuses);

        // Calculate thresholds based on armor, proficiency, level, and bonuses
        $proficiency_bonus = $character->getProficiencyBonus();
        $major_threshold = max(1, $armor_score + $proficiency_bonus + $character->level + $damage_threshold_bonus);
        $severe_threshold = max(1, $armor_score + $proficiency_bonus + $character->level + 5 + $damage_threshold_bonus + $severe_threshold_bonus);

        return new self(
            evasion: $base_evasion + $character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::AGILITY) + $total_evasion_bonus,
            hit_points: $base_hit_points + $total_hit_point_bonus, // Base hit points + bonuses (level not included for starting characters)
            hope: 2, // Starting hope
            stress: 6 + $total_stress_bonus, // Every PC starts with 6 stress slots + bonuses
            major_threshold: $major_threshold,
            severe_threshold: $severe_threshold,
            armor_score: $armor_score,
        );
    }

    /**
     * Get class data from JSON file
     */
    private static function getClassData(string $className): array
    {
        $path = resource_path('json/classes.json');
        if (!file_exists($path)) {
            return [];
        }
        
        $classes = json_decode(file_get_contents($path), true);
        return $classes[$className] ?? [];
    }

    public static function fromCharacterBuilder(CharacterBuilderData $builder, array $class_data, array $ancestry_data = []): self
    {
        $base_evasion = $class_data['startingEvasion'] ?? 10;
        $base_hit_points = $class_data['startingHitPoints'] ?? 5;

        // Get agility modifier from assigned traits
        $agility_modifier = $builder->assigned_traits['agility'] ?? 0;

        // Calculate armor score from selected equipment (with base armor score of 1)
        $base_armor_score = 1; // Base armor score for all characters
        $equipment_armor_score = 0;
        foreach ($builder->selected_equipment as $equipment) {
            if ($equipment['type'] === 'armor') {
                $equipment_armor_score += $equipment['data']['baseScore'] ?? 0;
            }
        }
        $armor_score = $base_armor_score + $equipment_armor_score;

        // Calculate ancestry and subclass bonuses using effects-based approach
        $ancestry_evasion_bonus = $builder->getAncestryEvasionBonus();
        $ancestry_hit_point_bonus = $builder->getAncestryHitPointBonus();
        $ancestry_stress_bonus = $builder->getAncestryStressBonus();
        $ancestry_damage_threshold_bonus = $builder->getAncestryDamageThresholdBonus();

        $subclass_evasion_bonus = $builder->getSubclassEvasionBonus();
        $subclass_hit_point_bonus = $builder->getSubclassHitPointBonus();
        $subclass_stress_bonus = $builder->getSubclassStressBonus();
        $subclass_damage_threshold_bonus = $builder->getSubclassDamageThresholdBonus();
        $subclass_severe_threshold_bonus = $builder->getSubclassSevereThresholdBonus();

        // For character builder (level 1), proficiency is 0
        $total_damage_threshold_bonus = $ancestry_damage_threshold_bonus + $subclass_damage_threshold_bonus;
        $major_threshold = max(1, $armor_score + 0 + 3 + $total_damage_threshold_bonus); // armor + proficiency(0) + base(3) + bonuses
        $severe_threshold = max(1, $armor_score + 0 + 8 + $total_damage_threshold_bonus + $subclass_severe_threshold_bonus); // armor + proficiency(0) + base(8) + bonuses

        return new self(
            evasion: $base_evasion + $agility_modifier + $ancestry_evasion_bonus + $subclass_evasion_bonus,
            hit_points: $base_hit_points + $ancestry_hit_point_bonus + $subclass_hit_point_bonus, // Base hit points + bonuses
            hope: 2,
            stress: 6 + $ancestry_stress_bonus + $subclass_stress_bonus, // Every PC starts with 6 stress slots + bonuses
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
