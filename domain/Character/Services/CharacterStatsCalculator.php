<?php

declare(strict_types=1);

namespace Domain\Character\Services;

/**
 * Service responsible for calculating computed character stats
 */
class CharacterStatsCalculator
{
    public function __construct(
        private readonly AncestryBonusService $ancestry_bonus_service,
        private readonly SubclassBonusService $subclass_bonus_service
    ) {}

    /**
     * Calculate all computed stats for a character
     *
     * @param array $classData Class data containing base stats
     * @param array $assignedTraits Character trait assignments
     * @param array $selectedEquipment Selected equipment items
     * @param string|null $ancestryKey Selected ancestry
     * @param string|null $subclassKey Selected subclass
     * @param int $startingLevel Character's starting/current level
     * @param array $advancementBonuses Bonuses from advancements
     * @return array Computed stats with detailed breakdown
     */
    public function calculateStats(
        array $classData,
        array $assignedTraits,
        array $selectedEquipment,
        ?string $ancestryKey,
        ?string $subclassKey,
        int $startingLevel = 1,
        array $advancementBonuses = []
    ): array {
        if (empty($classData)) {
            return [];
        }

        // Base values from class
        $base_evasion = $classData['startingEvasion'] ?? 10;
        $base_hit_points = $classData['startingHitPoints'] ?? 5;
        $agility_modifier = $assignedTraits['agility'] ?? 0;

        // Calculate armor score
        $armor_score = $this->calculateArmorScore($selectedEquipment);

        // Get proficiency
        $proficiency = $this->calculateProficiency($startingLevel, $advancementBonuses);

        // Get ancestry bonuses
        $ancestry_evasion = $this->ancestry_bonus_service->getEvasionBonus($ancestryKey ?? '');
        $ancestry_hp = $this->ancestry_bonus_service->getHitPointBonus($ancestryKey ?? '');
        $ancestry_stress = $this->ancestry_bonus_service->getStressBonus($ancestryKey ?? '');
        $ancestry_threshold = $this->ancestry_bonus_service->getDamageThresholdBonus(
            $ancestryKey ?? '',
            $proficiency['level_proficiency']
        );

        // Get subclass bonuses
        $subclass_evasion = $this->subclass_bonus_service->getEvasionBonus($subclassKey ?? '');
        $subclass_hp = $this->subclass_bonus_service->getHitPointBonus($subclassKey ?? '');
        $subclass_stress = $this->subclass_bonus_service->getStressBonus($subclassKey ?? '');
        $subclass_threshold = $this->subclass_bonus_service->getDamageThresholdBonus($subclassKey ?? '');
        $subclass_severe = $this->subclass_bonus_service->getSevereThresholdBonus($subclassKey ?? '');

        // Get advancement bonuses
        $advancement_evasion = $advancementBonuses['evasion'] ?? 0;
        $advancement_hp = $advancementBonuses['hit_points'] ?? 0;
        $advancement_stress = $advancementBonuses['stress'] ?? 0;

        // Calculate damage threshold bonus from leveling (+1 per level above 1)
        $level_threshold_bonus = $startingLevel - 1;

        // Calculate final stats
        $final_evasion = $base_evasion + $agility_modifier + $ancestry_evasion +
                        $subclass_evasion + $advancement_evasion;
        $final_hp = $base_hit_points + $ancestry_hp + $subclass_hp + $advancement_hp;
        $final_stress = 6 + $ancestry_stress + $subclass_stress + $advancement_stress;
        $major_threshold = max(1, $armor_score + 4 + $ancestry_threshold +
                              $subclass_threshold + $level_threshold_bonus);
        $severe_threshold = max(1, $armor_score + 9 + $ancestry_threshold +
                               $subclass_threshold + $subclass_severe + $level_threshold_bonus);

        return [
            // Simple values for tests and general use
            'evasion' => $final_evasion,
            'hit_points' => $final_hp,
            'final_hit_points' => $final_hp, // Alias for viewer compatibility
            'stress' => $final_stress,
            'hope' => 2,
            'major_threshold' => $major_threshold,
            'severe_threshold' => $severe_threshold,
            'armor_score' => $armor_score,
            'proficiency' => $proficiency['total'],
            'level' => $startingLevel,

            // Detailed breakdown for UI
            'detailed' => [
                'evasion' => [
                    'base' => $base_evasion,
                    'agility_modifier' => $agility_modifier,
                    'ancestry_bonus' => $ancestry_evasion,
                    'subclass_bonus' => $subclass_evasion,
                    'advancement_bonus' => $advancement_evasion,
                    'total' => $final_evasion,
                ],
                'hit_points' => [
                    'base' => $base_hit_points,
                    'ancestry_bonus' => $ancestry_hp,
                    'subclass_bonus' => $subclass_hp,
                    'advancement_bonus' => $advancement_hp,
                    'total' => $final_hp,
                ],
                'stress' => [
                    'base' => 6,
                    'ancestry_bonus' => $ancestry_stress,
                    'subclass_bonus' => $subclass_stress,
                    'advancement_bonus' => $advancement_stress,
                    'total' => $final_stress,
                ],
                'damage_thresholds' => [
                    'major' => $major_threshold,
                    'severe' => $severe_threshold,
                    'ancestry_bonus' => $ancestry_threshold,
                    'level_bonus' => $level_threshold_bonus,
                ],
                'proficiency' => $proficiency,
            ],
        ];
    }

    /**
     * Calculate armor score from selected equipment
     *
     * @param array $selectedEquipment Selected equipment items
     * @return int Total armor score
     */
    private function calculateArmorScore(array $selectedEquipment): int
    {
        $armor_score = 0;

        foreach ($selectedEquipment as $equipment) {
            if (($equipment['type'] ?? null) === 'armor') {
                $data = $equipment['data'] ?? [];
                $armor_score += $data['baseScore']
                    ?? $data['armor_score']
                    ?? $data['score']
                    ?? 0;
            }
        }

        return $armor_score;
    }

    /**
     * Calculate proficiency based on level and advancements
     *
     * @param int $startingLevel Character's starting/current level
     * @param array $advancementBonuses Bonuses from advancements
     * @return array Proficiency breakdown
     */
    private function calculateProficiency(int $startingLevel, array $advancementBonuses): array
    {
        // Base proficiency from level (per SRD: +1 at level 1, +2 at 2, +3 at 5, +4 at 8)
        $level_proficiency = match (true) {
            $startingLevel >= 8 => 4,
            $startingLevel >= 5 => 3,
            $startingLevel >= 2 => 2,
            default => 1,
        };

        $advancement_bonus = $advancementBonuses['proficiency'] ?? 0;
        $total = $level_proficiency + $advancement_bonus;

        return [
            'level_proficiency' => $level_proficiency,
            'advancement_bonus' => $advancement_bonus,
            'total' => $total,
        ];
    }
}

