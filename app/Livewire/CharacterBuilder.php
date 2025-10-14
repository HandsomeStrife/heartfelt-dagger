<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\ClassEnum;
use Domain\Character\Models\Character;
use Domain\Character\Services\AdvancementOptionsService;
use Domain\Character\Services\AdvancementValidationService;
use Domain\Character\Services\DomainCardService;
use Domain\Character\Services\TierAchievementService;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Usernotnull\Toast\Concerns\WireToast;

/**
 * CharacterBuilder Component
 * 
 * Manages the interactive character creation flow for DaggerHeart TTRPG characters,
 * including support for creating characters at higher levels (1-10).
 * 
 * Architecture:
 * - Uses CharacterBuilderData DTO for all character state management
 * - Injects domain services for advancement logic, validation, tier achievements
 * - Client-side state handled by character-builder.js for instant UI updates
 * - Server-side persistence via SaveCharacterAction with database transactions
 * 
 * Client-Side Migration:
 * Many methods previously on this component have been moved to character-builder.js
 * for better performance and instant UI feedback:
 * - Character selection (class, subclass, ancestry, community)
 * - Trait assignment and suggested trait application
 * - Equipment selection and management
 * - Domain card selection
 * - Experience and inventory management
 * - Background and connection answer tracking
 * 
 * Server-side sync occurs via:
 * - Entangled properties for automatic state sync
 * - Explicit saveToDatabase() calls for persistence
 * - Real-time validation via injected services
 * 
 * Higher-Level Character Creation:
 * - Users can select starting level (1-10)
 * - Level-by-level advancement selections (tier achievements, advancements, domain cards)
 * - All SRD-compliant validation enforced
 * - Complete transaction safety with rollback on errors
 */
class CharacterBuilder extends Component
{
    use WireToast, WithFileUploads;

    // Domain Services
    protected AdvancementOptionsService $advancement_options_service;

    protected TierAchievementService $tier_achievement_service;

    protected DomainCardService $domain_card_service;

    protected AdvancementValidationService $advancement_validation_service;

    protected \Domain\Character\Services\AncestryBonusService $ancestry_bonus_service;

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

    // Character model for accessing methods like getProfileImage()
    protected ?Character $character_model = null;

    // Higher-level character creation properties
    public int $current_advancement_level = 1;

    public string $advancement_step = 'tier_achievements'; // tier_achievements, advancements, domain_card

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

    /**
     * Re-inject services after Livewire hydration (deserialization)
     * This is necessary because service instances can't be serialized
     */
    public function hydrate(): void
    {
        $this->advancement_options_service = app(AdvancementOptionsService::class);
        $this->tier_achievement_service = app(TierAchievementService::class);
        $this->domain_card_service = app(DomainCardService::class);
        $this->advancement_validation_service = app(AdvancementValidationService::class);
        $this->ancestry_bonus_service = app(\Domain\Character\Services\AncestryBonusService::class);
    }

    public function mount(?string $characterKey = null): void
    {
        // Character key is now required - should always be provided by controller
        if (! $characterKey) {
            abort(400, 'Character key is required');
        }

        // Inject services (also done in hydrate for Livewire updates)
        $this->hydrate();

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

        // Initialize advancement level tracking
        $this->current_advancement_level = $this->character->starting_level > 1 ? 2 : 1;

        $this->updateCompletedSteps();
        $this->loadGameData();
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

        // Auto-save for essential properties that should save immediately
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


    // JavaScript method: toggleDomainCard() provides instant feedback and better UX

    // JavaScript methods provide instant feedback and better UX

    // direct wire:model binding to character.connection_answers array for real-time updates.

    // JavaScript method: applySuggestedTraits() provides instant feedback and better UX

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

    // Currently only used once in connection-creation.blade.php - consider using saveToDatabase() directly

    // for better performance and instant UI updates.

    // JavaScript computed properties: suggestedPrimaryWeapon, suggestedSecondaryWeapon, suggestedArmor

    // Data filtering for subclasses, domain cards, background questions, and connection questions
    // is now computed in real-time by JavaScript for better performance and instant UI updates

    public function getTabsData(): array
    {
        $tabs = [];
        foreach (\Domain\Character\Enums\CharacterBuilderStep::getAllInOrder() as $step) {
            $tabs[$step->getStepNumber()] = $step->getDisplayName();
        }

        return $tabs;
    }


    // The following methods were moved to JavaScript for better performance:
    // - getSuggestedWeaponData() -> suggestedPrimaryWeapon, suggestedSecondaryWeapon computed properties
    // - getSuggestedArmorData() -> suggestedArmor computed property
    // - isWeaponSuggested() -> client-side filtering in tier1PrimaryWeapons, tier1SecondaryWeapons
    // - isArmorSuggested() -> client-side filtering in tier1Armor

    // JavaScript component has isInventoryItemSelected() method for real-time UI updates


    // JavaScript component has direct access to gameData for item/consumable lookups

    // Complex inventory selection (Choose One/Choose Extra) is handled in character-builder.js

    // JavaScript computed properties: totalConnections, answeredConnections, isConnectionComplete

    /**
     * Get marked traits for the current tier
     * 
     * Traits that have been increased via trait_bonus advancements in the current tier
     * cannot be selected again until the next tier. Marked traits are cleared at
     * tier transitions (levels 5 and 8 per SRD rules).
     * 
     * @param int $current_level The level being configured
     * @return array<string> Array of trait keys that are marked and cannot be selected
     */
    public function getMarkedTraitsForLevel(int $current_level): array
    {
        $marked_traits = [];
        
        // Determine which tier we're in
        $current_tier = match(true) {
            $current_level >= 1 && $current_level <= 3 => 1,
            $current_level >= 4 && $current_level <= 6 => 2,
            $current_level >= 7 && $current_level <= 9 => 3,
            $current_level >= 10 => 4,
            default => 1,
        };
        
        // Find the start of the current tier
        $tier_start_level = match($current_tier) {
            1 => 1,
            2 => 4,
            3 => 7,
            4 => 10,
            default => 1,
        };
        
        // Collect all trait_bonus advancements from the start of this tier up to (but not including) current level
        for ($level = $tier_start_level; $level < $current_level; $level++) {
            $level_advancements = $this->character->creation_advancements[$level] ?? [];
            
            foreach ($level_advancements as $advancement) {
                if (isset($advancement['type']) && $advancement['type'] === 'trait_bonus') {
                    $traits = $advancement['traits'] ?? [];
                    $marked_traits = array_merge($marked_traits, $traits);
                }
            }
        }
        
        // Return unique trait keys
        return array_values(array_unique($marked_traits));
    }

    public function render()
    {
        return view('livewire.character-builder', [
            'game_data' => $this->game_data,
            'progress_percentage' => $this->character->getProgressPercentage(),
            'is_complete' => count($this->completed_steps) === count(\Domain\Character\Enums\CharacterBuilderStep::getAllInOrder()),
            'completed_steps' => $this->completed_steps,
            'tabs' => $this->getTabsData(),
            'computed_stats' => $this->getComputedStats(),
            'ancestry_bonuses' => $this->ancestry_bonus_service->getAncestryBonuses(
                $this->character->selected_ancestry,
                $this->character->starting_level
            ),
            'character_level' => $this->character_model?->level ?? 1,
            'last_saved_timestamp' => $this->last_saved_timestamp,
            'classes' => ClassEnum::cases(),
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

    // ========================================
    // Higher-Level Character Creation Methods
    // ========================================

    /**
     * Select the starting level for character creation
     */
    public function selectStartingLevel(int $level): void
    {
        if ($level < 1 || $level > 10) {
            $this->toast()->danger('Invalid level. Must be between 1 and 10.')->send();

            return;
        }

        $this->character->starting_level = $level;

        // Reset advancement tracking if changing level
        $this->character->creation_advancements = [];
        $this->character->creation_tier_experiences = [];
        $this->character->creation_domain_cards = [];

        // Reset current level tracking
        $this->current_advancement_level = $level > 1 ? 2 : 1;
        $this->advancement_step = 'tier_achievements';

        $this->updateStateOnly();
        $this->dispatch('starting-level-changed', $level);
    }

    /**
     * Get array of levels that require advancement selections (2 through starting_level)
     */
    public function getLevelsRequiringAdvancements(): array
    {
        $levels = [];
        for ($level = 2; $level <= $this->character->starting_level; $level++) {
            $levels[] = $level;
        }

        return $levels;
    }

    /**
     * Mark the background step as complete
     */
    public function markBackgroundComplete(): void
    {
        $step_number = \Domain\Character\Enums\CharacterBuilderStep::BACKGROUND->getStepNumber();
        
        if (! in_array($step_number, $this->completed_steps)) {
            $this->completed_steps[] = $step_number;
            toast()
                ->success('Background step marked as complete!')
                ->push();
        }
    }

    /**
     * Complete advancement selections and validate before moving to next level
     */
    public function completeAdvancementSelections(): void
    {
        // Validate current level is complete
        $errors = $this->character->validateAdvancementSelections();

        if (! empty($errors)) {
            $this->toast()->danger('Please complete all required selections before continuing.')->send();
            $this->dispatch('advancement-validation-errors', $errors);

            return;
        }

        // Move to next level or finish
        if ($this->current_advancement_level < $this->character->starting_level) {
            $this->current_advancement_level++;
            $this->advancement_step = 'tier_achievements';
            $this->dispatch('advancement-level-changed', $this->current_advancement_level);
        } else {
            // All levels complete
            $this->dispatch('advancements-complete');
            $this->toast()->success('All advancements complete!')->send();
        }
    }

    /**
     * Get available advancement options for current level
     */
    public function getAvailableAdvancementOptions(): array
    {
        if (! $this->character->selected_class) {
            return [];
        }

        // Use existing character model, or create temporary instance for unsaved characters
        $characterForService = $this->character_model ?? $this->createTemporaryCharacterInstance();

        return $this->advancement_options_service->getAvailableOptions(
            $characterForService,
            $this->current_advancement_level
        );
    }

    /**
     * Get available domain cards for current level
     */
    public function getAvailableDomainCards(): array
    {
        if (! $this->character->selected_class) {
            return [];
        }

        // Use existing character model, or create temporary instance for unsaved characters
        $characterForService = $this->character_model ?? $this->createTemporaryCharacterInstance();

        return $this->domain_card_service->getAvailableCards(
            $characterForService,
            $this->current_advancement_level
        );
    }

    /**
     * Create a temporary Character instance for service methods during creation
     * 
     * Required because some services (like DomainCardService) need a Character model
     * to load class data and determine available options, but during initial creation
     * the character hasn't been saved to the database yet.
     *
     * @return Character Temporary character instance with current builder data
     */
    private function createTemporaryCharacterInstance(): Character
    {
        $character = new Character();
        $character->class = $this->character->selected_class;
        $character->level = $this->current_advancement_level ?? 1;
        $character->subclass = $this->character->selected_subclass;
        
        // Set a temporary ID to prevent null ID issues in service methods
        // This character is NOT saved to database, just used for service logic
        $character->id = -1;
        
        return $character;
    }

    /**
     * Validate current advancement level's selections
     *
     * @return array Array of validation errors
     */
    public function validateCurrentLevel(): array
    {
        return $this->character->validateLevelCompletion($this->current_advancement_level);
    }

    /**
     * Check if current advancement level is complete
     *
     * @return bool
     */
    public function isCurrentLevelComplete(): bool
    {
        return $this->character->isLevelComplete($this->current_advancement_level);
    }

    /**
     * Validate all advancement selections
     *
     * @return array Array of validation errors grouped by level
     */
    public function validateAllAdvancements(): array
    {
        return $this->character->validateAdvancementSelections();
    }

    /**
     * Get overall advancement progress
     *
     * @return array Progress data with percentage complete
     */
    public function getAdvancementProgress(): array
    {
        return $this->character->getAdvancementProgress();
    }
}
