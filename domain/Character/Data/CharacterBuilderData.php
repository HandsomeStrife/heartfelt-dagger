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
        
        /** @var array<string, int> Assigned trait values. Key is trait name (agility, strength, finesse, instinct, presence, knowledge), value is trait modifier (-1, 0, 0, 1, 1, 2) */
        public array $assigned_traits = [],
        
        /** @var array<string, array{key: string, name: string, type: string, slot?: string}> Selected equipment. Key is slot type (primary_weapon, secondary_weapon, armor, starting_inventory), value is equipment data */
        public array $selected_equipment = [],
        
        /** @var array<int, array{name: string, description: string, modifier: int}> Starting experiences (min 2 required). Each provides +2 to relevant rolls */
        public array $experiences = [],
        
        /** @var array<int, string> Selected domain card keys from character's accessible domains */
        public array $selected_domain_cards = [],
        
        /** @var array<int, string> Answers to class-specific background questions (3 questions) */
        public array $background_answers = [],
        
        /** @var array<int, string> Answers to class-specific connection questions (3 questions) */
        public array $connection_answers = [],
        public ?string $profile_image_path = null,
        public ?string $physical_description = null,
        public ?string $personality_traits = null,
        public ?string $personal_history = null,
        public ?string $motivations = null,
        
        /** @var array<int, string> Array of manually completed step values */
        public array $manual_step_completions = [],
        
        public ?string $clank_bonus_experience = null,
        
        // Higher-level character creation properties
        
        /** @var int Starting level for character creation (1-10) */
        public int $starting_level = 1,
        
        /**
         * @var array<int, array<int, array{
         *     type: string,
         *     traits?: array<int, string>,
         *     domain_card?: string,
         *     selected_domain?: string,
         *     experience?: array{name: string, description: string}
         * }>> Advancements selected per level. Key is level number (2-10), 
         *     value is array of 2 advancement selections with their specific data
         */
        public array $creation_advancements = [],
        
        /**
         * @var array<int, array{
         *     name: string,
         *     description: string
         * }> Tier achievement experiences created per level. Key is level number (2, 5, or 8),
         *    value is the experience created for that tier achievement
         */
        public array $creation_tier_experiences = [],
        
        /**
         * @var array<int, string> Domain cards selected per level. Key is level number (1-10),
         *    value is the domain card key (ability key from abilities.json)
         */
        public array $creation_domain_cards = [],
        
        /**
         * @var array<string, array{
         *     domain: string,
         *     ability_key: string,
         *     ability_level: int,
         *     name: string
         * }> Domain cards granted by "additional domain card" advancements. 
         *     Key is "adv_{level}_{advIndex}" (e.g., "adv_3_0"), value is domain card data
         */
        public array $creation_advancement_cards = [],
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
            CharacterBuilderStep::EXPERIENCES => $this->isExperiencesComplete(),
            CharacterBuilderStep::DOMAIN_CARDS => $this->isDomainCardsComplete(),
            CharacterBuilderStep::CONNECTIONS => count(array_filter($this->connection_answers, fn ($answer) => ! empty(trim($answer ?? '')))) >= 1,
            default => false, // Handle any unknown steps gracefully
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
            'starting_level' => $this->starting_level,
            'creation_advancements' => $this->creation_advancements,
            'creation_tier_experiences' => $this->creation_tier_experiences,
            'creation_domain_cards' => $this->creation_domain_cards,
            'creation_advancement_cards' => $this->creation_advancement_cards,
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
            starting_level: $data['starting_level'] ?? 1,
            creation_advancements: $data['creation_advancements'] ?? [],
            creation_tier_experiences: $data['creation_tier_experiences'] ?? [],
            creation_domain_cards: $data['creation_domain_cards'] ?? [],
            creation_advancement_cards: $data['creation_advancement_cards'] ?? [],
        );
    }

    private function isEquipmentComplete(): bool
    {
        $validator = app(\Domain\Character\Services\EquipmentValidator::class);

        return $validator->isEquipmentComplete($this->selected_equipment, $this->selected_class);
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

    private function isExperiencesComplete(): bool
    {
        // Base experiences (2 required at level 1)
        $baseExperiencesComplete = count($this->experiences) >= 2;
        
        // If level 1, only base experiences needed
        if ($this->starting_level === 1) {
            return $baseExperiencesComplete;
        }
        
        // Check tier achievement experiences for levels 2, 5, 8
        $tierLevels = array_filter([2, 5, 8], fn($level) => $level <= $this->starting_level);
        foreach ($tierLevels as $level) {
            if (!isset($this->creation_tier_experiences[$level]) || 
                empty($this->creation_tier_experiences[$level]['name'])) {
                return false;
            }
        }
        
        return $baseExperiencesComplete;
    }

    private function isDomainCardsComplete(): bool
    {
        // Count total domain cards from all sources:
        // 1. selected_domain_cards (level 1 cards)
        // 2. creation_domain_cards (level 2+ regular selections)
        // 3. creation_advancement_cards (advancement-granted bonus cards)
        
        $level1Cards = count($this->selected_domain_cards);
        $creationCards = count($this->creation_domain_cards);
        $advancementCards = count($this->creation_advancement_cards);
        
        $totalCards = $level1Cards + $creationCards + $advancementCards;
        
        // Required cards = starting_level + 1
        // Level 1: 2 cards (1 + 1 = 2)
        // Level 2: 3 cards (2 + 1 = 3)
        // Level 3: 4 cards (3 + 1 = 4), etc.
        $requiredCards = $this->starting_level + 1;
        
        return $totalCards >= $requiredCards;
    }

    private function isAdvancementsComplete(): bool
    {
        // If starting at level 1, no advancements needed
        if ($this->starting_level === 1) {
            return true;
        }

        // Use the validateAdvancementSelections method to check completeness
        $errors = $this->validateAdvancementSelections();

        return empty($errors);
    }


    /**
     * Check if character creation requires advancement selection
     * (character starting at level 2+)
     */
    public function requiresAdvancementSelection(): bool
    {
        return $this->starting_level > 1;
    }

    /**
     * Validate all advancement selections are complete for levels 2 through starting_level
     *
     * @return array Array of validation errors keyed by level
     */
    public function validateAdvancementSelections(): array
    {
        $errors = [];

        if ($this->starting_level === 1) {
            return $errors; // No advancements needed for level 1
        }

        for ($level = 2; $level <= $this->starting_level; $level++) {
            $levelErrors = [];

            // Check tier achievements for levels 2, 5, 8
            if (in_array($level, [2, 5, 8])) {
                if (! isset($this->creation_tier_experiences[$level])) {
                    $levelErrors[] = 'Missing tier achievement experience';
                } elseif (empty($this->creation_tier_experiences[$level]['name'])) {
                    $levelErrors[] = 'Tier achievement experience must have a name';
                }
            }

            // Check domain card (required for EVERY level per SRD)
            if (! isset($this->creation_domain_cards[$level]) || empty($this->creation_domain_cards[$level])) {
                $levelErrors[] = 'Missing domain card selection';
            }

            // Check advancements (exactly 2 required per level)
            if (! isset($this->creation_advancements[$level])) {
                $levelErrors[] = 'Missing advancement selections';
            } elseif (count($this->creation_advancements[$level]) !== 2) {
                $levelErrors[] = sprintf(
                    'Must select exactly 2 advancements (selected %d)',
                    count($this->creation_advancements[$level])
                );
            }

            if (! empty($levelErrors)) {
                $errors[$level] = $levelErrors;
            }
        }

        return $errors;
    }

    /**
     * Validate a single level's completion
     *
     * @param int $level The level to validate
     * @return array Array of validation errors for this level
     */
    public function validateLevelCompletion(int $level): array
    {
        $errors = [];

        if ($level === 1 || $level > $this->starting_level) {
            return $errors; // No validation needed
        }

        // Check tier achievements for levels 2, 5, 8
        if (in_array($level, [2, 5, 8])) {
            if (! isset($this->creation_tier_experiences[$level])) {
                $errors[] = 'Missing tier achievement experience';
            } else {
                $experience = $this->creation_tier_experiences[$level];
                
                // Validate experience name
                if (empty($experience['name'])) {
                    $errors[] = 'Tier achievement experience must have a name';
                } elseif (strlen($experience['name']) < 1 || strlen($experience['name']) > 255) {
                    $errors[] = 'Tier achievement experience name must be between 1 and 255 characters';
                }
                
                // Validate experience description if provided
                if (isset($experience['description']) && strlen($experience['description']) > 500) {
                    $errors[] = 'Tier achievement experience description cannot exceed 500 characters';
                }
                
                // Check for invalid characters
                if (isset($experience['name']) && preg_match('/[<>]/', $experience['name'])) {
                    $errors[] = 'Tier achievement experience name contains invalid characters';
                }
            }
        }

        // Check domain card (required for ALL levels)
        if (! isset($this->creation_domain_cards[$level]) || empty($this->creation_domain_cards[$level])) {
            $errors[] = 'Missing required domain card selection';
        }

        // Check advancements (exactly 2 required)
        $levelAdvancements = $this->creation_advancements[$level] ?? [];
        if (count($levelAdvancements) !== 2) {
            $errors[] = sprintf('Exactly 2 advancements required, %d selected', count($levelAdvancements));
        } else {
            // Validate each advancement
            $validTypes = ['trait_bonus', 'hit_point', 'stress_slot', 'stress', 'experience_bonus', 
                           'domain_card', 'evasion', 'subclass_upgrade', 'proficiency', 'multiclass'];
            $selectedTypes = [];
            
            foreach ($levelAdvancements as $index => $advancement) {
                $type = $advancement['type'] ?? null;
                
                // Validate type exists and is valid
                if (! $type) {
                    $errors[] = sprintf('Advancement #%d is missing type', $index + 1);
                    continue;
                }
                
                if (! in_array($type, $validTypes)) {
                    $errors[] = sprintf('Advancement #%d has invalid type: %s', $index + 1, $type);
                    continue;
                }
                
                // Check for duplicate selections of same type (where not allowed)
                if ($type !== 'trait_bonus' && $type !== 'hit_point') {
                    if (in_array($type, $selectedTypes)) {
                        $errors[] = sprintf('Advancement type "%s" can only be selected once per level', $type);
                    }
                    $selectedTypes[] = $type;
                }
                
                // Validate type-specific requirements
                if ($type === 'trait_bonus') {
                    if (empty($advancement['traits']) || count($advancement['traits']) !== 2) {
                        $errors[] = sprintf('Trait bonus advancement must select exactly 2 traits');
                    }
                }
                
                // Validate experience_bonus type (as per code review)
                if ($type === 'experience_bonus') {
                    if (empty($advancement['experiences']) || count($advancement['experiences']) !== 2) {
                        $errors[] = sprintf('Experience bonus advancement must select exactly 2 experiences');
                    }
                }
                
                // Validate domain_card type (advancement-granted cards)
                if ($type === 'domain_card') {
                    $advKey = "adv_{$level}_{$index}";
                    if (!isset($this->creation_advancement_cards[$advKey]) || empty($this->creation_advancement_cards[$advKey])) {
                        $errors[] = sprintf('Missing domain card for "Additional Domain Card" advancement #%d', $index + 1);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Check if a specific level is complete
     *
     * @param int $level The level to check
     * @return bool
     */
    public function isLevelComplete(int $level): bool
    {
        return empty($this->validateLevelCompletion($level));
    }

    /**
     * Validate selections across all levels for consistency
     *
     * @return array Array of validation errors for cross-level issues
     */
    public function validateCrossLevelSelections(): array
    {
        $errors = [];

        // Check domain card duplicates across all selection types
        $allDomainCards = [];
        
        // Check level 1 cards (selected_domain_cards)
        foreach ($this->selected_domain_cards as $cardKey) {
            if (isset($allDomainCards[$cardKey])) {
                $errors[] = sprintf(
                    'Domain card "%s" selected multiple times (level 1 and level %d)',
                    $cardKey,
                    $allDomainCards[$cardKey]
                );
            }
            $allDomainCards[$cardKey] = 1;
        }
        
        // Check level 2+ regular selections (creation_domain_cards)
        for ($level = 2; $level <= $this->starting_level; $level++) {
            $cardData = $this->creation_domain_cards[$level] ?? null;
            if ($cardData && isset($cardData['ability_key'])) {
                $cardKey = $cardData['ability_key'];
                if (isset($allDomainCards[$cardKey])) {
                    $errors[] = sprintf(
                        'Domain card "%s" selected multiple times (levels %d and %d)',
                        $cardKey,
                        $allDomainCards[$cardKey],
                        $level
                    );
                }
                $allDomainCards[$cardKey] = $level;
            }
        }
        
        // Check advancement-granted domain cards (creation_advancement_cards)
        foreach ($this->creation_advancement_cards as $advKey => $cardData) {
            if (isset($cardData['ability_key'])) {
                $cardKey = $cardData['ability_key'];
                if (isset($allDomainCards[$cardKey])) {
                    $errors[] = sprintf(
                        'Domain card "%s" selected multiple times (level %d and advancement)',
                        $cardKey,
                        $allDomainCards[$cardKey]
                    );
                }
                $allDomainCards[$cardKey] = 'advancement_' . $advKey;
            }
        }

        // Check total domain cards don't exceed max
        $maxCards = $this->getMaxDomainCards();
        $totalCards = count($allDomainCards);
        if ($totalCards > $maxCards) {
            $errors[] = sprintf(
                'Too many domain cards selected (%d selected, max %d allowed)',
                $totalCards,
                $maxCards
            );
        }

        // Check trait marks across tiers (marked traits can't be selected again in same tier)
        // Tier mapping: Tier 1 = Level 1, Tier 2 = Levels 2-4, Tier 3 = Levels 5-7, Tier 4 = Levels 8-10
        $getTier = function(int $level): int {
            if ($level === 1) return 1;
            if ($level <= 4) return 2;
            if ($level <= 7) return 3;
            return 4;
        };
        
        $traitMarksByTier = [1 => [], 2 => [], 3 => [], 4 => []];
        
        for ($level = 2; $level <= $this->starting_level; $level++) {
            $tier = $getTier($level);
            $levelAdvancements = $this->creation_advancements[$level] ?? [];

            foreach ($levelAdvancements as $advancement) {
                if (($advancement['type'] ?? '') === 'trait_bonus') {
                    $traits = $advancement['traits'] ?? [];
                    foreach ($traits as $trait) {
                        // Check if trait was already marked in this tier
                        if (in_array($trait, $traitMarksByTier[$tier])) {
                            $errors[] = sprintf(
                                'Trait "%s" marked multiple times in Tier %d (level %d)',
                                $trait,
                                $tier,
                                $level
                            );
                        }
                        $traitMarksByTier[$tier][] = $trait;
                    }
                }
            }

            // Clear marks at tier boundaries (levels 2, 5, 8 are the START of new tiers)
            // Note: Marks clear automatically at tier start due to the tier calculation
        }

        return $errors;
    }

    /**
     * Get advancement progress summary
     *
     * @return array Progress data with completed/total counts
     */
    public function getAdvancementProgress(): array
    {
        if ($this->starting_level === 1) {
            return [
                'levels_requiring_advancements' => 0,
                'levels_completed' => 0,
                'percentage' => 100,
                'is_complete' => true,
            ];
        }

        $levelsRequiring = $this->starting_level - 1; // levels 2 through starting_level
        $levelsCompleted = 0;

        for ($level = 2; $level <= $this->starting_level; $level++) {
            $hasExperience = true;
            if (in_array($level, [2, 5, 8])) {
                $hasExperience = isset($this->creation_tier_experiences[$level]) &&
                    ! empty($this->creation_tier_experiences[$level]['name']);
            }

            $hasDomainCard = isset($this->creation_domain_cards[$level]) &&
                ! empty($this->creation_domain_cards[$level]);

            $hasAdvancements = isset($this->creation_advancements[$level]) &&
                count($this->creation_advancements[$level]) === 2;

            if ($hasExperience && $hasDomainCard && $hasAdvancements) {
                $levelsCompleted++;
            }
        }

        return [
            'levels_requiring_advancements' => $levelsRequiring,
            'levels_completed' => $levelsCompleted,
            'percentage' => $levelsRequiring > 0 ? round(($levelsCompleted / $levelsRequiring) * 100, 1) : 100,
            'is_complete' => $levelsCompleted === $levelsRequiring,
        ];
    }

    /**
     * Calculate bonuses from all creation advancements
     *
     * @return array Bonuses keyed by stat type
     */
    public function calculateAdvancementBonuses(): array
    {
        $bonuses = [
            'evasion' => 0,
            'hit_points' => 0,
            'stress' => 0,
            'trait_bonuses' => [], // trait_name => bonus_count
            'experiences' => 0,
            'proficiency' => 0,
        ];

        // Count advancements by type
        foreach ($this->creation_advancements as $level => $advancements) {
            foreach ($advancements as $advancement) {
                $type = $advancement['type'] ?? '';

                match ($type) {
                    'hit_point' => $bonuses['hit_points']++,
                    'stress' => $bonuses['stress']++,
                    'evasion' => $bonuses['evasion']++,
                    'experience_bonus' => $bonuses['experiences']++,
                    'proficiency' => $bonuses['proficiency']++,
                    'trait_bonus' => $this->addTraitBonuses($bonuses, $advancement),
                    default => null,
                };
            }
        }

        return $bonuses;
    }

    /**
     * Helper to add trait bonuses from an advancement
     */
    private function addTraitBonuses(array &$bonuses, array $advancement): void
    {
        $traits = $advancement['traits'] ?? [];
        foreach ($traits as $trait) {
            if (! isset($bonuses['trait_bonuses'][$trait])) {
                $bonuses['trait_bonuses'][$trait] = 0;
            }
            $bonuses['trait_bonuses'][$trait]++;
        }
    }

    /**
     * Calculate the final character level after advancement selections
     *
     * @return int The final level (same as starting_level)
     */
    public function calculateFinalLevel(): int
    {
        return $this->starting_level;
    }

    /**
     * Get effective trait values including advancement bonuses
     *
     * @return array Trait values with bonuses applied
     */
    public function getEffectiveTraitValues(): array
    {
        $base_traits = $this->assigned_traits;
        $advancement_bonuses = $this->calculateAdvancementBonuses();

        $effective_traits = [];
        foreach ($base_traits as $trait => $value) {
            $bonus = $advancement_bonuses['trait_bonuses'][$trait] ?? 0;
            $effective_traits[$trait] = $value + $bonus;
        }

        return $effective_traits;
    }

    /**
     * Get computed character stats using CharacterStatsCalculator service
     */
    public function getComputedStats(array $class_data = []): array
    {
        if (empty($class_data)) {
            return [];
        }

        $calculator = app(\Domain\Character\Services\CharacterStatsCalculator::class);

        return $calculator->calculateStats(
            classData: $class_data,
            assignedTraits: $this->assigned_traits,
            selectedEquipment: $this->selected_equipment,
            ancestryKey: $this->selected_ancestry,
            subclassKey: $this->selected_subclass,
            startingLevel: $this->starting_level,
            advancementBonuses: $this->calculateAdvancementBonuses()
        );
    }

    /**
     * Get the maximum number of domain cards this character can have
     */
    public function getMaxDomainCards(): int
    {
        $subclass_service = app(\Domain\Character\Services\SubclassBonusService::class);

        // Base cards = character level (1 per level), plus any subclass bonuses
        return $subclass_service->getMaxDomainCards($this->selected_subclass, $this->starting_level);
    }

    /**
     * Check if the selected ancestry has experience bonus selection effect
     */
    public function hasExperienceBonusSelection(): bool
    {
        $ancestry_service = app(\Domain\Character\Services\AncestryBonusService::class);

        return $ancestry_service->hasExperienceBonusSelection($this->selected_ancestry ?? '');
    }

    /**
     * Get the experience that receives bonus from experience_bonus_selection effect
     */
    public function getClankBonusExperience(): ?string
    {
        if (! $this->hasExperienceBonusSelection()) {
            return null;
        }

        return $this->clank_bonus_experience;
    }

    /**
     * Get the modifier for a specific experience (including ancestry bonuses)
     */
    public function getExperienceModifier(string $experienceName): int
    {
        $ancestry_service = app(\Domain\Character\Services\AncestryBonusService::class);

        return $ancestry_service->getExperienceModifier(
            $experienceName,
            $this->selected_ancestry,
            $this->clank_bonus_experience
        );
    }

    /**
     * Get the profile image URL using signed URL for security
     * 
     * @return string Profile image URL (signed S3 URL or default avatar)
     */
    public function getProfileImage(): string
    {
        if ($this->profile_image_path) {
            $s3Disk = Storage::disk('s3');
            if ($s3Disk->exists($this->profile_image_path)) {
                // @phpstan-ignore-next-line temporaryUrl exists on S3 driver
                /** @var mixed $s3Disk */
                return $s3Disk->temporaryUrl(
                    $this->profile_image_path,
                    now()->addHours(24)
                );
            }
        }

        return asset('img/default-avatar.png');
    }
}
