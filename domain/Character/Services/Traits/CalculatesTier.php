<?php

declare(strict_types=1);

namespace Domain\Character\Services\Traits;

/**
 * Shared trait for calculating character tier from level.
 *
 * Per DaggerHeart SRD, characters progress through 4 tiers based on level:
 * - Tier 1: Level 1
 * - Tier 2: Levels 2-4
 * - Tier 3: Levels 5-7
 * - Tier 4: Levels 8-10
 *
 * This trait provides a single source of truth for tier calculations
 * to prevent duplication across services and ensure consistency.
 *
 * @see docs-dev/contents/Leveling Up.md
 */
trait CalculatesTier
{
    /**
     * Calculate the tier for a given character level.
     *
     * @param int $level The character level (1-10)
     * @return int The tier number (1-4)
     *
     * @throws \InvalidArgumentException If level is outside valid range
     */
    protected function getTierForLevel(int $level): int
    {
        if ($level < 1 || $level > 10) {
            throw new \InvalidArgumentException("Level must be between 1 and 10, got {$level}");
        }

        return match (true) {
            $level >= 8 => 4,
            $level >= 5 => 3,
            $level >= 2 => 2,
            default => 1,
        };
    }

    /**
     * Get the minimum level for a given tier.
     *
     * Useful for validation and UI display.
     *
     * @param int $tier The tier number (1-4)
     * @return int The minimum level for that tier
     *
     * @throws \InvalidArgumentException If tier is outside valid range
     */
    protected function getMinLevelForTier(int $tier): int
    {
        if ($tier < 1 || $tier > 4) {
            throw new \InvalidArgumentException("Tier must be between 1 and 4, got {$tier}");
        }

        return match ($tier) {
            4 => 8,
            3 => 5,
            2 => 2,
            1 => 1,
        };
    }

    /**
     * Get the maximum level for a given tier.
     *
     * @param int $tier The tier number (1-4)
     * @return int The maximum level for that tier
     *
     * @throws \InvalidArgumentException If tier is outside valid range
     */
    protected function getMaxLevelForTier(int $tier): int
    {
        if ($tier < 1 || $tier > 4) {
            throw new \InvalidArgumentException("Tier must be between 1 and 4, got {$tier}");
        }

        return match ($tier) {
            4 => 10,
            3 => 7,
            2 => 4,
            1 => 1,
        };
    }

    /**
     * Get all levels within a specific tier.
     *
     * @param int $tier The tier number (1-4)
     * @return array Array of levels within that tier
     */
    protected function getLevelsInTier(int $tier): array
    {
        $min = $this->getMinLevelForTier($tier);
        $max = $this->getMaxLevelForTier($tier);

        return range($min, $max);
    }

    /**
     * Check if two levels are in the same tier.
     *
     * Useful for marked trait validation (traits marked in one tier
     * cannot be re-marked in the same tier).
     *
     * @param int $level1 First level to compare
     * @param int $level2 Second level to compare
     * @return bool True if both levels are in the same tier
     */
    protected function areInSameTier(int $level1, int $level2): bool
    {
        return $this->getTierForLevel($level1) === $this->getTierForLevel($level2);
    }

    /**
     * Get a display name for a tier.
     *
     * @param int $tier The tier number (1-4)
     * @return string Display name (e.g., "Tier 1", "Tier 2")
     */
    protected function getTierDisplayName(int $tier): string
    {
        if ($tier < 1 || $tier > 4) {
            throw new \InvalidArgumentException("Tier must be between 1 and 4, got {$tier}");
        }

        return "Tier {$tier}";
    }

    /**
     * Get a description of the level range for a tier.
     *
     * @param int $tier The tier number (1-4)
     * @return string Description (e.g., "Levels 2-4", "Level 1")
     */
    protected function getTierLevelRange(int $tier): string
    {
        $min = $this->getMinLevelForTier($tier);
        $max = $this->getMaxLevelForTier($tier);

        if ($min === $max) {
            return "Level {$min}";
        }

        return "Levels {$min}-{$max}";
    }
}

