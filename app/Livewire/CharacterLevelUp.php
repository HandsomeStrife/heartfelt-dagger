<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\ApplyAdvancementAction;
use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterAdvancementRepository;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class CharacterLevelUp extends Component
{
    public string $character_key;

    public bool $can_edit;

    public ?Character $character = null;

    public array $game_data = [];

    // Current step in level-up process (now 4 steps)
    public string $current_step = 'tier_achievements'; // tier_achievements, first_advancement, second_advancement, confirmation

    // Advancement selection state (separate tracking for each advancement)
    public ?int $first_advancement = null;

    public ?int $second_advancement = null;

    public int $current_tier = 1;

    public array $available_slots = [];

    public array $tier_options = [];

    // User selections for specific advancement types
    public array $advancement_choices = [];

    // Experience creation for tier achievements
    public string $new_experience_name = '';

    public string $new_experience_description = '';

    // Repositories and Actions
    private CharacterAdvancementRepository $advancement_repository;

    private ApplyAdvancementAction $apply_advancement_action;

    public function mount(string $characterKey, bool $canEdit): void
    {
        $this->character_key = $characterKey;
        $this->can_edit = $canEdit;

        $this->advancement_repository = new CharacterAdvancementRepository;
        $this->apply_advancement_action = new ApplyAdvancementAction;

        $this->loadCharacter();
        $this->loadGameData();
        $this->initializeLevelUpData();
    }

    public function loadCharacter(): void
    {
        $this->character = Character::where('character_key', $this->character_key)->firstOrFail();
    }

    public function initializeLevelUpData(): void
    {
        if (! $this->character) {
            return;
        }

        // Get the target tier for leveling up (what tier we're advancing into)
        $target_level = $this->character->level + 1;
        $this->current_tier = match (true) {
            $target_level >= 8 => 4,
            $target_level >= 5 => 3,
            $target_level >= 2 => 2,
            default => 1,
        };
        $this->available_slots = $this->advancement_repository->getAvailableSlots($this->character->id, $this->current_tier);

        // Load tier options from class data
        $this->loadTierOptions();

        // Reset selections
        $this->first_advancement = null;
        $this->second_advancement = null;
        $this->advancement_choices = [
            'tier_experience' => null,
            'tier_domain_card' => null,
        ];
    }

    public function loadTierOptions(): void
    {
        if (! $this->character || ! isset($this->game_data['classes'][$this->character->class])) {
            $this->tier_options = [];

            return;
        }

        $class_data = $this->game_data['classes'][$this->character->class];
        $tier_key = "tier{$this->current_tier}";

        if (isset($class_data['tierOptions'][$tier_key])) {
            $this->tier_options = $class_data['tierOptions'][$tier_key];
        } else {
            $this->tier_options = [];
        }
    }

    public function loadGameData(): void
    {
        $json_files = ['classes', 'subclasses', 'domains', 'abilities'];

        foreach ($json_files as $file) {
            $path = resource_path("json/{$file}.json");
            if (File::exists($path)) {
                $this->game_data[$file] = json_decode(File::get($path), true);
            }
        }
    }

    // Step navigation validation methods
    public function validateTierAchievements(): bool
    {
        if (! $this->character) {
            return false;
        }

        $target_level = $this->character->level + 1;

        // Only levels 2, 5, 8 have tier achievement requirements
        if (! in_array($target_level, [2, 5, 8])) {
            return true; // No requirements for other levels
        }

        $hasExperience = isset($this->advancement_choices['tier_experience']) &&
                        ! empty($this->advancement_choices['tier_experience']);

        $hasDomainCard = isset($this->advancement_choices['tier_domain_card']) &&
                        ! empty($this->advancement_choices['tier_domain_card']);

        // Check if experience is created for tier achievement levels
        if (! $hasExperience) {
            session()->flash('error', 'Please create your tier achievement experience before proceeding.');

            return false;
        }

        // Check if tier domain card is selected for tier achievement levels
        if (! $hasDomainCard) {
            session()->flash('error', 'Please select your tier achievement domain card before proceeding.');

            return false;
        }

        return true;
    }

    public function nextStep(): void
    {
        // Note: This is now handled client-side, keeping for compatibility
        switch ($this->current_step) {
            case 'tier_achievements':
                $this->current_step = 'first_advancement';
                break;
            case 'first_advancement':
                if ($this->first_advancement !== null) {
                    $this->current_step = 'second_advancement';
                }
                break;
            case 'second_advancement':
                if ($this->second_advancement !== null) {
                    $this->current_step = 'confirmation';
                }
                break;
        }
    }

    public function previousStep(): void
    {
        // Note: This is now handled client-side, keeping for compatibility
        switch ($this->current_step) {
            case 'first_advancement':
                $this->current_step = 'tier_achievements';
                break;
            case 'second_advancement':
                $this->current_step = 'first_advancement';
                break;
            case 'confirmation':
                $this->current_step = 'second_advancement';
                break;
        }
    }

    public function selectAdvancement(int $option_index): void
    {
        // Note: This is now handled client-side, keeping for compatibility
        if (! $this->can_edit) {
            return;
        }

        // Check if already selected - if so, deselect it
        if (in_array($option_index, $this->selected_advancements)) {
            $this->removeAdvancement($option_index);

            return;
        }

        // Limit to available slots
        if (count($this->selected_advancements) >= count($this->available_slots)) {
            session()->flash('error', 'You have already selected the maximum number of advancements.');

            return;
        }

        $this->selected_advancements[] = $option_index;

        // Initialize choices for this advancement if needed
        if (! isset($this->advancement_choices[$option_index])) {
            $this->advancement_choices[$option_index] = [];
        }
    }

    public function removeAdvancement(int $option_index): void
    {
        if (! $this->can_edit) {
            return;
        }

        if ($this->first_advancement === $option_index) {
            $this->first_advancement = null;
        }
        if ($this->second_advancement === $option_index) {
            $this->second_advancement = null;
        }

        // Remove choices for this advancement
        unset($this->advancement_choices[$option_index]);
    }

    public function selectFirstAdvancement(int $option_index): void
    {
        if (! $this->can_edit) {
            return;
        }

        $this->first_advancement = $option_index;

        // Initialize choices for this advancement if needed
        if (! isset($this->advancement_choices[$option_index])) {
            $this->advancement_choices[$option_index] = [];
        }
    }

    public function selectSecondAdvancement(int $option_index): void
    {
        if (! $this->can_edit) {
            return;
        }

        $this->second_advancement = $option_index;

        // Initialize choices for this advancement if needed
        if (! isset($this->advancement_choices[$option_index])) {
            $this->advancement_choices[$option_index] = [];
        }
    }

    public function updateAdvancementChoice(int $option_index, string $field, $value): void
    {
        if (! $this->can_edit) {
            return;
        }

        if (! isset($this->advancement_choices[$option_index])) {
            $this->advancement_choices[$option_index] = [];
        }

        $this->advancement_choices[$option_index][$field] = $value;
    }

    public function addTierExperience(): void
    {
        if (! $this->can_edit) {
            return;
        }

        $name = trim($this->new_experience_name);
        $description = trim($this->new_experience_description);

        // Validate experience name
        if (empty($name)) {
            session()->flash('error', 'Experience name is required.');

            return;
        }

        if (strlen($name) > 100) {
            session()->flash('error', 'Experience name must be 100 characters or less.');

            return;
        }

        // Sanitize input to prevent XSS and other issues
        $name = strip_tags($name);
        $description = strip_tags($description);

        // Remove dangerous characters
        $name = preg_replace('/[<>"\']/', '', $name);
        $description = preg_replace('/[<>"\']/', '', $description);

        // Store the experience data in advancement_choices for later processing
        if (! isset($this->advancement_choices['tier_experience'])) {
            $this->advancement_choices['tier_experience'] = [];
        }

        $this->advancement_choices['tier_experience'] = [
            'name' => $name,
            'description' => $description,
            'modifier' => 2,
        ];

        // Clear form fields
        $this->new_experience_name = '';
        $this->new_experience_description = '';

        session()->flash('success', 'Experience created! It will be added when you complete the level up.');
    }

    public function removeTierExperience(): void
    {
        if (! $this->can_edit) {
            return;
        }

        unset($this->advancement_choices['tier_experience']);
        session()->flash('info', 'Experience removed.');
    }

    private function applyTierAchievements(): void
    {
        $target_level = $this->character->level + 1;

        // Apply tier achievements based on target level
        if (in_array($target_level, [2, 5, 8])) {
            // Create tier experience if provided
            if (isset($this->advancement_choices['tier_experience'])) {
                $experience_data = $this->advancement_choices['tier_experience'];

                \Domain\Character\Models\CharacterExperience::create([
                    'character_id' => $this->character->id,
                    'experience_name' => $experience_data['name'],
                    'experience_description' => $experience_data['description'],
                    'modifier' => $experience_data['modifier'],
                ]);
            }

            // Apply proficiency bonus (automatic for all tier achievements)
            \Domain\Character\Models\CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => $this->current_tier,
                'advancement_number' => 0, // 0 for tier achievements
                'advancement_type' => 'proficiency',
                'advancement_data' => ['bonus' => 1],
                'description' => 'Tier achievement: +1 Proficiency bonus',
            ]);

            // Clear marked traits for levels 5 and 8 (tier 3 and 4 entry)
            if (in_array($target_level, [5, 8])) {
                $this->character->traits()->update(['is_marked' => false]);
            }

            // Update character level
            $this->character->update(['level' => $target_level]);
        }

        // Apply domain card selection (required for ALL levels)
        if (isset($this->advancement_choices['tier_domain_card'])) {
            $this->createDomainCard($this->advancement_choices['tier_domain_card']);
        }
    }

    public function validateSelections(): bool
    {
        $required_selections = count($this->available_slots);
        $selected_advancements = array_filter([$this->first_advancement, $this->second_advancement], fn ($x) => $x !== null);

        if (count($selected_advancements) !== $required_selections) {
            session()->flash('error', "You must select exactly {$required_selections} advancement(s).");

            return false;
        }

        // Validate that required choices are made
        foreach ($selected_advancements as $option_index) {
            $option = $this->tier_options['options'][$option_index];
            $description = $option['description'];

            // Check if this advancement requires user choices
            if ($this->advancementRequiresChoices($description)) {
                if (! $this->hasRequiredChoices($option_index, $description)) {
                    session()->flash('error', 'Please complete all required selections before proceeding.');

                    return false;
                }
            }
        }

        return true;
    }

    private function advancementRequiresChoices(string $description): bool
    {
        return str_contains(strtolower($description), 'trait') ||
               str_contains(strtolower($description), 'multiclass') ||
               str_contains(strtolower($description), 'domain card') ||
               str_contains(strtolower($description), 'experience');
    }

    private function hasRequiredChoices(int $option_index, string $description): bool
    {
        $choices = $this->advancement_choices[$option_index] ?? [];

        if (str_contains(strtolower($description), 'trait')) {
            return isset($choices['traits']) &&
                   is_array($choices['traits']) &&
                   count($choices['traits']) === 2;
        }

        if (str_contains(strtolower($description), 'multiclass')) {
            return isset($choices['class']) && ! empty($choices['class']);
        }

        if (str_contains(strtolower($description), 'domain card')) {
            return isset($choices['domain_card']) && ! empty($choices['domain_card']);
        }

        if (str_contains(strtolower($description), 'experience')) {
            return isset($choices['experiences']) && count($choices['experiences']) === 2;
        }

        return true;
    }

    public function confirmLevelUp(): void
    {
        if (! $this->validateSelections() || ! $this->character || ! $this->can_edit) {
            return;
        }

        try {
            // Ensure repositories and actions are initialized
            if (! isset($this->apply_advancement_action)) {
                $this->apply_advancement_action = new ApplyAdvancementAction;
            }

            // Apply tier achievements first (e.g., experience creation)
            $this->applyTierAchievements();

            // Apply each selected advancement
            $selected_advancements = array_filter([$this->first_advancement, $this->second_advancement], fn ($x) => $x !== null);
            foreach ($selected_advancements as $index => $option_index) {
                $advancement_number = $index + 1;
                $option = $this->tier_options['options'][$option_index];
                $choices = $this->advancement_choices[$option_index] ?? [];

                $advancement_data = $this->parseAdvancement($option, $advancement_number, $choices);
                $this->apply_advancement_action->execute($this->character, $advancement_data);
            }

            session()->flash('success', 'Character leveled up successfully!');

            // Redirect back to character viewer
            $this->redirect(route('character.show', [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character_key,
            ]));

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to apply level up: '.$e->getMessage());
        }
    }

    private function parseAdvancement(array $option, int $advancement_number, array $choices): CharacterAdvancementData
    {
        $description = $option['description'];

        // Match advancement types based on description patterns
        if (str_contains(strtolower($description), 'trait')) {
            $traits = $choices['traits'] ?? ['agility', 'strength']; // fallback

            return CharacterAdvancementData::traitBonus(
                $this->current_tier,
                $advancement_number,
                $traits
            );
        }

        if (str_contains($description, 'Hit Point')) {
            return CharacterAdvancementData::hitPoint($this->current_tier, $advancement_number);
        }

        if (str_contains($description, 'Stress')) {
            return CharacterAdvancementData::stress($this->current_tier, $advancement_number);
        }

        if (str_contains($description, 'Experience')) {
            // Check if this is the "bonus to experiences" advancement
            if (str_contains($description, 'bonus to') && str_contains($description, 'Experiences')) {
                $selectedExperiences = $choices['experience_bonuses'] ?? [];
                if (count($selectedExperiences) !== 2) {
                    throw new \InvalidArgumentException('Experience bonus advancement requires exactly 2 experiences to be selected');
                }

                return new CharacterAdvancementData(
                    tier: $this->current_tier,
                    advancement_number: $advancement_number,
                    advancement_type: 'experience_bonus',
                    advancement_data: ['experiences' => $selectedExperiences],
                    description: $description
                );
            }

            // Otherwise, this is the regular experience bonus advancement
            return CharacterAdvancementData::experienceBonus($this->current_tier, $advancement_number);
        }

        if (str_contains($description, 'Evasion')) {
            return CharacterAdvancementData::evasion($this->current_tier, $advancement_number);
        }

        if (str_contains($description, 'domain card')) {
            $selectedCard = $choices['domain_card'] ?? null;
            if (! $selectedCard) {
                throw new \InvalidArgumentException('Domain card selection is required for this advancement');
            }

            // Get the level of the selected card for validation
            $abilities = $this->game_data['abilities'] ?? [];
            $cardLevel = $abilities[$selectedCard]['level'] ?? 1;

            return CharacterAdvancementData::domainCard($this->current_tier, $advancement_number, $cardLevel);
        }

        if (str_contains($description, 'Multiclass')) {
            $class_key = $choices['class'] ?? 'warrior'; // fallback

            return CharacterAdvancementData::multiclass($this->current_tier, $advancement_number, $class_key);
        }

        // Default fallback
        return new CharacterAdvancementData(
            tier: $this->current_tier,
            advancement_number: $advancement_number,
            advancement_type: 'generic',
            advancement_data: ['description' => $description],
            description: $description
        );
    }

    public function getAllCharacterExperiences()
    {
        if (! $this->character) {
            return collect();
        }

        $experiences = collect($this->character->experiences ?? []);

        // Add the pending tier achievement experience if it exists
        if (isset($this->advancement_choices['tier_experience']) &&
            is_array($this->advancement_choices['tier_experience']) &&
            ! empty($this->advancement_choices['tier_experience']['name'])) {

            $tierExperience = (object) [
                'name' => $this->advancement_choices['tier_experience']['name'],
                'description' => $this->advancement_choices['tier_experience']['description'],
                'modifier' => $this->advancement_choices['tier_experience']['modifier'],
                'is_pending' => true, // Flag to indicate this is not yet saved
            ];

            $experiences->push($tierExperience);
        }

        return $experiences;
    }

    public function getAvailableAdvancementsForStep(string $step): array
    {
        if (! isset($this->tier_options['options'])) {
            return [];
        }

        $allOptions = $this->tier_options['options'];
        $selectedAdvancements = array_filter([$this->first_advancement, $this->second_advancement], fn ($x) => $x !== null);

        $availableOptions = [];

        foreach ($allOptions as $index => $option) {
            // If this advancement is already selected, check if it can be selected multiple times
            if (in_array($index, $selectedAdvancements)) {
                // Count how many times this advancement has been selected
                $selectionCount = count(array_filter($selectedAdvancements, fn ($selected) => $selected === $index));

                // Check if this advancement allows multiple selections
                $hasMultipleSlots = isset($option['slots']) && $option['slots'] > 1;
                $maxSelections = $hasMultipleSlots ? $option['slots'] : 1;

                // Don't show if already at max selections
                if ($selectionCount >= $maxSelections) {
                    continue;
                }
            }

            // Preserve original index as key
            $availableOptions[$index] = $option;
        }

        return $availableOptions;
    }

    public function getAvailableDomainCards(int $maxLevel = 4): array
    {
        if (! $this->character) {
            return [];
        }

        // Get character's available domains based on class
        $classData = $this->game_data['classes'][$this->character->class] ?? null;
        if (! $classData) {
            return [];
        }

        $availableDomains = $classData['domains'] ?? [];
        $abilities = $this->game_data['abilities'] ?? [];

        $domainCards = [];
        foreach ($abilities as $abilityKey => $abilityData) {
            if (in_array($abilityData['domain'] ?? '', $availableDomains) &&
                ($abilityData['level'] ?? 1) <= $maxLevel) {
                $domainCards[] = [
                    'key' => $abilityKey,
                    'name' => $abilityData['name'] ?? ucwords(str_replace('-', ' ', $abilityKey)),
                    'domain' => $abilityData['domain'] ?? '',
                    'level' => $abilityData['level'] ?? 1,
                    'type' => $abilityData['type'] ?? 'Ability',
                    'recall_cost' => $abilityData['recallCost'] ?? 0,
                    'descriptions' => $abilityData['descriptions'] ?? [],
                ];
            }
        }

        return $domainCards;
    }

    public function selectDomainCard(int $advancementIndex, string $abilityKey): void
    {
        if (! $this->can_edit) {
            return;
        }

        // Initialize domain card choices if not exists
        if (! isset($this->advancement_choices[$advancementIndex]['domain_card'])) {
            $this->advancement_choices[$advancementIndex]['domain_card'] = null;
        }

        // Set the selected domain card
        $this->advancement_choices[$advancementIndex]['domain_card'] = $abilityKey;
    }

    public function removeDomainCard(int $advancementIndex): void
    {
        if (! $this->can_edit) {
            return;
        }

        unset($this->advancement_choices[$advancementIndex]['domain_card']);
    }

    public function selectExperienceBonus(int $advancementIndex, string $experienceName): void
    {
        if (! $this->can_edit) {
            return;
        }

        // Initialize experience bonus choices if not exists
        if (! isset($this->advancement_choices[$advancementIndex]['experience_bonuses'])) {
            $this->advancement_choices[$advancementIndex]['experience_bonuses'] = [];
        }

        $experiences = $this->advancement_choices[$advancementIndex]['experience_bonuses'];

        // Toggle selection (max 2)
        if (in_array($experienceName, $experiences)) {
            // Remove if already selected
            $this->advancement_choices[$advancementIndex]['experience_bonuses'] = array_values(
                array_filter($experiences, fn ($exp) => $exp !== $experienceName)
            );
        } elseif (count($experiences) < 2) {
            // Add if under limit
            $this->advancement_choices[$advancementIndex]['experience_bonuses'][] = $experienceName;
        }
    }

    public function removeExperienceBonus(int $advancementIndex): void
    {
        if (! $this->can_edit) {
            return;
        }

        unset($this->advancement_choices[$advancementIndex]['experience_bonuses']);
    }

    public function selectTierDomainCard(string $abilityKey): void
    {
        if (! $this->can_edit) {
            return;
        }

        // Set the selected tier domain card
        $this->advancement_choices['tier_domain_card'] = $abilityKey;
    }

    public function removeTierDomainCard(): void
    {
        if (! $this->can_edit) {
            return;
        }

        unset($this->advancement_choices['tier_domain_card']);
    }

    /**
     * Create a domain card record in the database
     */
    private function createDomainCard(string $abilityKey): void
    {
        $abilities = $this->game_data['abilities'] ?? [];
        $abilityData = $abilities[$abilityKey] ?? null;

        if (! $abilityData) {
            throw new \InvalidArgumentException("Invalid ability key: {$abilityKey}");
        }

        \Domain\Character\Models\CharacterDomainCard::create([
            'character_id' => $this->character->id,
            'ability_key' => $abilityKey,
            'domain' => $abilityData['domain'] ?? '',
            'level' => $abilityData['level'] ?? 1,
            'card_data' => [
                'name' => $abilityData['name'] ?? '',
                'type' => $abilityData['type'] ?? '',
                'recall_cost' => $abilityData['recallCost'] ?? 0,
                'descriptions' => $abilityData['descriptions'] ?? [],
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.character-level-up');
    }
}
