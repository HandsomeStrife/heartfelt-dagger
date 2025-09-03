<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Enums\CharacterBuilderStep;
use Illuminate\Support\Facades\Storage;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterBuilderData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?string $character_key = null,
        public ?string $public_key = null,
        public ?string $name = null,
        public ?string $pronouns = null,
        public ?string $selected_class = null,
        public ?string $selected_subclass = null,
        public ?string $selected_ancestry = null,
        public ?string $selected_community = null,
        public array $assigned_traits = [],
        public array $selected_equipment = [],
        public array $experiences = [],
        public array $selected_domain_cards = [],
        public array $background_answers = [],
        public array $connection_answers = [],
        public ?string $profile_image_path = null,
        public ?string $physical_description = null,
        public ?string $personality_traits = null,
        public ?string $personal_history = null,
        public ?string $motivations = null,
        public array $manual_step_completions = [],
        public ?string $clank_bonus_experience = null,
    ) {}

    public function isStepComplete(CharacterBuilderStep|int $step): bool
    {
        // Convert int to enum for backward compatibility
        if (is_int($step)) {
            $step = CharacterBuilderStep::fromStepNumber($step);
            if (! $step) {
                return false;
            }
        }

        // Check if manually marked complete first
        if (in_array($step->value, $this->manual_step_completions)) {
            return true;
        }

        return match ($step) {
            CharacterBuilderStep::CLASS_SELECTION => ! empty($this->selected_class),
            CharacterBuilderStep::SUBCLASS_SELECTION => ! empty($this->selected_subclass),
            CharacterBuilderStep::ANCESTRY => ! empty($this->selected_ancestry),
            CharacterBuilderStep::COMMUNITY => ! empty($this->selected_community),
            CharacterBuilderStep::TRAITS => count($this->assigned_traits) === 6 && $this->validateTraitValues(),
            CharacterBuilderStep::EQUIPMENT => $this->isEquipmentComplete(),
            CharacterBuilderStep::BACKGROUND => $this->isBackgroundComplete(),
            CharacterBuilderStep::EXPERIENCES => count($this->experiences) >= 2,
            CharacterBuilderStep::DOMAIN_CARDS => count($this->selected_domain_cards) >= 2,
            CharacterBuilderStep::CONNECTIONS => count(array_filter($this->connection_answers, fn ($answer) => ! empty(trim($answer ?? '')))) >= 1,
        };
    }

    public function markStepComplete(CharacterBuilderStep|int $step): void
    {
        // Convert int to enum for backward compatibility
        if (is_int($step)) {
            $step = CharacterBuilderStep::fromStepNumber($step);
            if (! $step) {
                return;
            }
        }

        if (! in_array($step->value, $this->manual_step_completions)) {
            $this->manual_step_completions[] = $step->value;
        }
    }

    public function getCompletedSteps(): array
    {
        $completed = [];
        foreach (CharacterBuilderStep::getAllInOrder() as $step) {
            if ($this->isStepComplete($step)) {
                $completed[] = $step->getStepNumber();
            }
        }

        return $completed;
    }

    public function getProgressPercentage(): float
    {
        $completedSteps = count($this->getCompletedSteps());
        $totalSteps = count(CharacterBuilderStep::getAllInOrder());

        return round(($completedSteps / $totalSteps) * 100, 1);
    }

    public function canProceedToStep(int $step): bool
    {
        // Allow free navigation between all tabs
        $maxStep = count(CharacterBuilderStep::getAllInOrder());

        return $step >= 1 && $step <= $maxStep;
    }

    private function validateTraitValues(): bool
    {
        $expectedValues = [-1, 0, 0, 1, 1, 2];
        $actualValues = array_values($this->assigned_traits);
        sort($actualValues);
        sort($expectedValues);

        return $actualValues === $expectedValues;
    }

    public function getRemainingTraitValues(): array
    {
        $allValues = [-1, 0, 0, 1, 1, 2];
        $usedValues = array_values($this->assigned_traits);

        $remaining = [];
        foreach ($allValues as $value) {
            if (($key = array_search($value, $usedValues)) !== false) {
                unset($usedValues[$key]);
            } else {
                $remaining[] = $value;
            }
        }

        return $remaining;
    }

    public function getSelectedDomains(): array
    {
        // This would be loaded from the classes.json based on selected_class
        return match ($this->selected_class) {
            'warrior' => ['blade', 'bone'],
            'wizard' => ['codex', 'midnight'],
            'sorcerer' => ['arcana', 'splendor'],
            'bard' => ['grace', 'codex'],
            'druid' => ['sage', 'arcana'],
            'guardian' => ['valor', 'blade'],
            'ranger' => ['sage', 'bone'],
            'rogue' => ['midnight', 'grace'],
            'seraph' => ['splendor', 'valor'],
            default => [],
        };
    }

    /**
     * Get filtered domain cards based on selected class domains
     * Show all abilities for those domains (all levels).
     */
    public function getFilteredDomainCards(array $allDomains, array $allAbilities): array
    {
        if (empty($this->selected_class)) {
            return [];
        }

        $classDomains = $this->getSelectedDomains();
        $filteredCards = [];

        foreach ($classDomains as $domainKey) {
            if (isset($allDomains[$domainKey])) {
                $domain = $allDomains[$domainKey];
                $domainAbilities = [];

                // Include ALL abilities across levels for this domain
                if (isset($domain['abilitiesByLevel']) && is_array($domain['abilitiesByLevel'])) {
                    foreach ($domain['abilitiesByLevel'] as $level => $levelBlock) {
                        $abilityKeys = $levelBlock['abilities'] ?? [];
                        foreach ($abilityKeys as $abilityKey) {
                            if (isset($allAbilities[$abilityKey])) {
                                $domainAbilities[$abilityKey] = $allAbilities[$abilityKey];
                            }
                        }
                    }
                }

                if (! empty($domainAbilities)) {
                    $filteredCards[$domainKey] = [
                        'domain' => $domain,
                        'abilities' => $domainAbilities,
                    ];
                }
            }
        }

        return $filteredCards;
    }

    /**
     * Get background questions for selected class
     */
    public function getBackgroundQuestions(array $allClasses): array
    {
        if (empty($this->selected_class) || ! isset($allClasses[$this->selected_class])) {
            return [];
        }

        return $allClasses[$this->selected_class]['backgroundQuestions'] ?? [];
    }

    /**
     * Get connection questions for selected class
     */
    public function getConnectionQuestions(array $allClasses): array
    {
        if (empty($this->selected_class) || ! isset($allClasses[$this->selected_class])) {
            return [];
        }

        return $allClasses[$this->selected_class]['connections'] ?? [];
    }

    /**
     * Get available subclasses for selected class
     */
    public function getAvailableSubclasses(array $allClasses, array $allSubclasses): array
    {
        if (empty($this->selected_class) || ! isset($allClasses[$this->selected_class])) {
            return [];
        }

        $classSubclasses = $allClasses[$this->selected_class]['subclasses'] ?? [];
        $availableSubclasses = [];

        foreach ($classSubclasses as $subclassKey) {
            if (isset($allSubclasses[$subclassKey])) {
                $availableSubclasses[$subclassKey] = $allSubclasses[$subclassKey];
            }
        }

        return $availableSubclasses;
    }

    public function toArray(): array
    {
        return [
            'character_key' => $this->character_key,
            'public_key' => $this->public_key,
            'name' => $this->name,
            'selected_class' => $this->selected_class,
            'selected_subclass' => $this->selected_subclass,
            'selected_ancestry' => $this->selected_ancestry,
            'selected_community' => $this->selected_community,
            'assigned_traits' => $this->assigned_traits,
            'selected_equipment' => $this->selected_equipment,
            'experiences' => $this->experiences,
            'selected_domain_cards' => $this->selected_domain_cards,
            'background_answers' => $this->background_answers,
            'connection_answers' => $this->connection_answers,
            'profile_image_path' => $this->profile_image_path,
            'physical_description' => $this->physical_description,
            'personality_traits' => $this->personality_traits,
            'personal_history' => $this->personal_history,
            'motivations' => $this->motivations,
            'manual_step_completions' => $this->manual_step_completions,
            'clank_bonus_experience' => $this->clank_bonus_experience,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            character_key: $data['character_key'] ?? null,
            public_key: $data['public_key'] ?? null,
            name: $data['name'] ?? '',
            pronouns: $data['pronouns'] ?? null,
            selected_class: $data['selected_class'] ?? null,
            selected_subclass: $data['selected_subclass'] ?? null,
            selected_ancestry: $data['selected_ancestry'] ?? null,
            selected_community: $data['selected_community'] ?? null,
            assigned_traits: $data['assigned_traits'] ?? [],
            selected_equipment: $data['selected_equipment'] ?? [],
            experiences: $data['experiences'] ?? [],
            selected_domain_cards: $data['selected_domain_cards'] ?? [],
            background_answers: $data['background_answers'] ?? [],
            connection_answers: $data['connection_answers'] ?? [],
            profile_image_path: $data['profile_image_path'] ?? null,
            physical_description: $data['physical_description'] ?? null,
            personality_traits: $data['personality_traits'] ?? null,
            personal_history: $data['personal_history'] ?? null,
            motivations: $data['motivations'] ?? null,
            manual_step_completions: $data['manual_step_completions'] ?? [],
            clank_bonus_experience: $data['clank_bonus_experience'] ?? null,
        );
    }

    private function isEquipmentComplete(): bool
    {
        // Check for primary weapon and armor
        $has_primary_weapon = collect($this->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'weapon' && ($eq['data']['type'] ?? 'Primary') === 'Primary'
        );
        $has_armor = collect($this->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'armor');

        if (! $has_primary_weapon || ! $has_armor) {
            return false;
        }

        // Check if starting inventory requirements are met (both chooseOne and chooseExtra required)
        if ($this->selected_class) {
            // Load game data to check starting inventory requirements
            $classesPath = base_path('resources/json/classes.json');
            if (file_exists($classesPath)) {
                $classesData = json_decode(file_get_contents($classesPath), true);
                $classData = $classesData[$this->selected_class] ?? null;

                if ($classData && isset($classData['startingInventory'])) {
                    $startingInventory = $classData['startingInventory'];

                    // Handle known naming mismatches
                    $itemMappings = [
                        'minor healing potion' => 'minor health potion',
                        'minor stamina potion' => 'minor stamina potion',
                        'healing potion' => 'health potion',
                        'major healing potion' => 'major health potion',
                    ];

                    // Check chooseOne items
                    if (isset($startingInventory['chooseOne']) && is_array($startingInventory['chooseOne']) && ! empty($startingInventory['chooseOne'])) {
                        $has_choose_one_item = false;
                        foreach ($startingInventory['chooseOne'] as $item) {
                            $item_key = strtolower($item);
                            $mapped_key = $itemMappings[$item_key] ?? $item_key;

                            if (collect($this->selected_equipment)->contains(fn ($eq) => $eq['key'] === $mapped_key)) {
                                $has_choose_one_item = true;
                                break;
                            }
                        }

                        if (! $has_choose_one_item) {
                            return false;
                        }
                    }

                    // Check chooseExtra items
                    if (isset($startingInventory['chooseExtra']) && is_array($startingInventory['chooseExtra']) && ! empty($startingInventory['chooseExtra'])) {
                        $has_choose_extra_item = false;
                        foreach ($startingInventory['chooseExtra'] as $item) {
                            $item_key = strtolower($item);
                            $mapped_key = $itemMappings[$item_key] ?? $item_key;

                            if (collect($this->selected_equipment)->contains(fn ($eq) => $eq['key'] === $mapped_key)) {
                                $has_choose_extra_item = true;
                                break;
                            }
                        }

                        if (! $has_choose_extra_item) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    private function isBackgroundComplete(): bool
    {
        // Must have a class selected to have background questions
        if (empty($this->selected_class)) {
            return false;
        }

        // Must have at least one background answer
        $answered_questions = count(array_filter($this->background_answers, fn ($answer) => ! empty(trim($answer))));

        return $answered_questions >= 1;
    }

    /**
     * Get ancestry bonus for evasion from effects
     */
    public function getAncestryEvasionBonus(): int
    {
        $effects = $this->getAncestryEffects('evasion_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }
        return $bonus;
    }

    /**
     * Get ancestry bonus for hit points from effects
     */
    public function getAncestryHitPointBonus(): int
    {
        $effects = $this->getAncestryEffects('hit_point_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }
        return $bonus;
    }

    /**
     * Get ancestry bonus for stress from effects
     */
    public function getAncestryStressBonus(): int
    {
        $effects = $this->getAncestryEffects('stress_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }
        return $bonus;
    }

    /**
     * Get ancestry bonus for damage thresholds from effects
     */
    public function getAncestryDamageThresholdBonus(): int
    {
        $effects = $this->getAncestryEffects('damage_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            if ($value === 'proficiency') {
                $bonus += 2; // Base proficiency at level 1 for character creation
            } else {
                $bonus += (int)$value;
            }
        }
        return $bonus;
    }

    /**
     * Get all applied ancestry bonuses
     */
    public function getAncestryBonuses(): array
    {
        $bonuses = [];

        if ($this->selected_ancestry) {
            $evasion_bonus = $this->getAncestryEvasionBonus();
            $hit_point_bonus = $this->getAncestryHitPointBonus();
            $stress_bonus = $this->getAncestryStressBonus();
            $damage_threshold_bonus = $this->getAncestryDamageThresholdBonus();

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
        }

        return $bonuses;
    }

    /**
     * Get computed character stats including ancestry bonuses
     */
    public function getComputedStats(array $class_data = []): array
    {
        if (empty($class_data)) {
            return [];
        }

        $base_evasion = $class_data['startingEvasion'] ?? 10;
        $base_hit_points = $class_data['startingHitPoints'] ?? 5;
        $agility_modifier = $this->assigned_traits['agility'] ?? 0;

        // Calculate armor score from selected equipment (support multiple key names)
        $armor_score = 0;
        foreach ($this->selected_equipment as $equipment) {
            if (($equipment['type'] ?? null) === 'armor') {
                $data = $equipment['data'] ?? [];
                $armor_score += $data['baseScore']
                    ?? $data['armor_score']
                    ?? $data['score']
                    ?? 0;
            }
        }

        // Get ancestry bonuses
        $ancestry_evasion_bonus = $this->getAncestryEvasionBonus();
        $ancestry_hit_point_bonus = $this->getAncestryHitPointBonus();
        $ancestry_stress_bonus = $this->getAncestryStressBonus();
        $ancestry_damage_threshold_bonus = $this->getAncestryDamageThresholdBonus();

        // Calculate final stats
        $final_evasion = $base_evasion + $agility_modifier + $ancestry_evasion_bonus;
        $final_hit_points = $base_hit_points + $ancestry_hit_point_bonus; // Base hit points + ancestry bonus
        $final_stress = 6 + $ancestry_stress_bonus; // Every PC starts with 6 stress slots + ancestry bonus
        $major_threshold = max(1, $armor_score + 4 + $ancestry_damage_threshold_bonus); // Level 1 + 3 + ancestry
        $severe_threshold = max(1, $armor_score + 9 + $ancestry_damage_threshold_bonus); // Level 1 + 8 + ancestry

        return [
            // Simple values for tests and general use
            'evasion' => $final_evasion,
            'hit_points' => $final_hit_points,
            'stress' => $final_stress,
            'hope' => 2,
            'major_threshold' => $major_threshold,
            'severe_threshold' => $severe_threshold,
            'armor_score' => $armor_score,
            
            // Detailed breakdown for UI
            'detailed' => [
                'evasion' => [
                    'base' => $base_evasion,
                    'agility_modifier' => $agility_modifier,
                    'ancestry_bonus' => $ancestry_evasion_bonus,
                    'total' => $final_evasion,
                ],
                'hit_points' => [
                    'base' => $base_hit_points,
                    'ancestry_bonus' => $ancestry_hit_point_bonus,
                    'total' => $final_hit_points,
                ],
                'stress' => [
                    'base' => 6,
                    'ancestry_bonus' => $ancestry_stress_bonus,
                    'total' => $final_stress,
                ],
                'damage_thresholds' => [
                    'major' => $major_threshold,
                    'severe' => $severe_threshold,
                    'ancestry_bonus' => $ancestry_damage_threshold_bonus,
                ],
            ],
        ];
    }

    /**
     * Get subclass effects by type
     */
    public function getSubclassEffects(string $effectType): array
    {
        if (!$this->selected_subclass) {
            return [];
        }
        
        $subclassData = $this->getSubclassData();
        if (!$subclassData) {
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
     * Get subclass data from JSON file
     */
    private function getSubclassData(): ?array
    {
        $path = resource_path('json/subclasses.json');
        if (!file_exists($path)) {
            return null;
        }
        
        $subclasses = json_decode(file_get_contents($path), true);
        return $subclasses[$this->selected_subclass] ?? null;
    }

    /**
     * Get subclass bonus for evasion from effects
     */
    public function getSubclassEvasionBonus(): int
    {
        $effects = $this->getSubclassEffects('evasion_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }
        return $bonus;
    }

    /**
     * Get subclass bonus for hit points from effects
     */
    public function getSubclassHitPointBonus(): int
    {
        $effects = $this->getSubclassEffects('hit_point_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }
        return $bonus;
    }

    /**
     * Get subclass bonus for stress from effects
     */
    public function getSubclassStressBonus(): int
    {
        $effects = $this->getSubclassEffects('stress_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }
        return $bonus;
    }

    /**
     * Get subclass bonus for damage thresholds from effects
     */
    public function getSubclassDamageThresholdBonus(): int
    {
        $effects = $this->getSubclassEffects('damage_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            $bonus += (int)$value;
        }
        return $bonus;
    }

    /**
     * Get subclass bonus for severe damage threshold from effects
     */
    public function getSubclassSevereThresholdBonus(): int
    {
        $effects = $this->getSubclassEffects('severe_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            $bonus += (int)$value;
        }
        return $bonus;
    }

    /**
     * Get subclass bonus for domain cards from effects
     */
    public function getSubclassDomainCardBonus(): int
    {
        $effects = $this->getSubclassEffects('domain_card_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }
        return $bonus;
    }

    /**
     * Get the maximum number of domain cards this character can have
     */
    public function getMaxDomainCards(): int
    {
        // Base starting domain cards for all characters
        $base_cards = 2;
        
        // Add subclass bonuses
        $subclass_bonus = $this->getSubclassDomainCardBonus();
        
        return $base_cards + $subclass_bonus;
    }

    /**
     * Get ancestry effects by type
     */
    public function getAncestryEffects(string $effectType): array
    {
        if (!$this->selected_ancestry) {
            return [];
        }
        
        $ancestriesData = $this->getAncestryData();
        if (!$ancestriesData) {
            return [];
        }
        
        $effects = [];
        $features = $ancestriesData['features'] ?? [];
        
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
     * Get ancestry data from JSON file
     */
    private function getAncestryData(): ?array
    {
        $path = resource_path('json/ancestries.json');
        if (!file_exists($path)) {
            return null;
        }
        
        $ancestries = json_decode(file_get_contents($path), true);
        return $ancestries[$this->selected_ancestry] ?? null;
    }

    /**
     * Check if the selected ancestry has experience bonus selection effect
     */
    public function hasExperienceBonusSelection(): bool
    {
        return !empty($this->getAncestryEffects('experience_bonus_selection'));
    }

    /**
     * Get the experience that receives bonus from experience_bonus_selection effect
     */
    public function getClankBonusExperience(): ?string
    {
        if (!$this->hasExperienceBonusSelection()) {
            return null;
        }
        
        return $this->clank_bonus_experience;
    }

    /**
     * Get the modifier for a specific experience (including ancestry bonuses)
     */
    public function getExperienceModifier(string $experienceName): int
    {
        $baseModifier = 2; // All experiences start with +2
        
        // Check if this experience gets experience bonus selection effect
        if ($this->hasExperienceBonusSelection() && 
            $this->getClankBonusExperience() === $experienceName) {
            
            $effects = $this->getAncestryEffects('experience_bonus_selection');
            $bonus = 0;
            foreach ($effects as $effect) {
                $bonus += $effect['value'] ?? 0;
            }
            return $baseModifier + $bonus;
        }
        
        return $baseModifier;
    }

    /**
     * Get the profile image URL using signed URL for security
     */
    public function getProfileImage(): string
    {
        if ($this->profile_image_path) {
            $s3Disk = Storage::disk('s3');
            if ($s3Disk->exists($this->profile_image_path)) {
                return $s3Disk->temporaryUrl(
                    $this->profile_image_path,
                    now()->addHours(24) // URLs valid for 24 hours
                );
            }
        }

        return asset('img/default-avatar.png');
    }

}
