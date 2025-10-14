<?php

declare(strict_types=1);

namespace Domain\Character\Services;

use Domain\Character\Enums\AdvancementType;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Services\Traits\CalculatesTier;
use Illuminate\Support\Facades\Log;

/**
 * Service for determining available advancement options for characters.
 * 
 * This service provides the single source of truth for what advancement options
 * are available at any given level, respecting max selections, mutual exclusivity,
 * and special rules (like multiclass restrictions).
 * 
 * Used by both CharacterBuilder (for initial character creation) and CharacterLevelUp
 * (for leveling existing characters) to ensure consistent logic.
 */
class AdvancementOptionsService
{
    use CalculatesTier;

    public function __construct(
        private GameDataLoader $gameDataLoader,
    ) {}

    /**
     * Get available advancement options for a character at a specific level
     *
     * Returns all advancement options available for the character's tier,
     * with availability flags based on special rules and mutual exclusivity.
     *
     * @param Character $character The character to get options for (determines class/multiclass domains)
     * @param int $level The level to get options for (determines tier and available options)
     * 
     * @return array{
     *     select_count: int,
     *     options: array<int, array{
     *         description: string,
     *         maxSelections: int,
     *         available: bool,
     *         slotsRequired: int,
     *         notes?: string
     *     }>
     * } Array with 'select_count' (number of advancements to select, always 2) 
     *    and 'options' array of advancement options with availability status
     */
    public function getAvailableOptions(Character $character, int $level): array
    {
        $tier = $this->calculateTier($level);
        $classData = $this->loadClassData($character->class);
        
        if (empty($classData)) {
            Log::warning('Class data not found for advancement options', [
                'character_id' => $character->id,
                'class' => $character->class,
                'level' => $level,
                'tier' => $tier,
            ]);
        }
        
        $tierOptions = $classData['tierOptions']["tier{$tier}"] ?? [];

        if (empty($tierOptions)) {
            Log::warning('No tier options found for character', [
                'character_id' => $character->id,
                'class' => $character->class,
                'level' => $level,
                'tier' => $tier,
                'has_class_data' => ! empty($classData),
            ]);

            return [
                'select_count' => 2,
                'options' => [],
            ];
        }

        $options = [];
        foreach ($tierOptions['options'] as $index => $option) {
            $options[] = [
                'index' => $index,
                'description' => $option['description'],
                'type' => $this->parseAdvancementType($option['description'])->value, // Explicit type field
                'max_selections' => $option['maxSelections'] ?? 1,
                'mutually_exclusive' => $option['mutuallyExclusive'] ?? null,
                'available' => $this->isOptionAvailable($character, $level, $option, $index),
                'selections_used' => $this->getSelectionCount($character, $level, $option),
                'slots_required' => $option['slotsRequired'] ?? 1,
                'notes' => $option['notes'] ?? null,
            ];
        }

        return [
            'select_count' => $tierOptions['selectCount'] ?? 2,
            'options' => $options,
        ];
    }

    /**
     * Check if an advancement option is available for selection
     *
     * @param Character $character The character selecting the option
     * @param int $level The level at which selection is happening
     * @param array $option The option data from tier options
     * @param int $optionIndex The index of the option in the tier options array
     * @return bool True if the option can be selected
     */
    public function isOptionAvailable(
        Character $character,
        int $level,
        array $option,
        int $optionIndex
    ): bool {
        // Check max selections across ALL tiers (not just this one)
        $used = $this->getSelectionCountAcrossAllTiers($character, $option['description']);
        $max = $option['maxSelections'] ?? 1;

        if ($used >= $max) {
            return false;
        }

        // Check mutual exclusivity within the current tier
        $tier = $this->calculateTier($level);
        if ($this->hasMutuallyExclusiveSelection($character, $tier, $option)) {
            return false;
        }

        // Check special rules (multiclass only at tier 3+, etc.)
        return $this->validateSpecialRules($character, $level, $option);
    }

    /**
     * Get the number of times an option has been selected for a specific level
     *
     * @param Character $character The character to check
     * @param int $level The level to check
     * @param array $option The option to count
     * @return int Number of times this option was selected at this level
     */
    public function getSelectionCount(Character $character, int $level, array $option): int
    {
        return CharacterAdvancement::where('character_id', $character->id)
            ->where('level', $level)
            ->where('description', $option['description'])
            ->count();
    }

    /**
     * Get the number of times an option has been selected across all tiers/levels
     *
     * @param Character $character The character to check
     * @param string $description The option description to match
     * @return int Total number of times this option was selected
     */
    private function getSelectionCountAcrossAllTiers(Character $character, string $description): int
    {
        return CharacterAdvancement::where('character_id', $character->id)
            ->where('description', $description)
            ->count();
    }

    /**
     * Check if a mutually exclusive option has been selected in the same tier
     *
     * @param Character $character The character to check
     * @param int $tier The tier to check within
     * @param array $option The option to check exclusivity for
     * @return bool True if a mutually exclusive option exists
     */
    private function hasMutuallyExclusiveSelection(
        Character $character,
        int $tier,
        array $option
    ): bool {
        // If no mutual exclusivity defined, return false
        if (! isset($option['mutuallyExclusive'])) {
            return false;
        }

        $exclusiveType = $option['mutuallyExclusive'];

        // Check if any advancement of the mutually exclusive type exists in this tier
        return CharacterAdvancement::where('character_id', $character->id)
            ->where('tier', $tier)
            ->where('advancement_type', $exclusiveType)
            ->exists();
    }

    /**
     * Validate special rules for advancement options
     *
     * @param Character $character The character to validate for
     * @param int $level The level at which selection is happening
     * @param array $option The option to validate
     * @return bool True if special rules are satisfied
     */
    private function validateSpecialRules(Character $character, int $level, array $option): bool
    {
        $description = strtolower($option['description']);

        // Multiclass is only available at tier 3+ (level 5+)
        if (str_contains($description, 'multiclass')) {
            if ($level < 5) {
                return false;
            }

            // Check if multiclass already taken (only one multiclass allowed per SRD)
            $hasMulticlass = CharacterAdvancement::where('character_id', $character->id)
                ->where('advancement_type', 'multiclass')
                ->exists();

            if ($hasMulticlass) {
                return false;
            }
        }

        // Subclass upgrade and multiclass are mutually exclusive in same tier
        if (str_contains($description, 'upgraded subclass') || str_contains($description, 'subclass card')) {
            $tier = $this->calculateTier($level);

            // Check if multiclass taken in this tier
            $hasMulticlassInTier = CharacterAdvancement::where('character_id', $character->id)
                ->where('tier', $tier)
                ->where('advancement_type', 'multiclass')
                ->exists();

            if ($hasMulticlassInTier) {
                return false;
            }
        }

        // Proficiency advancement requires 2 slots (both advancement slots for the level)
        if (str_contains($description, 'proficiency')) {
            // This is informational - UI must enforce selecting this option twice
            // or using both advancement slots for this option
        }

        return true;
    }

    /**
     * Calculate the tier for a given level
     *
     * @param int $level The character level
     * @return int The tier (1-4)
     */
    public function calculateTier(int $level): int
    {
        return match (true) {
            $level >= 8 => 4,
            $level >= 5 => 3,
            $level >= 2 => 2,
            default => 1,
        };
    }

    /**
     * Load class data using GameDataLoader service
     *
     * @param string $classKey The class key (e.g., 'warrior', 'wizard')
     * @return array The class data array
     */
    private function loadClassData(string $classKey): array
    {
        return $this->gameDataLoader->loadClassData($classKey);
    }

    /**
     * Parse an advancement option description to determine its type
     *
     * @param string $description The advancement option description
     * @return AdvancementType The advancement type enum
     */
    public function parseAdvancementType(string $description): AdvancementType
    {
        return AdvancementType::fromDescription($description);
    }
}

