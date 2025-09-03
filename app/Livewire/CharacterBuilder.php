<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\ClassEnum;
use Domain\Character\Models\Character;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Usernotnull\Toast\Concerns\WireToast;

class CharacterBuilder extends Component
{
    use WithFileUploads, WireToast;

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

    // Experience Form Fields
    public string $new_experience_name = '';

    public string $new_experience_description = '';

    // Experience editing fields
    public array $editing_experience = [];
    public string $edit_experience_description = '';

    // Character model for accessing methods like getProfileImage()
    protected ?Character $character_model = null;

    // Computed Properties for Tests
    public function getComputedStatsProperty(): array
    {
        return $this->getComputedStats();
    }

    public function getAncestryBonusesProperty(): array
    {
        return $this->character->getAncestryBonuses();
    }

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

    public function initializeCharacter(): void
    {
        $this->character = new CharacterBuilderData;
        $this->updateCompletedSteps();
    }

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
            'selected_domain_cards'
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

    public function updatedProfileImage(): void
    {
        $this->validate([
            'profile_image' => 'image|max:2048', // 2MB Max
        ]);

        if ($this->profile_image) {
            // Dispatch upload start event
            $this->dispatch('upload:start');
            
            try {
                // Generate organized path: year/month/day/character-token/image_name.extension
                $date = now();
                $year = $date->format('Y');
                $month = $date->format('m');
                $day = $date->format('d');

                // Get original filename and extension
                $original_name = $this->profile_image->getClientOriginalName();
                $extension = $this->profile_image->getClientOriginalExtension();
                $filename = pathinfo($original_name, PATHINFO_FILENAME);

                // Sanitize filename and add timestamp to avoid conflicts
                $sanitized_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
                $timestamp = $date->format('His');
                $final_filename = "{$sanitized_filename}_{$timestamp}.{$extension}";

                // Construct the directory path
                $directory = "character-portraits/{$year}/{$month}/{$day}/{$this->storage_key}";

                // Store the file to S3 (no ACL visibility - rely on bucket policy)
                $path = $this->profile_image->storeAs($directory, $final_filename, 's3');

                // Verify the file was actually uploaded
                if (!Storage::disk('s3')->exists($path)) {
                    throw new \Exception('File upload to S3 failed - file does not exist after upload');
                }

                $this->character->profile_image_path = $path;
                $this->saveToDatabase();
                
                // Refresh character model after uploading
                $this->character_model = Character::where('character_key', $this->storage_key)->first();
                
                $this->dispatch('character-updated', $this->character);
                $this->dispatch('image-uploaded', ['path' => $path]);
                $this->dispatch('upload:finish');
                
                // Use toast notification instead of old notify system
                toast()->success('Image uploaded successfully!')->push();

            } catch (\Exception $e) {
                $this->dispatch('upload:error');
                
                toast()->danger('Failed to upload image: ' . $e->getMessage())->push();
                
                Log::error('Image upload failed', [
                    'error' => $e->getMessage(),
                    'character_key' => $this->storage_key,
                    'filename' => $original_name ?? 'unknown'
                ]);
            }
        }
    }

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

    public function markBackgroundComplete(): void
    {
        // Mark background step as manually complete
        $this->character->markStepComplete(\Domain\Character\Enums\CharacterBuilderStep::BACKGROUND);
        $this->updateStateOnly();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Background section marked as complete!',
        ]);
    }

    public function addExperience(?string $name = null, ?string $description = null): void
    {
        $experience_name = $name ?? trim($this->new_experience_name);
        $experience_description = $description ?? trim($this->new_experience_description);

        if (empty($experience_name)) {
            return;
        }

        // Check experience limit (maximum 2 at character creation)
        if (count($this->character->experiences) >= 2) {
            return;
        }

        $this->character->experiences[] = [
            'name' => $experience_name,
            'description' => $experience_description,
            'modifier' => 2,
        ];

        // Clear form fields
        $this->new_experience_name = '';
        $this->new_experience_description = '';

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

    /**
     * Start editing an experience description
     */
    public function startEditingExperience(int $index): void
    {
        $experience = $this->character->experiences[$index] ?? null;
        if ($experience) {
            $this->editing_experience = ['index' => $index];
            $this->edit_experience_description = $experience['description'] ?? '';
        }
    }

    /**
     * Save the edited experience description
     */
    public function saveExperienceEdit(): void
    {
        $index = $this->editing_experience['index'] ?? null;
        if ($index !== null && isset($this->character->experiences[$index])) {
            $this->character->experiences[$index]['description'] = $this->edit_experience_description;
            $this->editing_experience = [];
            $this->edit_experience_description = '';
            $this->updateStateOnly();
        }
    }

    /**
     * Cancel editing experience description
     */
    public function cancelExperienceEdit(): void
    {
        $this->editing_experience = [];
        $this->edit_experience_description = '';
    }

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

    public function getEquipmentProgress(): array
    {
        $selectedPrimary = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'weapon' && ($eq['data']['type'] ?? 'Primary') === 'Primary');
        $selectedSecondary = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'weapon' && ($eq['data']['type'] ?? '') === 'Secondary');
        $selectedArmor = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'armor');
        
        // Basic implementation for backward compatibility until equipment template is fully converted
        $hasSelectedChooseOne = true;
        $hasSelectedChooseExtra = true; 
        $hasStartingInventory = false;

        return [
            'selectedPrimary' => $selectedPrimary,
            'selectedSecondary' => $selectedSecondary,
            'selectedArmor' => $selectedArmor,
            'hasSelectedChooseOne' => $hasSelectedChooseOne,
            'hasSelectedChooseExtra' => $hasSelectedChooseExtra,
            'hasStartingInventory' => $hasStartingInventory,
            'equipmentComplete' => $selectedPrimary && $selectedArmor,
        ];
    }

    // NOTE: Equipment suggestion methods removed - now handled client-side in character-builder.js
    // The following methods were moved to JavaScript for better performance:
    // - getSuggestedWeaponData() -> suggestedPrimaryWeapon, suggestedSecondaryWeapon computed properties
    // - getSuggestedArmorData() -> suggestedArmor computed property  
    // - isWeaponSuggested() -> client-side filtering in tier1PrimaryWeapons, tier1SecondaryWeapons
    // - isArmorSuggested() -> client-side filtering in tier1Armor

    // NOTE: isInventoryItemSelected() method removed - inventory selection state now tracked client-side
    // JavaScript component has isInventoryItemSelected() method for real-time UI updates

    public function syncEquipment(array $selected_equipment): void
    {
        // Update the character's selected equipment from Alpine.js state
        $this->character->selected_equipment = $selected_equipment;

        // Update completion state and save
        $this->updateStateOnly();
    }

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
            'equipment_progress' => $this->getEquipmentProgress(),
            'computed_stats' => $this->getComputedStats(),
            'ancestry_bonuses' => $this->character->getAncestryBonuses(),
            'character_level' => $this->character_model?->level ?? 1,
            'last_saved_timestamp' => $this->last_saved_timestamp,
            'classes' => ClassEnum::cases(),
            // NOTE: Removed redundant data now handled client-side:
            // - filtered_data (subclasses, domain cards, questions computed in JS)
            // - equipment_progress (equipment completion tracked in JS)  
            // - connection_progress (connection tracking handled in JS)
        ]);
    }

    /**
     * Get computed character stats for display
     */
    public function getComputedStats(): array
    {
        if (!$this->character->selected_class || !isset($this->game_data['classes'][$this->character->selected_class])) {
            return [];
        }

        $class_data = $this->game_data['classes'][$this->character->selected_class];
        return $this->character->getComputedStats($class_data);
    }
}
