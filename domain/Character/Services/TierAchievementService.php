<?php

declare(strict_types=1);

namespace Domain\Character\Services;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Services\Traits\CalculatesTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling tier achievement logic.
 * 
 * Tier achievements occur at levels 2, 5, and 8 per DaggerHeart SRD.
 * Each tier achievement includes:
 * - New Experience at +2 modifier
 * - Proficiency increase by 1
 * - Trait mark clearing (levels 5 and 8 only)
 * 
 * This service is shared between CharacterBuilder and CharacterLevelUp to ensure
 * consistent tier achievement application.
 */
class TierAchievementService
{
    use CalculatesTier;

    /**
     * Check if a level grants tier achievements
     *
     * @param int $level The level to check
     * @return bool True if this level grants tier achievements
     */
    public function isTierAchievementLevel(int $level): bool
    {
        return in_array($level, [2, 5, 8]);
    }

    /**
     * Get tier achievements for a specific level
     *
     * Returns the automatic benefits gained at tier achievement levels (2, 5, 8).
     * All tier achievements include:
     * - New Experience at +2
     * - Proficiency increase by 1
     * - Trait mark clearing (levels 5 & 8 only)
     *
     * @param int $level The level to get achievements for (2, 5, or 8)
     * 
     * @return array{
     *     experience?: array{required: bool, description: string},
     *     proficiency?: array{increase: int, new_value: int, description: string},
     *     clear_marks?: array{tiers: array<int>, description: string}
     * } Array of tier achievements (empty if not a tier achievement level)
     */
    public function getTierAchievements(int $level): array
    {
        if (! $this->isTierAchievementLevel($level)) {
            return [];
        }

        $achievements = [
            'experience' => [
                'required' => true,
                'description' => 'Gain a new Experience at +2',
            ],
            'proficiency' => [
                'increase' => 1,
                'new_value' => $this->getProficiencyForLevel($level),
                'description' => 'Permanently increase your Proficiency by 1',
            ],
        ];

        // Clear marked traits at levels 5 and 8
        if (in_array($level, [5, 8])) {
            $achievements['clear_marks'] = [
                'description' => 'Clear any marked traits',
            ];
        }

        return $achievements;
    }

    /**
     * Apply tier achievements to a character
     *
     * @param Character $character The character to apply achievements to
     * @param int $level The level at which tier achievements are being applied (must be 2, 5, or 8)
     * @param array $experienceData The experience data array with 'name' and optional 'description'
     * @return void
     * @throws \InvalidArgumentException If experience data is invalid
     */
    public function applyTierAchievements(
        Character $character,
        int $level,
        array $experienceData
    ): void {
        // Do nothing if not a tier achievement level
        if (! $this->isTierAchievementLevel($level)) {
            Log::debug('Skipping tier achievement application - not a tier achievement level', [
                'character_id' => $character->id,
                'level' => $level,
            ]);

            return;
        }

        // Validate and sanitize experience data
        $this->validateExperienceData($experienceData);
        $sanitizedName = $this->sanitizeInput($experienceData['name'], 100);
        $sanitizedDescription = isset($experienceData['description'])
            ? $this->sanitizeInput($experienceData['description'], 500)
            : '';

        try {
            // Use database transaction for data integrity
            DB::transaction(function () use ($character, $level, $sanitizedName, $sanitizedDescription) {
                // Create tier achievement experience
                CharacterExperience::create([
                    'character_id' => $character->id,
                    'experience_name' => $sanitizedName,
                    'experience_description' => $sanitizedDescription,
                    'modifier' => 2,
                ]);

                // Update proficiency (this replaces the character's base proficiency)
                $character->update([
                    'proficiency' => $this->getProficiencyForLevel($level),
                ]);

                // Clear marked traits at levels 5 and 8
                if (in_array($level, [5, 8])) {
                    $character->traits()->update(['is_marked' => false]);
                }
            });

            Log::info('Tier achievement applied successfully', [
                'character_id' => $character->id,
                'level' => $level,
                'experience_name' => $sanitizedName,
                'new_proficiency' => $this->getProficiencyForLevel($level),
                'traits_cleared' => in_array($level, [5, 8]),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to apply tier achievement', [
                'character_id' => $character->id,
                'level' => $level,
                'experience_data' => $experienceData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException(
                "Failed to apply tier achievement for character {$character->id} at level {$level}: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get the proficiency value for a given level
     * 
     * Per DaggerHeart SRD:
     * - Level 1: Proficiency 1
     * - Levels 2-4: Proficiency 2 (tier achievement at 2)
     * - Levels 5-7: Proficiency 3 (tier achievement at 5)
     * - Levels 8-10: Proficiency 4 (tier achievement at 8)
     *
     * @param int $level The character level
     * @return int The proficiency value
     */
    public function getProficiencyForLevel(int $level): int
    {
        return match (true) {
            $level >= 8 => 4,
            $level >= 5 => 3,
            $level >= 2 => 2,
            default => 1,
        };
    }


    /**
     * Calculate the damage threshold increase for a given level
     * 
     * Per DaggerHeart SRD Step Three: "Increase all damage thresholds by 1"
     * This happens every level after level 1.
     *
     * @param int $level The character level
     * @return int The damage threshold increase (+0 at level 1, +1 per level after)
     */
    public function getDamageThresholdIncreaseForLevel(int $level): int
    {
        return $level - 1;
    }

    /**
     * Get all levels between two points that grant tier achievements
     *
     * @param int $startLevel Starting level (exclusive)
     * @param int $endLevel Ending level (inclusive)
     * @return array Array of levels that grant tier achievements
     */
    public function getTierAchievementLevelsBetween(int $startLevel, int $endLevel): array
    {
        $tierAchievementLevels = [2, 5, 8];
        $result = [];

        foreach ($tierAchievementLevels as $level) {
            if ($level > $startLevel && $level <= $endLevel) {
                $result[] = $level;
            }
        }

        return $result;
    }

    /**
     * Check if a character should have their trait marks cleared at a given level
     *
     * @param int $level The level to check
     * @return bool True if trait marks should be cleared
     */
    public function shouldClearTraitMarks(int $level): bool
    {
        return in_array($level, [5, 8]);
    }

    /**
     * Determine which tier's trait marks would be cleared at a given level
     * 
     * - Level 5 clears tier 1-2 marks
     * - Level 8 clears tier 1-3 marks
     *
     * @param int $level The level at which marks are being cleared
     * @return array Array of tiers whose marks are cleared
     */
    public function getClearedTiersForLevel(int $level): array
    {
        return match ($level) {
            5 => [1, 2],
            8 => [1, 2, 3],
            default => [],
        };
    }

    /**
     * Validate experience data structure and content
     *
     * @param array $experienceData The experience data to validate
     * @return void
     * @throws \InvalidArgumentException If validation fails
     */
    private function validateExperienceData(array $experienceData): void
    {
        // Name is required
        if (! isset($experienceData['name']) || trim($experienceData['name']) === '') {
            throw new \InvalidArgumentException('Experience name is required for tier achievements');
        }

        // Name length validation (max 100 characters)
        if (mb_strlen($experienceData['name']) > 100) {
            throw new \InvalidArgumentException('Experience name must not exceed 100 characters');
        }

        // Description length validation if provided (max 500 characters)
        if (isset($experienceData['description']) && mb_strlen($experienceData['description']) > 500) {
            throw new \InvalidArgumentException('Experience description must not exceed 500 characters');
        }
    }

    /**
     * Sanitize user input to prevent XSS and ensure data integrity
     *
     * @param string $input The input to sanitize
     * @param int $maxLength Maximum allowed length
     * @return string The sanitized input
     */
    private function sanitizeInput(string $input, int $maxLength): string
    {
        // Trim whitespace
        $sanitized = trim($input);

        // Remove HTML tags
        $sanitized = strip_tags($sanitized);

        // Encode special HTML characters
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Truncate to max length
        if (mb_strlen($sanitized) > $maxLength) {
            $sanitized = mb_substr($sanitized, 0, $maxLength);
        }

        return $sanitized;
    }
}

