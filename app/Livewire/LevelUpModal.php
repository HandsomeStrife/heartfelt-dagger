<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\ApplyAdvancementAction;
use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterAdvancementRepository;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\On;
use Livewire\Component;

class LevelUpModal extends Component
{
    public bool $show_modal = false;

    public ?string $character_key = null;

    public ?Character $character = null;

    public array $game_data = [];

    // Current step in level-up process
    public string $current_step = 'tier_achievements'; // tier_achievements, advancement_selection, confirmation

    // Advancement selection state
    public array $selected_advancements = [];

    public array $advancement_data = [];

    public int $current_tier = 1;

    public array $available_slots = [];

    public array $tier_options = [];

    // User input for specific advancement types
    public array $trait_selections = [];

    public array $experience_selections = [];

    public ?string $multiclass_selection = null;

    public ?string $domain_card_selection = null;

    // Repositories and Actions
    private CharacterAdvancementRepository $advancement_repository;

    private ApplyAdvancementAction $apply_advancement_action;

    public function mount(): void
    {
        $this->loadGameData();
    }

    #[On('open-level-up-modal')]
    public function openModal(string $characterKey): void
    {
        // Initialize repositories and actions here since they're needed when modal opens
        $this->advancement_repository = new CharacterAdvancementRepository;
        $this->apply_advancement_action = new ApplyAdvancementAction;

        $this->character_key = $characterKey;
        $this->loadCharacter();

        if ($this->character) {
            $this->initializeLevelUpData();
            $this->show_modal = true;
            $this->current_step = 'tier_achievements';
        }
    }

    public function closeModal(): void
    {
        $this->show_modal = false;
        $this->resetComponent();
    }

    public function loadCharacter(): void
    {
        if (! $this->character_key) {
            return;
        }

        $this->character = Character::where('character_key', $this->character_key)->first();
    }

    public function initializeLevelUpData(): void
    {
        if (! $this->character) {
            return;
        }

        $this->current_tier = $this->character->getTier();
        $this->available_slots = $this->advancement_repository->getAvailableSlots($this->character->id, $this->current_tier);

        // Load tier options from class data
        $this->loadTierOptions();

        // Reset selections
        $this->selected_advancements = [];
        $this->advancement_data = [];
        $this->trait_selections = [];
        $this->experience_selections = [];
        $this->multiclass_selection = null;
        $this->domain_card_selection = null;
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

    public function nextStep(): void
    {
        switch ($this->current_step) {
            case 'tier_achievements':
                $this->current_step = 'advancement_selection';
                break;
            case 'advancement_selection':
                if ($this->validateSelections()) {
                    $this->current_step = 'confirmation';
                }
                break;
        }
    }

    public function previousStep(): void
    {
        switch ($this->current_step) {
            case 'advancement_selection':
                $this->current_step = 'tier_achievements';
                break;
            case 'confirmation':
                $this->current_step = 'advancement_selection';
                break;
        }
    }

    public function selectAdvancement(int $option_index): void
    {
        if (count($this->selected_advancements) >= 2) {
            // Remove first selection if trying to add a third
            $this->selected_advancements = array_slice($this->selected_advancements, 1);
        }

        $this->selected_advancements[] = $option_index;
        $this->selected_advancements = array_unique($this->selected_advancements);
    }

    public function removeAdvancement(int $option_index): void
    {
        $this->selected_advancements = array_filter($this->selected_advancements, function ($selected) use ($option_index) {
            return $selected !== $option_index;
        });
        $this->selected_advancements = array_values($this->selected_advancements);
    }

    public function validateSelections(): bool
    {
        if (count($this->selected_advancements) !== 2) {
            session()->flash('error', 'You must select exactly 2 advancements.');

            return false;
        }

        // Additional validation logic would go here
        return true;
    }

    public function confirmLevelUp(): void
    {
        if (! $this->validateSelections() || ! $this->character) {
            return;
        }

        // Ensure repositories are initialized
        if (! isset($this->apply_advancement_action)) {
            $this->apply_advancement_action = new ApplyAdvancementAction;
        }

        try {
            // Apply each selected advancement
            foreach ($this->selected_advancements as $index => $option_index) {
                $advancement_number = $index + 1;
                $option = $this->tier_options['options'][$option_index];

                $advancement_data = $this->parseAdvancement($option, $advancement_number);
                $this->apply_advancement_action->execute($this->character, $advancement_data);
            }

            session()->flash('success', 'Character leveled up successfully!');
            $this->closeModal();
            $this->dispatch('character-updated');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to apply level up: '.$e->getMessage());
        }
    }

    private function parseAdvancement(array $option, int $advancement_number): CharacterAdvancementData
    {
        $description = $option['description'];

        // Match advancement types based on description patterns
        if (str_contains(strtolower($description), 'trait')) {
            // For now, use default traits - in full implementation would get from user input
            $traits = ['agility', 'strength'];

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
            return CharacterAdvancementData::experienceBonus($this->current_tier, $advancement_number);
        }

        if (str_contains($description, 'Evasion')) {
            return CharacterAdvancementData::evasion($this->current_tier, $advancement_number);
        }

        if (str_contains($description, 'domain card')) {
            $level = match ($this->current_tier) {
                2 => 2,
                3 => 3,
                default => 4,
            };

            return CharacterAdvancementData::domainCard($this->current_tier, $advancement_number, $level);
        }

        // Default fallback - create a generic advancement
        return new CharacterAdvancementData(
            tier: $this->current_tier,
            advancement_number: $advancement_number,
            advancement_type: 'generic',
            advancement_data: ['description' => $description],
            description: $description
        );
    }

    private function resetComponent(): void
    {
        $this->character_key = null;
        $this->character = null;
        $this->current_step = 'tier_achievements';
        $this->selected_advancements = [];
        $this->advancement_data = [];
        $this->current_tier = 1;
        $this->available_slots = [];
        $this->tier_options = [];
        $this->trait_selections = [];
        $this->experience_selections = [];
        $this->multiclass_selection = null;
        $this->domain_card_selection = null;
    }

    public function render()
    {
        return view('livewire.level-up-modal');
    }
}
