<?php

declare(strict_types=1);

namespace Domain\Character\Services;

/**
 * Service responsible for calculating subclass bonuses for characters
 */
class SubclassBonusService
{
    /**
     * Get all applied subclass bonuses
     *
     * @param string|null $subclassKey The subclass key
     * @return array Array of bonuses keyed by stat type
     */
    public function getSubclassBonuses(?string $subclassKey): array
    {
        if (! $subclassKey) {
            return [];
        }

        $bonuses = [];

        $evasion_bonus = $this->getEvasionBonus($subclassKey);
        $hit_point_bonus = $this->getHitPointBonus($subclassKey);
        $stress_bonus = $this->getStressBonus($subclassKey);
        $damage_threshold_bonus = $this->getDamageThresholdBonus($subclassKey);
        $severe_threshold_bonus = $this->getSevereThresholdBonus($subclassKey);
        $domain_card_bonus = $this->getDomainCardBonus($subclassKey);

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

        if ($severe_threshold_bonus > 0) {
            $bonuses['severe_threshold'] = $severe_threshold_bonus;
        }

        if ($domain_card_bonus > 0) {
            $bonuses['domain_cards'] = $domain_card_bonus;
        }

        return $bonuses;
    }

    /**
     * Get subclass bonus for evasion
     *
     * @param string $subclassKey The subclass key
     * @return int The evasion bonus
     */
    public function getEvasionBonus(string $subclassKey): int
    {
        $effects = $this->getSubclassEffects($subclassKey, 'evasion_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for hit points
     *
     * @param string $subclassKey The subclass key
     * @return int The hit point bonus
     */
    public function getHitPointBonus(string $subclassKey): int
    {
        $effects = $this->getSubclassEffects($subclassKey, 'hit_point_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for stress
     *
     * @param string $subclassKey The subclass key
     * @return int The stress bonus
     */
    public function getStressBonus(string $subclassKey): int
    {
        $effects = $this->getSubclassEffects($subclassKey, 'stress_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for damage thresholds
     *
     * @param string $subclassKey The subclass key
     * @return int The damage threshold bonus
     */
    public function getDamageThresholdBonus(string $subclassKey): int
    {
        $effects = $this->getSubclassEffects($subclassKey, 'damage_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            $bonus += (int) $value;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for severe damage threshold
     *
     * @param string $subclassKey The subclass key
     * @return int The severe threshold bonus
     */
    public function getSevereThresholdBonus(string $subclassKey): int
    {
        $effects = $this->getSubclassEffects($subclassKey, 'severe_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            $bonus += (int) $value;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for domain cards
     *
     * @param string $subclassKey The subclass key
     * @return int The domain card bonus
     */
    public function getDomainCardBonus(string $subclassKey): int
    {
        $effects = $this->getSubclassEffects($subclassKey, 'domain_card_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get the maximum number of domain cards for a character with this subclass
     *
     * @param string|null $subclassKey The subclass key
     * @param int $baseCards Base starting domain cards (typically 2)
     * @return int The maximum domain cards
     */
    public function getMaxDomainCards(?string $subclassKey, int $baseCards = 2): int
    {
        if (! $subclassKey) {
            return $baseCards;
        }

        $subclass_bonus = $this->getDomainCardBonus($subclassKey);

        return $baseCards + $subclass_bonus;
    }

    /**
     * Get subclass effects by type
     *
     * @param string $subclassKey The subclass key
     * @param string $effectType The effect type to filter by
     * @return array Array of effects matching the type
     */
    public function getSubclassEffects(string $subclassKey, string $effectType): array
    {
        $subclassData = $this->loadSubclassData($subclassKey);
        if (! $subclassData) {
            return [];
        }

        $effects = [];
        $allFeatures = array_merge(
            $subclassData['foundationFeatures'] ?? [],
            $subclassData['specializationFeatures'] ?? [],
            $subclassData['masteryFeatures'] ?? []
        );

        foreach ($allFeatures as $feature) {
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
     * Load subclass data from JSON file
     *
     * @param string $subclassKey The subclass key
     * @return array|null The subclass data or null if not found
     */
    private function loadSubclassData(string $subclassKey): ?array
    {
        $path = resource_path('json/subclasses.json');
        if (! file_exists($path)) {
            return null;
        }

        $subclasses = json_decode(file_get_contents($path), true);

        return $subclasses[$subclassKey] ?? null;
    }
}

