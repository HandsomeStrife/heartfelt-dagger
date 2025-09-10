<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\ClassEnum;
use Domain\Character\Models\Character;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Usernotnull\Toast\Concerns\WireToast;

class CharacterBuilder extends Component
{
    use WireToast, WithFileUploads;

    // State Properties
    public CharacterBuilderData $character;

    public array $completed_steps = [];

    public $profile_image;

    // Browser Storage Key
    public string $storage_key;

    // Direct pronouns field (now stored as database column)
    public ?string $pronouns = null;

    // Last saved timestamp for JavaScript tracking
    public ?int $last_saved_timestamp = null;

    // Game Data
    public array $game_data = [];

    // NOTE: Equipment category expansion state removed - UI state now handled client-side

    // NOTE: Experience form fields removed - experience editing now handled client-side

    // Character model for accessing methods like getProfileImage()
    protected ?Character $character_model = null;

    // NOTE: Computed property wrappers removed - use getComputedStats() and $character->getAncestryBonuses() directly

    public function getImageUrl(): ?string
    {
        // 1. If user just uploaded an image (temporary), show that
        if ($this->profile_image) {
            return $this->profile_image->temporaryUrl();
        }

        // 2. If character has a saved image, use the model's method
        if ($this->character_model && $this->character_model->profile_image_path) {
            return $this->character_model->getProfileImage();
        }

        // 3. No image exists
        return null;
    }

    /**
     * Refresh character data from database (e.g., after image upload)
     */
    public function refreshCharacter(): void
    {
        if ($this->storage_key) {
            $action = new LoadCharacterAction;
            $character_data = $action->execute($this->storage_key);

            if ($character_data) {
                $this->character = $character_data;
                $this->character_model = Character::where('character_key', $this->storage_key)->first();
            }
        }
    }

    /**
     * Select which experience gets experience_bonus_selection ancestry bonus
     */
    public function selectClankBonusExperience(string $experienceName): void
    {
        if ($this->character->hasExperienceBonusSelection()) {
            $this->character->clank_bonus_experience = $experienceName;
            $this->updateStateOnly();
        }
    }

    public function mount(?string $characterKey = null): void
    {
        // Character key is now required - should always be provided by controller
        if (! $characterKey) {
            abort(400, 'Character key is required');
        }

        // Load character from database
        $action = new LoadCharacterAction;
        $character_data = $action->execute($characterKey);

        if (! $character_data) {
            abort(404, 'Character not found');
        }

        $this->character = $character_data;
        $this->storage_key = $characterKey;

        // Load the character model to get pronouns and last saved time from database
        $this->character_model = Character::where('character_key', $characterKey)->first();
        $this->pronouns = $this->character_model->pronouns ?? null;
        $this->last_saved_timestamp = $this->character_model?->updated_at?->timestamp;

        $this->updateCompletedSteps();
        $this->loadGameData();
    }

    // NOTE: initializeCharacter() removed - characters are now always loaded from database

    public function loadGameData(): void
    {
        // Load JSON data files
        $json_files = [
            'classes',
            'ancestries',
            'communities',
            'subclasses',
            'domains',
            'abilities',
            'weapons',
            'armor',
            'items',
            'consumables',
        ];

        foreach ($json_files as $file) {
            $path = resource_path("json/{$file}.json");
            if (File::exists($path)) {
                $this->game_data[$file] = json_decode(File::get($path), true);
            }
        }

        // Process markdown in subclass feature descriptions
        if (isset($this->game_data['subclasses'])) {
            $this->processSubclassMarkdown();
        }
    }

    /**
     * Convert markdown formatting in subclass feature descriptions to HTML
     */
    private function processSubclassMarkdown(): void
    {
        foreach ($this->game_data['subclasses'] as $subclassKey => &$subclass) {
            // Process foundation features
            if (isset($subclass['foundationFeatures']) && is_array($subclass['foundationFeatures'])) {
                foreach ($subclass['foundationFeatures'] as &$feature) {
                    if (isset($feature['description']) && is_string($feature['description'])) {
                        $feature['description'] = Markdown::convert($feature['description'])->getContent();
                    }
                }
            }

            // Process specialization features
            if (isset($subclass['specializationFeatures']) && is_array($subclass['specializationFeatures'])) {
                foreach ($subclass['specializationFeatures'] as &$feature) {
                    if (isset($feature['description']) && is_string($feature['description'])) {
                        $feature['description'] = Markdown::convert($feature['description'])->getContent();
                    }
                }
            }

            // Process mastery features
            if (isset($subclass['masteryFeatures']) && is_array($subclass['masteryFeatures'])) {
                foreach ($subclass['masteryFeatures'] as &$feature) {
                    if (isset($feature['description']) && is_string($feature['description'])) {
                        $feature['description'] = Markdown::convert($feature['description'])->getContent();
                    }
                }
            }
        }
    }

    // NOTE: Character selection methods (selectClass, selectSubclass, selectAncestry, selectCommunity)
    // are now handled client-side in character-builder.js for instant UI updates.
    // Server-side sync happens via entangled properties and explicit save calls.

    // NOTE: Trait assignment methods (assignTrait, resetTraits) are now handled client-side
    // in character-builder.js via applySuggestedTraits() for instant UI updates.
    // Server-side sync happens via entangled properties.

    // NOTE: updateCharacterName() and updatePronouns() removed
    // Character name and pronouns now save only when user clicks Save button

    /**
     * Auto-save when character object properties change via live model binding
     * This catches any updates to the $character object properties like character.name
     */
    public function updatedCharacter($value, $key): void
    {
        // Properties that should trigger step completion updates but not auto-save
        $stepCompletionProperties = [
            'selected_class',
            'selected_subclass',
            'selected_ancestry',
            'selected_community',
            'assigned_traits',
            'selected_equipment',
            'experiences',
            'selected_domain_cards',
        ];

        // Auto-save for specific properties that should save immediately
        // NOTE: Most auto-save properties removed - only keep essential ones
        $autoSaveProperties = [
            // 'name' removed - now manual save only
            // 'background_answers' removed - now manual save only
            // 'physical_description' removed - now manual save only
            // 'personality_traits' removed - now manual save only
            // 'personal_history' removed - now manual save only
            // 'motivations' removed - now manual save only
        ];

        // Handle nested properties like background_answers.0
        $topLevelKey = explode('.', $key)[0];

        if (in_array($key, $autoSaveProperties) || in_array($topLevelKey, $autoSaveProperties)) {
            $this->saveToDatabase();
            $this->dispatch('character-updated', $this->character);
        } elseif (in_array($key, $stepCompletionProperties) || in_array($topLevelKey, $stepCompletionProperties)) {
            // Update step completion for selection properties without auto-saving
            $this->updateStateOnly();

            // Force re-render for step completion updates
            $this->dispatch('step-completion-updated');
        }
    }

    // NOTE: updatedPronouns() removed - pronouns now save only when user clicks Save button

    // NOTE: updatedProfileImage() removed - image uploads now handled via SimpleImageUploader and CharacterImageUploadController

    public function clearProfileImage(): void
    {
        // Delete existing file if it exists
        if ($this->character->profile_image_path) {
            Storage::disk('s3')->delete($this->character->profile_image_path);
        }

        // Clear both the uploaded file and stored path
        $this->profile_image = null;
        $this->character->profile_image_path = null;
        $this->saveToDatabase();

        // Refresh character model after clearing
        $this->character_model = Character::where('character_key', $this->storage_key)->first();

        $this->dispatch('character-updated', $this->character);
    }

    // NOTE: Equipment selection methods (selectEquipment, selectInventoryItem) are now handled
    // client-side in character-builder.js for instant UI updates and better UX.
    // Server-side sync happens via syncEquipment() method called from JavaScript.

    // NOTE: removeEquipment(), clearAllEquipment(), and toggleEquipmentCategory() methods removed
    // Equipment management and UI state now handled client-side for better performance and UX

    // NOTE: updateBackgroundAnswer() removed - background answers are now handled via
    // direct wire:model binding to character.background_answers array for real-time updates.

    // NOTE: markBackgroundComplete() removed - step completion now automatically calculated based on content

    public function addExperience(string $name, string $description = ''): void
    {
        if (empty(trim($name))) {
            return;
        }

        // Check experience limit (maximum 2 at character creation)
        if (count($this->character->experiences) >= 2) {
            return;
        }

        $this->character->experiences[] = [
            'name' => trim($name),
            'description' => trim($description),
            'modifier' => 2,
        ];

        $this->updateStateOnly();
        $this->dispatch('experience-added');
    }

    public function clearAllExperiences(): void
    {
        $this->character->experiences = [];
        $this->updateStateOnly();
    }

    public function removeExperience(int $index): void
    {
        unset($this->character->experiences[$index]);
        $this->character->experiences = array_values($this->character->experiences);
        $this->updateStateOnly();
    }

    // NOTE: Experience editing methods removed - experience editing now handled client-side for better UX

    // NOTE: selectDomainCard() method removed - domain card selection now handled client-side
    // JavaScript method: toggleDomainCard() provides instant feedback and better UX

    // NOTE: removeDomainCard() and clearAllDomainCards() methods removed - domain card management now handled client-side
    // JavaScript methods provide instant feedback and better UX

    // NOTE: updateConnectionAnswer() removed - connection answers are now handled via
    // direct wire:model binding to character.connection_answers array for real-time updates.

    // NOTE: applySuggestedTraits() method removed - trait application now handled client-side
    // JavaScript method: applySuggestedTraits() provides instant feedback and better UX

    // NOTE: applySuggestedEquipment() method removed - equipment application now handled client-side
    // JavaScript method: applySuggestedEquipment() provides instant feedback and better UX

    public function updateCompletedSteps(): void
    {
        $this->completed_steps = $this->character->getCompletedSteps();
    }

    /**
     * Helper method to update completion state and dispatch character-updated event (NO DATABASE SAVE)
     */
    private function updateStateOnly(): void
    {
        $this->updateCompletedSteps();
        $this->dispatch('character-updated', $this->character);
    }

    // NOTE: saveAndUpdateState() removed - redundant with saveToDatabase() which already
    // handles state updates and character-updated dispatching.

    public function saveToDatabase(): void
    {
        try {
            // Debug: Log that method was called
            error_log('SAVE TO DATABASE CALLED!');
            logger('=== SaveToDatabase Debug ===');
            logger('Character selected_equipment count: '.count($this->character->selected_equipment ?? []));
            foreach ($this->character->selected_equipment ?? [] as $eq) {
                logger("  - Equipment: {$eq['type']} - {$eq['key']}");
            }

            // Load the existing character from database
            $character = \Domain\Character\Models\Character::where('character_key', $this->storage_key)->firstOrFail();

            // Update it using the SaveCharacterAction
            $action = new SaveCharacterAction;
            $updated_character = $action->updateCharacter($character, $this->character, $this->pronouns);

            // Reload the character data to ensure consistency
            $load_action = new LoadCharacterAction;
            $this->character = $load_action->execute($this->storage_key);

            // Reload pronouns and update last saved time from database
            $this->character_model = Character::where('character_key', $this->storage_key)->first();
            $this->pronouns = $this->character_model->pronouns ?? null;

            // Update timestamp and dispatch JS event for real-time updates
            $this->last_saved_timestamp = time();
            $this->dispatch('character-saved-timestamp',
                timestamp: $this->last_saved_timestamp
            );

            // Dispatch character saved event for JavaScript state management
            $this->dispatch('character-saved');

            // Show success toast notification
            toast()
                ->success('Character saved successfully!')
                ->push();

        } catch (\Exception $e) {
            toast()
                ->danger('Failed to save character: '.$e->getMessage())
                ->push();
        }
    }

    // NOTE: saveCharacter() method could be consolidated with saveToDatabase() for consistency
    // Currently only used once in connection-creation.blade.php - consider using saveToDatabase() directly

    // NOTE: resetCharacter() removed - character reset is now handled client-side
    // for better performance and instant UI updates.

    // NOTE: getSuggestedEquipment() method removed - equipment suggestions now computed client-side
    // JavaScript computed properties: suggestedPrimaryWeapon, suggestedSecondaryWeapon, suggestedArmor

    // NOTE: getFilteredData() method removed - all filtering now handled client-side in character-builder.js
    // Data filtering for subclasses, domain cards, background questions, and connection questions
    // is now computed in real-time by JavaScript for better performance and instant UI updates

    // NOTE: getTabsData() could be moved to client-side or enum helper for better performance
    public function getTabsData(): array
    {
        $tabs = [];
        foreach (\Domain\Character\Enums\CharacterBuilderStep::getAllInOrder() as $step) {
            $tabs[$step->getStepNumber()] = $step->getDisplayName();
        }

        return $tabs;
    }

    // NOTE: getEquipmentProgress() removed - equipment progress now tracked client-side via AlpineJS computed properties

    // NOTE: Equipment suggestion methods removed - now handled client-side in character-builder.js
    // The following methods were moved to JavaScript for better performance:
    // - getSuggestedWeaponData() -> suggestedPrimaryWeapon, suggestedSecondaryWeapon computed properties
    // - getSuggestedArmorData() -> suggestedArmor computed property
    // - isWeaponSuggested() -> client-side filtering in tier1PrimaryWeapons, tier1SecondaryWeapons
    // - isArmorSuggested() -> client-side filtering in tier1Armor

    // NOTE: isInventoryItemSelected() method removed - inventory selection state now tracked client-side
    // JavaScript component has isInventoryItemSelected() method for real-time UI updates

    // NOTE: syncEquipment() removed - equipment now synced via entangled properties automatically

    // NOTE: getInventoryItemData() method removed - inventory data lookup now handled client-side
    // JavaScript component has direct access to gameData for item/consumable lookups

    // NOTE: getProcessedInventoryItems() method removed - inventory processing now handled client-side
    // Complex inventory selection (Choose One/Choose Extra) is handled in character-builder.js

    // NOTE: getConnectionProgress() method removed - connection progress now tracked client-side
    // JavaScript computed properties: totalConnections, answeredConnections, isConnectionComplete

    public function render()
    {
        return view('livewire.character-builder', [
            'game_data' => $this->game_data,
            'progress_percentage' => $this->character->getProgressPercentage(),
            'is_complete' => count($this->completed_steps) === count(\Domain\Character\Enums\CharacterBuilderStep::getAllInOrder()),
            'completed_steps' => $this->completed_steps,
            'tabs' => $this->getTabsData(),
            'computed_stats' => $this->getComputedStats(),
            'ancestry_bonuses' => $this->character->getAncestryBonuses(),
            'character_level' => $this->character_model?->level ?? 1,
            'last_saved_timestamp' => $this->last_saved_timestamp,
            'classes' => ClassEnum::cases(),
            // NOTE: Removed redundant data now handled client-side:
            // - filtered_data (subclasses, domain cards, questions computed in JS)
            // - connection_progress (connection tracking handled in JS)
        ]);
    }

    /**
     * Get computed character stats for display
     */
    public function getComputedStats(): array
    {
        if (! $this->character->selected_class || ! isset($this->game_data['classes'][$this->character->selected_class])) {
            return [];
        }

        $class_data = $this->game_data['classes'][$this->character->selected_class];

        return $this->character->getComputedStats($class_data);
    }
}
