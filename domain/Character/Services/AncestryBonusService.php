<?php

declare(strict_types=1);

namespace Domain\Character\Services;

use Domain\Character\Models\Character;

/**
 * Service responsible for calculating ancestry bonuses for characters
 */
class AncestryBonusService
{
    /**
     * Get all applied ancestry bonuses for a character
     *
     * @param string|null $ancestryKey The ancestry key
     * @return array Array of bonuses keyed by stat type
     */
    public function getAncestryBonuses(?string $ancestryKey): array
    {
        if (! $ancestryKey) {
            return [];
        }

        $bonuses = [];

        $evasion_bonus = $this->getEvasionBonus($ancestryKey);
        $hit_point_bonus = $this->getHitPointBonus($ancestryKey);
        $stress_bonus = $this->getStressBonus($ancestryKey);
        $damage_threshold_bonus = $this->getDamageThresholdBonus($ancestryKey);

        if ($evasion_bonus > 0) {
            $bonuses['evasion'] = $evasion_bonus;
        }

        if ($hit_point_bonus > 0) {
            $bonuses['hit_points'] = $hit_point_bonus;
        }

        if ($stress_bonus > 0) {
            $bonuses['stress'] = $stress_bonus;
        }

        if ($damage_threshold_bonus > 0) {
            $bonuses['damage_thresholds'] = $damage_threshold_bonus;
        }

        return $bonuses;
    }

    /**
     * Get ancestry bonus for evasion
     *
     * @param string $ancestryKey The ancestry key
     * @return int The evasion bonus
     */
    public function getEvasionBonus(string $ancestryKey): int
    {
        $effects = $this->getAncestryEffects($ancestryKey, 'evasion_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get ancestry bonus for hit points
     *
     * @param string $ancestryKey The ancestry key
     * @return int The hit point bonus
     */
    public function getHitPointBonus(string $ancestryKey): int
    {
        $effects = $this->getAncestryEffects($ancestryKey, 'hit_point_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get ancestry bonus for stress
     *
     * @param string $ancestryKey The ancestry key
     * @return int The stress bonus
     */
    public function getStressBonus(string $ancestryKey): int
    {
        $effects = $this->getAncestryEffects($ancestryKey, 'stress_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get ancestry bonus for damage thresholds
     *
     * @param string $ancestryKey The ancestry key
     * @param int $proficiencyBonus Current proficiency bonus (for proficiency-based effects)
     * @return int The damage threshold bonus
     */
    public function getDamageThresholdBonus(string $ancestryKey, int $proficiencyBonus = 2): int
    {
        $effects = $this->getAncestryEffects($ancestryKey, 'damage_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            if ($value === 'proficiency') {
                $bonus += $proficiencyBonus;
            } else {
                $bonus += (int) $value;
            }
        }

        return $bonus;
    }

    /**
     * Check if ancestry has experience bonus selection effect
     *
     * @param string $ancestryKey The ancestry key
     * @return bool True if ancestry has experience bonus selection
     */
    public function hasExperienceBonusSelection(string $ancestryKey): bool
    {
        return ! empty($this->getAncestryEffects($ancestryKey, 'experience_bonus_selection'));
    }

    /**
     * Get experience modifier including ancestry bonuses
     *
     * @param string $experienceName The experience name
     * @param string|null $ancestryKey The ancestry key
     * @param string|null $selectedBonusExperience The experience selected for bonus (Clank)
     * @return int The total modifier
     */
    public function getExperienceModifier(
        string $experienceName,
        ?string $ancestryKey,
        ?string $selectedBonusExperience
    ): int {
        $baseModifier = 2; // All experiences start with +2

        if (! $ancestryKey) {
            return $baseModifier;
        }

        // Check if this experience gets experience bonus selection effect
        if ($this->hasExperienceBonusSelection($ancestryKey) &&
            $selectedBonusExperience === $experienceName) {

            $effects = $this->getAncestryEffects($ancestryKey, 'experience_bonus_selection');
            $bonus = 0;
            foreach ($effects as $effect) {
                $bonus += $effect['value'] ?? 0;
            }

            return $baseModifier + $bonus;
        }

        return $baseModifier;
    }

    /**
     * Get ancestry effects by type
     *
     * @param string $ancestryKey The ancestry key
     * @param string $effectType The effect type to filter by
     * @return array Array of effects matching the type
     */
    public function getAncestryEffects(string $ancestryKey, string $effectType): array
    {
        $ancestryData = $this->loadAncestryData($ancestryKey);
        if (! $ancestryData) {
            return [];
        }

        $effects = [];
        $features = $ancestryData['features'] ?? [];

        foreach ($features as $feature) {
            $featureEffects = $feature['effects'] ?? [];
            foreach ($featureEffects as $effect) {
                if (($effect['type'] ?? '') === $effectType) {
                    $effects[] = $effect;
                }
            }
        }

        return $effects;
    }

    /**
     * Load ancestry data from JSON file
     *
     * @param string $ancestryKey The ancestry key
     * @return array|null The ancestry data or null if not found
     */
    private function loadAncestryData(string $ancestryKey): ?array
    {
        $path = resource_path('json/ancestries.json');
        if (! file_exists($path)) {
            return null;
        }

        $ancestries = json_decode(file_get_contents($path), true);

        return $ancestries[$ancestryKey] ?? null;
    }
}

