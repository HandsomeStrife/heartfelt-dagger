<?php

declare(strict_types=1);

namespace Domain\Character\Services;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Services\Traits\CalculatesTier;

/**
 * Service for validating advancement selections according to DaggerHeart SRD rules.
 * 
 * Handles validation for:
 * - Complete level selections (tier achievements, advancements, domain cards)
 * - Trait marking rules (can't select marked traits in same tier)
 * - Advancement requirements (trait count, experience count, etc.)
 * - SRD compliance checks
 * 
 * This service is shared between CharacterBuilder and CharacterLevelUp to ensure
 * consistent validation rules.
 */
class AdvancementValidationService
{
    use CalculatesTier;

    public function __construct(
        private TierAchievementService $tierAchievementService,
        private DomainCardService $domainCardService,
    ) {}

    /**
     * Validate all selections for a specific level
     *
     * Checks that all required selections are present and valid:
     * - Tier achievement experience (if levels 2, 5, or 8)
     * - Domain card selection (required for ALL levels)
     * - Advancement selections (exactly 2, from available options)
     *
     * @param Character $character The character being validated (for checking marked traits, accessible domains)
     * @param int $level The level to validate selections for (determines tier and required selections)
     * @param array{
     *     tier_experience?: array{name: string, description: string},
     *     domain_card?: string,
     *     advancements?: array<int, array{type: string, traits?: array<string>, ...}>
     * } $selections The selections made for this level
     * 
     * @return array<int, string> Array of validation error messages (empty array if all valid)
     */
    public function validateLevelSelections(
        Character $character,
        int $level,
        array $selections
    ): array {
        $errors = [];

        // Validate tier achievement (if applicable)
        if ($this->tierAchievementService->isTierAchievementLevel($level)) {
            if (empty($selections['tier_experience'])) {
                $errors[] = "Tier achievement experience required for level {$level}";
            } else {
                // Validate experience data structure
                if (empty($selections['tier_experience']['name'])) {
                    $errors[] = "Tier achievement experience must have a name";
                }
            }
        }

        // Validate domain card (REQUIRED for ALL levels per SRD Step Four)
        if (empty($selections['domain_card'])) {
            $errors[] = "Domain card selection required for level {$level}";
        } else {
            if (! $this->domainCardService->validateCardSelection(
                $character,
                $selections['domain_card'],
                $level
            )) {
                $errors[] = "Invalid domain card selection for level {$level}";
            }
        }

        // Validate advancement selections (must have exactly 2)
        $advancements = $selections['advancements'] ?? [];
        if (count($advancements) !== 2) {
            $errors[] = "Exactly 2 advancements required for level {$level}";
        }

        // Validate specific advancement requirements
        foreach ($advancements as $advancement) {
            $advancementErrors = $this->validateAdvancementOption(
                $character,
                $level,
                $advancement
            );
            $errors = array_merge($errors, $advancementErrors);
        }

        return $errors;
    }

    /**
     * Validate a specific advancement option
     *
     * @param Character $character The character being validated
     * @param int $level The level at which advancement is being taken
     * @param array $advancement The advancement data
     * @return array Array of error messages
     */
    private function validateAdvancementOption(
        Character $character,
        int $level,
        array $advancement
    ): array {
        $errors = [];
        $type = $advancement['type'] ?? '';

        switch ($type) {
            case 'trait_bonus':
                $errors = array_merge(
                    $errors,
                    $this->validateTraitSelection($character, $level, $advancement)
                );
                break;

            case 'domain_card':
                if (empty($advancement['card_key'])) {
                    $errors[] = 'Domain card key required for domain card advancement';
                }
                break;

            case 'experience_bonus':
                if (count($advancement['experience_names'] ?? []) !== 2) {
                    $errors[] = 'Experience bonus must select exactly 2 experiences';
                }
                break;

            case 'multiclass':
                if ($level < 5) {
                    $errors[] = 'Multiclass only available at tier 3 (level 5+)';
                }
                if (empty($advancement['class_key'])) {
                    $errors[] = 'Multiclass requires class selection';
                }
                break;

            case 'subclass_upgrade':
                if (empty($advancement['subclass'])) {
                    $errors[] = 'Subclass upgrade requires subclass selection';
                }
                break;
        }

        return $errors;
    }

    /**
     * Validate trait selection (marked trait rules)
     *
     * @param Character $character The character being validated
     * @param int $level The level at which traits are being selected
     * @param array $advancement The trait advancement data
     * @return array Array of error messages
     */
    private function validateTraitSelection(
        Character $character,
        int $level,
        array $advancement
    ): array {
        $errors = [];
        $traits = $advancement['traits'] ?? [];

        if (count($traits) !== 2) {
            $errors[] = 'Trait advancement must select exactly 2 traits';

            return $errors;
        }

        $tier = $this->getTierForLevel($level);
        $markedTraits = $this->getMarkedTraits($character, $tier);

        foreach ($traits as $trait) {
            if (in_array($trait, $markedTraits)) {
                $errors[] = "Trait '{$trait}' is already marked in this tier and cannot be selected again";
            }
        }

        return $errors;
    }

    /**
     * Get traits that are marked in the current tier
     * 
     * Traits are marked when they receive a +1 bonus from an advancement.
     * They cannot be advanced again in the same tier.
     * Tier achievements at levels 5 and 8 clear marked traits.
     *
     * @param Character $character The character to check
     * @param int $tier The tier to check marked traits for
     * @return array Array of trait names that are currently marked
     */
    public function getMarkedTraits(Character $character, int $tier): array
    {
        $markedTraits = [];

        // Get all trait advancements
        $traitAdvancements = $character->advancements()
            ->where('advancement_type', 'trait_bonus')
            ->get();

        foreach ($traitAdvancements as $advancement) {
            $advancementTier = $advancement->tier;
            $traits = $advancement->advancement_data['traits'] ?? [];

            // Check if marks have been cleared by tier achievements
            $cleared = $this->areMarksCleared($character->level, $advancementTier);

            if (! $cleared && $advancementTier <= $tier) {
                $markedTraits = array_merge($markedTraits, $traits);
            }
        }

        return array_unique($markedTraits);
    }

    /**
     * Check if trait marks from a given tier have been cleared
     * 
     * Per DaggerHeart SRD:
     * - Level 5 tier achievement clears tier 1-2 marks
     * - Level 8 tier achievement clears tier 1-3 marks
     *
     * @param int $currentLevel The character's current level
     * @param int $markTier The tier in which the trait was marked
     * @return bool True if the marks have been cleared
     */
    private function areMarksCleared(int $currentLevel, int $markTier): bool
    {
        // Level 8 clears tier 1-3 marks
        if ($currentLevel >= 8 && $markTier <= 3) {
            return true;
        }

        // Level 5 clears tier 1-2 marks
        if ($currentLevel >= 5 && $markTier <= 2) {
            return true;
        }

        return false;
    }

    /**
     * Check if a specific trait can be marked at a given tier
     *
     * @param Character $character The character to check
     * @param string $trait The trait name to check
     * @param int $tier The tier at which marking would occur
     * @return bool True if the trait can be marked
     */
    public function canMarkTrait(Character $character, string $trait, int $tier): bool
    {
        $markedTraits = $this->getMarkedTraits($character, $tier);

        return ! in_array($trait, $markedTraits);
    }

    /**
     * Get available (unmarked) traits for a character at a tier
     *
     * @param Character $character The character to check
     * @param int $tier The tier to check for
     * @return array Array of trait names that can be marked
     */
    public function getAvailableTraits(Character $character, int $tier): array
    {
        $allTraits = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];
        $markedTraits = $this->getMarkedTraits($character, $tier);

        return array_diff($allTraits, $markedTraits);
    }

    /**
     * Validate complete character advancement selections for multiple levels
     * 
     * Used during character creation to validate all advancement selections at once.
     *
     * @param Character $character The character being validated
     * @param int $startLevel Starting level (exclusive)
     * @param int $endLevel Ending level (inclusive)
     * @param array $allSelections Selections keyed by level
     * @return array Array of errors keyed by level
     */
    public function validateMultipleLevels(
        Character $character,
        int $startLevel,
        int $endLevel,
        array $allSelections
    ): array {
        $errorsByLevel = [];

        for ($level = $startLevel + 1; $level <= $endLevel; $level++) {
            $selections = $allSelections[$level] ?? [];
            $errors = $this->validateLevelSelections($character, $level, $selections);

            if (! empty($errors)) {
                $errorsByLevel[$level] = $errors;
            }
        }

        return $errorsByLevel;
    }


    /**
     * Validate that advancement selections respect max selection limits
     *
     * @param Character $character The character to validate for
     * @param array $option The advancement option with maxSelections
     * @param int $timesSelected How many times this has been selected across all tiers
     * @return bool True if selection count is valid
     */
    public function validateMaxSelections(Character $character, array $option, int $timesSelected): bool
    {
        $maxSelections = $option['maxSelections'] ?? 1;

        return $timesSelected < $maxSelections;
    }

    /**
     * Check if two advancement options are mutually exclusive in the same tier
     *
     * @param string $type1 First advancement type
     * @param string $type2 Second advancement type
     * @param int $tier The tier being checked
     * @return bool True if they are mutually exclusive
     */
    public function areMutuallyExclusive(string $type1, string $type2, int $tier): bool
    {
        // Multiclass and subclass upgrade are mutually exclusive
        if (($type1 === 'multiclass' && $type2 === 'subclass_upgrade') ||
            ($type1 === 'subclass_upgrade' && $type2 === 'multiclass')) {
            return true;
        }

        return false;
    }
}

