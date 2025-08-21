<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Enums\CharacterBuilderStep;
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
            CharacterBuilderStep::CLASS_SELECTION => ! empty($this->selected_class) && ! empty($this->selected_subclass),
            CharacterBuilderStep::HERITAGE => ! empty($this->selected_ancestry) && ! empty($this->selected_community),
            CharacterBuilderStep::TRAITS => count($this->assigned_traits) === 6 && $this->validateTraitValues(),
            CharacterBuilderStep::EQUIPMENT => $this->isEquipmentComplete(),
            CharacterBuilderStep::BACKGROUND => $this->isBackgroundComplete(),
            CharacterBuilderStep::EXPERIENCES => count($this->experiences) >= 2,
            CharacterBuilderStep::DOMAIN_CARDS => count($this->selected_domain_cards) >= 2,
            CharacterBuilderStep::CONNECTIONS => count(array_filter($this->connection_answers, fn ($answer) => ! empty(trim($answer)))) >= 1,
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

                // Get level 1 abilities for this domain
                if (isset($domain['abilitiesByLevel']['1']['abilities'])) {
                    foreach ($domain['abilitiesByLevel']['1']['abilities'] as $abilityKey) {
                        if (isset($allAbilities[$abilityKey])) {
                            $domainAbilities[$abilityKey] = $allAbilities[$abilityKey];
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
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            character_key: $data['character_key'] ?? null,
            public_key: $data['public_key'] ?? null,
            name: $data['name'] ?? '',
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
}
