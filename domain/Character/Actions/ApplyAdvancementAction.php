<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Illuminate\Support\Facades\DB;

class ApplyAdvancementAction
{
    public function execute(Character $character, CharacterAdvancementData $advancement_data): CharacterAdvancement
    {
        return DB::transaction(function () use ($character, $advancement_data) {
            // Validate tier range
            if ($advancement_data->tier < 1 || $advancement_data->tier > 4) {
                throw new \InvalidArgumentException('Tier must be between 1 and 4');
            }

            // Validate advancement number range
            if ($advancement_data->advancement_number < 1 || $advancement_data->advancement_number > 2) {
                throw new \InvalidArgumentException('Advancement number must be 1 or 2');
            }

            // Validate that this advancement slot is available
            $existing_advancement = CharacterAdvancement::where([
                'character_id' => $character->id,
                'tier' => $advancement_data->tier,
                'advancement_number' => $advancement_data->advancement_number,
            ])->first();

            if ($existing_advancement) {
                throw new \InvalidArgumentException('Advancement slot already taken');
            }

            // Validate tier progression (character must be at appropriate level)
            $required_level = match($advancement_data->tier) {
                1 => 1,
                2 => 1,
                3 => 5,
                4 => 7,
                default => throw new \InvalidArgumentException('Tier must be between 1 and 4'),
            };

            if ($character->level < $required_level) {
                throw new \InvalidArgumentException("Character level insufficient for tier {$advancement_data->tier}");
            }

            // Validate advancement-specific requirements
            if ($advancement_data->advancement_type === 'multiclass') {
                $class = $advancement_data->advancement_data['class'] ?? '';
                if (empty($class)) {
                    throw new \InvalidArgumentException('Multiclass advancement requires a class selection');
                }
            }

            if ($advancement_data->advancement_type === 'trait_bonus') {
                $traits = $advancement_data->advancement_data['traits'] ?? [];
                if (empty($traits)) {
                    throw new \InvalidArgumentException('Trait bonus advancement requires at least one trait');
                }
            }

            // Create the advancement record
            return CharacterAdvancement::create([
                'character_id' => $character->id,
                'tier' => $advancement_data->tier,
                'advancement_number' => $advancement_data->advancement_number,
                'advancement_type' => $advancement_data->advancement_type,
                'advancement_data' => $advancement_data->advancement_data,
                'description' => $advancement_data->description,
            ]);
        });
    }

    /**
     * Get available advancement options for a character at a specific tier
     */
    public function getAvailableOptions(Character $character, int $tier): array
    {
        // Load class data to get tier options
        $class_data_path = resource_path("json/classes.json");
        if (!file_exists($class_data_path)) {
            return [];
        }

        $classes_data = json_decode(file_get_contents($class_data_path), true);
        $class_data = $classes_data[$character->class] ?? null;
        
        if (!$class_data || !isset($class_data['tierOptions']["tier{$tier}"])) {
            return [];
        }

        $tier_options = $class_data['tierOptions']["tier{$tier}"];
        $options = [];

        foreach ($tier_options['options'] as $index => $option) {
            $options[] = [
                'index' => $index,
                'description' => $option['description'],
                'max_selections' => $option['maxSelections'] ?? 1,
                'mutually_exclusive' => $option['mutuallyExclusive'] ?? null,
                'available' => $this->isOptionAvailable($character, $tier, $option),
            ];
        }

        return [
            'select_count' => $tier_options['selectCount'],
            'options' => $options,
        ];
    }

    /**
     * Check if a specific advancement option is available for selection
     */
    private function isOptionAvailable(Character $character, int $tier, array $option): bool
    {
        // Check max selections limit
        $max_selections = $option['maxSelections'] ?? 1;
        $description = $option['description'];

        // Count how many times this option has been selected across all tiers
        $times_selected = CharacterAdvancement::where('character_id', $character->id)
            ->where('description', $description)
            ->count();

        if ($times_selected >= $max_selections) {
            return false;
        }

        // Check mutual exclusivity
        if (isset($option['mutuallyExclusive'])) {
            $exclusive_type = $option['mutuallyExclusive'];
            
            // Check if any mutually exclusive option has been selected in this tier
            $exclusive_selected = CharacterAdvancement::where('character_id', $character->id)
                ->where('tier', $tier)
                ->where('advancement_type', $exclusive_type)
                ->exists();

            if ($exclusive_selected) {
                return false;
            }
        }

        // Special validation for multiclass options
        if (str_contains($description, 'Multiclass')) {
            // Multiclass is only available at tier 3 and 4
            if ($tier < 3) {
                return false;
            }

            // Check if subclass advancement has been taken in this tier (mutually exclusive)
            $subclass_taken = CharacterAdvancement::where('character_id', $character->id)
                ->where('tier', $tier)
                ->where('advancement_type', 'subclass')
                ->exists();

            if ($subclass_taken) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse advancement option description to determine type and data
     */
    public function parseAdvancementOption(string $description, int $tier, int $advancement_number, array $user_selections = []): CharacterAdvancementData
    {
        // This is a simplified parser - in a real implementation you'd want more sophisticated parsing
        if (str_contains($description, 'trait') && str_contains($description, '+1 bonus')) {
            $traits = $user_selections['traits'] ?? ['agility', 'strength']; // Default example
            return CharacterAdvancementData::traitBonus($tier, $advancement_number, $traits);
        }

        if (str_contains($description, 'Hit Point')) {
            return CharacterAdvancementData::hitPoint($tier, $advancement_number);
        }

        if (str_contains($description, 'Stress')) {
            return CharacterAdvancementData::stress($tier, $advancement_number);
        }

        if (str_contains($description, 'Experience')) {
            return CharacterAdvancementData::experienceBonus($tier, $advancement_number);
        }

        if (str_contains($description, 'domain card')) {
            $level = match($tier) {
                2 => 2,
                3 => 3,
                default => 4,
            };
            return CharacterAdvancementData::domainCard($tier, $advancement_number, $level);
        }

        if (str_contains($description, 'Evasion')) {
            return CharacterAdvancementData::evasion($tier, $advancement_number);
        }

        if (str_contains($description, 'Proficiency')) {
            return CharacterAdvancementData::proficiency($tier, $advancement_number);
        }

        if (str_contains($description, 'subclass')) {
            $subclass_key = $user_selections['subclass'] ?? 'example'; // Would need user input
            return CharacterAdvancementData::subclass($tier, $advancement_number, $subclass_key);
        }

        if (str_contains($description, 'Multiclass')) {
            $class_key = $user_selections['class'] ?? 'warrior'; // Would need user input
            return CharacterAdvancementData::multiclass($tier, $advancement_number, $class_key);
        }

        // Default fallback
        throw new \InvalidArgumentException("Unable to parse advancement option: {$description}");
    }
}
