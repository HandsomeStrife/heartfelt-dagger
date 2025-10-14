<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Illuminate\Support\Facades\DB;

class ApplyAdvancementAction
{
    public function execute(Character $character, CharacterAdvancementData $advancement_data, ?int $level = null): CharacterAdvancement
    {
        return DB::transaction(function () use ($character, $advancement_data, $level) {
            // Use provided level or calculate from tier
            $character_level = $level ?? $character->level;
            // Validate tier range
            if ($advancement_data->tier < 1 || $advancement_data->tier > 4) {
                throw new \InvalidArgumentException('Tier must be between 1 and 4');
            }

            // Validate advancement number range
            if ($advancement_data->advancement_number < 1 || $advancement_data->advancement_number > 2) {
                throw new \InvalidArgumentException('Advancement number must be 1 or 2');
            }

            // Validate that this advancement slot is available for this level
            $existing_advancement = CharacterAdvancement::where([
                'character_id' => $character->id,
                'level' => $character_level,
                'advancement_number' => $advancement_data->advancement_number,
            ])->first();

            if ($existing_advancement) {
                throw new \InvalidArgumentException('Advancement slot already taken for this level');
            }

            // Validate tier progression (character must be at appropriate level)
            $required_level = match ($advancement_data->tier) {
                1 => 1,
                2 => 1,
                3 => 5,
                4 => 7,
                default => throw new \InvalidArgumentException('Tier must be between 1 and 4'),
            };

            // Use $character_level (which may be passed in) instead of $character->level
            if ($character_level < $required_level) {
                throw new \InvalidArgumentException("Character level {$character_level} insufficient for tier {$advancement_data->tier} (requires level {$required_level})");
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
            $advancement = CharacterAdvancement::create([
                'character_id' => $character->id,
                'tier' => $advancement_data->tier,
                'level' => $character_level,
                'advancement_number' => $advancement_data->advancement_number,
                'advancement_type' => $advancement_data->advancement_type,
                'advancement_data' => $advancement_data->advancement_data,
                'description' => $advancement_data->description,
            ]);

            // Apply advancement-specific effects
            $this->applyAdvancementEffects($character, $advancement_data);

            return $advancement;
        });
    }

    /**
     * Get available advancement options for a character at a specific tier
     */
    public function getAvailableOptions(Character $character, int $tier): array
    {
        // Load class data to get tier options
        $class_data_path = resource_path('json/classes.json');
        if (! file_exists($class_data_path)) {
            return [];
        }

        $classes_data = json_decode(file_get_contents($class_data_path), true);
        $class_data = $classes_data[$character->class] ?? null;

        if (! $class_data || ! isset($class_data['tierOptions']["tier{$tier}"])) {
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
            $level = match ($tier) {
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

    /**
     * Apply advancement-specific effects to the character
     */
    private function applyAdvancementEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        switch ($advancement_data->advancement_type) {
            case 'trait_bonus':
                $this->applyTraitBonusEffects($character, $advancement_data);
                break;
            case 'hit_point':
                $this->applyHitPointEffects($character, $advancement_data);
                break;
            case 'stress':
                $this->applyStressEffects($character, $advancement_data);
                break;
            case 'domain_card':
                $this->applyDomainCardEffects($character, $advancement_data);
                break;
            case 'proficiency_advancement':
                $this->applyProficiencyAdvancementEffects($character, $advancement_data);
                break;
            case 'subclass_upgrade':
                $this->applySubclassUpgradeEffects($character, $advancement_data);
                break;
            case 'multiclass':
                $this->applyMulticlassEffects($character, $advancement_data);
                break;
            case 'experience_bonus':
                $this->applyExperienceBonusEffects($character, $advancement_data);
                break;
            case 'evasion':
                $this->applyEvasionEffects($character, $advancement_data);
                break;
                // Add other advancement types as needed
        }
    }

    /**
     * Apply trait bonus advancement effects (mark traits)
     */
    private function applyTraitBonusEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        $traits = $advancement_data->advancement_data['traits'] ?? [];

        foreach ($traits as $trait_name) {
            // Mark the trait in the database
            $character->traits()
                ->where('trait_name', $trait_name)
                ->update(['is_marked' => true]);
        }
    }

    /**
     * Apply hit point advancement effects
     */
    private function applyHitPointEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        // Hit point bonuses are handled through the advancement record itself
        // No additional database changes needed
    }

    /**
     * Apply stress advancement effects
     */
    private function applyStressEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        // Stress bonuses are handled through the advancement record itself
        // No additional database changes needed
    }

    /**
     * Apply domain card advancement effects (create domain card record)
     */
    private function applyDomainCardEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        $ability_key = $advancement_data->advancement_data['ability_key'] ?? null;
        
        if (!$ability_key) {
            throw new \InvalidArgumentException('Domain card advancement requires ability_key in advancement_data');
        }

        // Load abilities data to get domain and level information
        $abilities_path = resource_path('json/abilities.json');
        if (!file_exists($abilities_path)) {
            throw new \RuntimeException('Abilities data file not found');
        }

        $abilities = json_decode(file_get_contents($abilities_path), true);
        $ability_data = $abilities[$ability_key] ?? null;

        if (!$ability_data) {
            throw new \InvalidArgumentException("Invalid ability key: {$ability_key}");
        }

        // Create the domain card record
        \Domain\Character\Models\CharacterDomainCard::create([
            'character_id' => $character->id,
            'ability_key' => $ability_key,
            'domain' => $ability_data['domain'] ?? '',
            'ability_level' => $ability_data['level'] ?? 1,
        ]);
    }

    /**
     * Apply proficiency advancement effects
     */
    private function applyProficiencyAdvancementEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        // Proficiency advancements are handled through the advancement record itself
        // The proficiency bonus is calculated by summing all proficiency advancement bonuses
        // No additional database changes needed
    }

    /**
     * Apply subclass upgrade effects
     */
    private function applySubclassUpgradeEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        // For now, this is tracked through the advancement record
        // Future implementation could update character subclass progression state
        // No additional database changes needed currently
    }

    /**
     * Apply multiclass effects
     */
    private function applyMulticlassEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        // For now, this is tracked through the advancement record
        // Future implementation could create multiclass records and update character domains
        // No additional database changes needed currently
    }

    /**
     * Apply experience bonus effects
     */
    private function applyExperienceBonusEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        $experience_bonuses = $advancement_data->advancement_data['experience_bonuses'] ?? [];
        
        foreach ($experience_bonuses as $experience_name) {
            // Find the experience and apply +1 bonus
            $experience = $character->experiences()
                ->where('experience_name', $experience_name)
                ->first();
                
            if ($experience) {
                $experience->increment('modifier', 1);
            }
        }
    }

    /**
     * Apply evasion advancement effects
     */
    private function applyEvasionEffects(Character $character, CharacterAdvancementData $advancement_data): void
    {
        // Evasion bonuses are handled through the advancement record itself
        // No additional database changes needed
    }
}
