<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CharacterBuilder extends Component
{
    use WithFileUploads;

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

    // Equipment Category Expansion State
    public array $equipment_category_expanded = [
        'weapons' => true,
        'armor' => true,
        'items' => true,
        'consumables' => true,
    ];

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

        // 2. If character has a saved image, show that from S3
        if ($this->character_model && $this->character_model->profile_image_path) {
            // Use signed URL for secure access (valid for 1 hour)
            return Storage::disk('s3')->temporaryUrl(
                $this->character_model->profile_image_path,
                now()->addHour()
            );
        }

        // 3. No image exists
        return null;
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

    public function selectClass(?string $class_key): void
    {
        // Only reset the fields that should change when class changes
        $this->character->assigned_traits = [];
        $this->character->selected_equipment = [];
        $this->character->background_answers = [];
        $this->character->experiences = [];
        $this->character->selected_domain_cards = [];
        $this->character->connection_answers = [];
        $this->character->manual_step_completions = [];

        // Set the new class
        $this->character->selected_class = $class_key;
        $this->character->selected_subclass = null; // Reset subclass when class changes

        // Reset completed steps array
        $this->completed_steps = [];
        
        // Update state only - do NOT auto-save to database
        $this->updateStateOnly();
    }

    public function selectSubclass(string $subclass_key): void
    {
        // Only reset the fields that should change when subclass changes
        $this->character->assigned_traits = [];
        $this->character->selected_equipment = [];
        $this->character->background_answers = [];
        $this->character->experiences = [];
        $this->character->selected_domain_cards = [];
        $this->character->connection_answers = [];
        $this->character->manual_step_completions = [];

        // Set the new subclass (preserve class)
        $this->character->selected_subclass = $subclass_key;

        // Reset completed steps array
        $this->completed_steps = [];
        
        // Update state only - do NOT auto-save to database
        $this->updateStateOnly();
    }

    public function selectAncestry(?string $ancestry_key): void
    {
        $this->character->selected_ancestry = $ancestry_key;
        $this->updateStateOnly();
    }

    public function selectCommunity(?string $community_key): void
    {
        $this->character->selected_community = $community_key;
        $this->updateStateOnly();
    }

    public function assignTrait(string $trait_name, ?int $value): void
    {
        if ($value === null) {
            // Clear the trait assignment
            unset($this->character->assigned_traits[$trait_name]);
        } else {
            $this->character->assigned_traits[$trait_name] = $value;
        }

        $this->updateStateOnly();
    }

    public function resetTraits(): void
    {
        $this->character->assigned_traits = [];
        $this->updateStateOnly();
    }

    public function updateCharacterName(string $name): void
    {
        $this->character->name = $name;
        $this->saveToDatabase(); // Keep auto-save for name - typed input field
        $this->dispatch('character-updated', $this->character);
    }

    public function updatePronouns(string $pronouns): void
    {
        $this->pronouns = $pronouns;
        $this->saveToDatabase(); // Keep auto-save for pronouns - typed input field
        $this->dispatch('character-updated', $this->character);
    }

    /**
     * Auto-save when character object properties change via live model binding
     * This catches any updates to the $character object properties like character.name
     */
    public function updatedCharacter($value, $key): void
    {
        // Auto-save for specific properties that should save immediately
        $autoSaveProperties = [
            'name', 
            'background_answers', 
            'physical_description', 
            'personality_traits', 
            'personal_history', 
            'motivations'
        ];
        
        // Handle nested properties like background_answers.0
        $topLevelKey = explode('.', $key)[0];
        
        if (in_array($key, $autoSaveProperties) || in_array($topLevelKey, $autoSaveProperties)) {
            $this->saveToDatabase();
            $this->dispatch('character-updated', $this->character);
        }
    }

    /**
     * Auto-save when pronouns change via live model binding
     */
    public function updatedPronouns(): void
    {
        $this->saveToDatabase();
        $this->dispatch('character-updated', $this->character);
    }

    public function updatedProfileImage(): void
    {
        $this->validate([
            'profile_image' => 'image|max:2048', // 2MB Max
        ]);

        if ($this->profile_image) {
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
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Image uploaded successfully to S3!',
                ]);

            } catch (\Exception $e) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to upload image: ' . $e->getMessage(),
                ]);
                \Log::error('Image upload failed', [
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

    public function selectEquipment(string $equipment_key, string $equipment_type): void
    {
        // Handle different data source naming conventions
        $data_key = match ($equipment_type) {
            'weapon' => 'weapons',
            'armor' => 'armor',
            default => $equipment_type.'s',
        };

        $equipment = $this->game_data[$data_key][$equipment_key] ?? null;

        if ($equipment) {
            // Remove existing equipment of the same type for single selection
            if ($equipment_type === 'weapon') {
                // For weapons, check the weapon type (Primary/Secondary) for single selection
                $weapon_type = $equipment['type'] ?? 'Primary';
                $this->character->selected_equipment = collect($this->character->selected_equipment)
                    ->reject(fn ($eq) => $eq['type'] === 'weapon' && ($eq['data']['type'] ?? 'Primary') === $weapon_type)
                    ->values()
                    ->toArray();
            } else {
                // For armor, remove any existing armor
                $this->character->selected_equipment = collect($this->character->selected_equipment)
                    ->reject(fn ($eq) => $eq['type'] === $equipment_type)
                    ->values()
                    ->toArray();
            }

            // Add the new equipment
            $this->character->selected_equipment[] = [
                'key' => $equipment_key,
                'type' => $equipment_type,
                'data' => $equipment,
            ];

            $this->updateCompletedSteps();
            $this->saveToDatabase();
            $this->dispatch('character-updated', $this->character);
        }
    }

    public function selectInventoryItem(string $item_name): void
    {
        $item_key = strtolower($item_name);

        // Try to find in items first
        $item_data = $this->game_data['items'][$item_key] ?? null;
        if ($item_data) {
            $this->character->selected_equipment[] = [
                'key' => $item_key,
                'type' => 'item',
                'data' => $item_data,
            ];

            $this->updateCompletedSteps();
            $this->saveToDatabase();
            $this->dispatch('character-updated', $this->character);

            return;
        }

        // Try consumables if not found in items
        $consumable_data = $this->game_data['consumables'][$item_key] ?? null;
        if ($consumable_data) {
            $this->character->selected_equipment[] = [
                'key' => $item_key,
                'type' => 'consumable',
                'data' => $consumable_data,
            ];

            $this->updateCompletedSteps();
            $this->saveToDatabase();
            $this->dispatch('character-updated', $this->character);

            return;
        }

        // Item not found in either category
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => "Item '{$item_name}' not found in game data.",
        ]);
    }

    public function removeEquipment(int $index): void
    {
        unset($this->character->selected_equipment[$index]);
        $this->character->selected_equipment = array_values($this->character->selected_equipment);
        $this->updateStateOnly();
    }

    public function clearAllEquipment(): void
    {
        $this->character->selected_equipment = [];
        $this->updateStateOnly();
    }

    public function toggleEquipmentCategory(string $category): void
    {
        $this->equipment_category_expanded[$category] = ! ($this->equipment_category_expanded[$category] ?? false);
    }

    public function updateBackgroundAnswer(int $question_index, string $answer): void
    {
        $this->character->background_answers[$question_index] = $answer;
        $this->updateStateOnly();
    }

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

    public function selectDomainCard(string $domain, string $ability_key): void
    {
        $ability = $this->game_data['abilities'][$ability_key] ?? null;

        if (! $ability) {
            return;
        }

        // Check if the card is already selected and remove it (deselect)
        $existing_index = collect($this->character->selected_domain_cards)->search(function ($card) use ($domain, $ability_key) {
            return $card['domain'] === $domain && $card['ability_key'] === $ability_key;
        });

        if ($existing_index !== false) {
            // Card is already selected, remove it (deselect)
            unset($this->character->selected_domain_cards[$existing_index]);
            $this->character->selected_domain_cards = array_values($this->character->selected_domain_cards);
        } elseif (count($this->character->selected_domain_cards) < $this->character->getMaxDomainCards()) {
            // Card is not selected and we have space, add it
            $this->character->selected_domain_cards[] = [
                'domain' => $domain,
                'ability_key' => $ability_key,
                'ability_level' => $ability['level'] ?? 1,
                'ability_data' => $ability,
            ];
        }

        $this->updateStateOnly();
    }

    public function removeDomainCard(int $index): void
    {
        unset($this->character->selected_domain_cards[$index]);
        $this->character->selected_domain_cards = array_values($this->character->selected_domain_cards);
        $this->updateStateOnly();
    }

    public function clearAllDomainCards(): void
    {
        $this->character->selected_domain_cards = [];
        $this->updateStateOnly();
    }

    public function updateConnectionAnswer(int $question_index, string $answer): void
    {
        $this->character->connection_answers[$question_index] = $answer;
        $this->updateStateOnly();
    }

    public function applySuggestedTraits(): void
    {
        if (! $this->character->selected_class) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select a class first.',
            ]);

            return;
        }

        $class_data = $this->game_data['classes'][$this->character->selected_class] ?? null;
        if (! $class_data || ! isset($class_data['suggestedTraits'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No trait suggestions available for this class.',
            ]);

            return;
        }

        $this->character->assigned_traits = $class_data['suggestedTraits'];
        $this->updateStateOnly();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Applied suggested traits for '.($class_data['name'] ?? $this->character->selected_class).'!',
        ]);
    }

    public function applySuggestedEquipment(): void
    {
        if (! $this->character->selected_class) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select a class first.',
            ]);

            return;
        }

        $class_data = $this->game_data['classes'][$this->character->selected_class] ?? null;
        if (! $class_data) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No equipment suggestions available for this class.',
            ]);

            return;
        }

        // Clear existing equipment
        $this->character->selected_equipment = [];

        // Add suggested weapons
        if (isset($class_data['suggestedWeapons']['primary'])) {
            $primary_weapon = $class_data['suggestedWeapons']['primary'];
            if (is_array($primary_weapon) && isset($primary_weapon['name'])) {
                $weapon_key = strtolower($primary_weapon['name']);
                $weapon_data = $this->game_data['weapons'][$weapon_key] ?? null;

                if ($weapon_data) {
                    $this->character->selected_equipment[] = [
                        'key' => $weapon_key,
                        'type' => 'weapon',
                        'data' => $weapon_data,
                    ];
                }
            }
        }

        if (isset($class_data['suggestedWeapons']['secondary'])) {
            $secondary_weapon = $class_data['suggestedWeapons']['secondary'];
            if (is_array($secondary_weapon) && isset($secondary_weapon['name'])) {
                $weapon_key = strtolower($secondary_weapon['name']);
                $weapon_data = $this->game_data['weapons'][$weapon_key] ?? null;

                if ($weapon_data) {
                    $this->character->selected_equipment[] = [
                        'key' => $weapon_key,
                        'type' => 'weapon',
                        'data' => $weapon_data,
                    ];
                }
            }
        }

        // Add suggested armor
        if (isset($class_data['suggestedArmor'])) {
            $suggested_armor = $class_data['suggestedArmor'];
            if (is_array($suggested_armor) && isset($suggested_armor['name'])) {
                $armor_key = strtolower($suggested_armor['name']);
                $armor_data = $this->game_data['armor'][$armor_key] ?? null;

                if ($armor_data) {
                    $this->character->selected_equipment[] = [
                        'key' => $armor_key,
                        'type' => 'armor',
                        'data' => $armor_data,
                    ];
                }
            }
        }

        // Add starting inventory items
        if (isset($class_data['startingInventory'])) {
            $inventory = $class_data['startingInventory'];

            // Handle 'always' items
            if (isset($inventory['always']) && is_array($inventory['always'])) {
                foreach ($inventory['always'] as $item) {
                    if (is_string($item)) {
                        $item_key = strtolower($item);

                        // Try to find in items first
                        $item_data = $this->game_data['items'][$item_key] ?? null;
                        if ($item_data) {
                            $this->character->selected_equipment[] = [
                                'key' => $item_key,
                                'type' => 'item',
                                'data' => $item_data,
                            ];

                            continue;
                        }

                        // Try consumables if not found in items
                        $consumable_data = $this->game_data['consumables'][$item_key] ?? null;
                        if ($consumable_data) {
                            $this->character->selected_equipment[] = [
                                'key' => $item_key,
                                'type' => 'consumable',
                                'data' => $consumable_data,
                            ];
                        }
                    }
                }
            }

            // Handle 'chooseOne' items (pick the first one)
            if (isset($inventory['chooseOne']) && is_array($inventory['chooseOne']) && ! empty($inventory['chooseOne'])) {
                $first_choice = $inventory['chooseOne'][0];
                if (is_string($first_choice)) {
                    $item_key = strtolower($first_choice);

                    // Try to find in items first
                    $item_data = $this->game_data['items'][$item_key] ?? null;
                    if ($item_data) {
                        $this->character->selected_equipment[] = [
                            'key' => $item_key,
                            'type' => 'item',
                            'data' => $item_data,
                        ];
                    } else {
                        // Try consumables if not found in items
                        $consumable_data = $this->game_data['consumables'][$item_key] ?? null;
                        if ($consumable_data) {
                            $this->character->selected_equipment[] = [
                                'key' => $item_key,
                                'type' => 'consumable',
                                'data' => $consumable_data,
                            ];
                        }
                    }
                }
            }
        }

        $this->updateStateOnly();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Applied suggested equipment for '.($class_data['name'] ?? $this->character->selected_class).'!',
        ]);
    }

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

    /**
     * Helper method to update completion state, save to database, and dispatch character-updated event
     * ONLY USE THIS FOR EXPLICIT SAVE OPERATIONS!
     */
    private function saveAndUpdateState(): void
    {
        $this->updateCompletedSteps();
        $this->saveToDatabase();
        $this->dispatch('character-updated', $this->character);
    }

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

            // Dispatch success notification
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Character saved successfully!',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to save character: '.$e->getMessage(),
            ]);
        }
    }

    public function saveCharacter(): void
    {
        if (! $this->character->isStepComplete(9)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please complete all steps before saving.',
            ]);

            return;
        }

        try {
            $action = new SaveCharacterAction;
            $character = $action->execute($this->character, Auth::user());

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Character saved successfully!',
            ]);

            $this->dispatch('character-saved', [
                'character_key' => $character->character_key,
                'share_url' => $character->getShareUrl(),
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to save character: '.$e->getMessage(),
            ]);
        }
    }

    public function resetCharacter(): void
    {
        $this->character = new CharacterBuilderData;
        $this->completed_steps = [];

        $this->dispatch('reset-storage', ['key' => $this->storage_key]);
        $this->dispatch('character-reset');
    }

    public function getSuggestedEquipment(): array
    {
        if (! $this->character->selected_class) {
            return [];
        }

        $class_data = $this->game_data['classes'][$this->character->selected_class] ?? null;
        if (! $class_data) {
            return [];
        }

        $suggestions = [];

        // Add suggested weapons
        if (isset($class_data['suggestedWeapons']['primary'])) {
            $primary_weapon = $class_data['suggestedWeapons']['primary'];
            if (is_array($primary_weapon) && isset($primary_weapon['name'])) {
                $weapon_key = strtolower($primary_weapon['name']);
                $weapon_data = $this->game_data['weapons'][$weapon_key] ?? null;

                if ($weapon_data) {
                    $suggestions[] = [
                        'key' => $weapon_key,
                        'type' => 'weapon',
                        'category' => 'Primary Weapon',
                        'data' => $weapon_data,
                        'reason' => 'Suggested primary weapon for '.$class_data['name'],
                    ];
                }
            }
        }

        if (isset($class_data['suggestedWeapons']['secondary'])) {
            $secondary_weapon = $class_data['suggestedWeapons']['secondary'];
            if (is_array($secondary_weapon) && isset($secondary_weapon['name'])) {
                $weapon_key = strtolower($secondary_weapon['name']);
                $weapon_data = $this->game_data['weapons'][$weapon_key] ?? null;

                if ($weapon_data) {
                    $suggestions[] = [
                        'key' => $weapon_key,
                        'type' => 'weapon',
                        'category' => 'Secondary Weapon',
                        'data' => $weapon_data,
                        'reason' => 'Suggested secondary weapon for '.$class_data['name'],
                    ];
                }
            }
        }

        // Add suggested armor
        if (isset($class_data['suggestedArmor'])) {
            $suggested_armor = $class_data['suggestedArmor'];
            if (is_array($suggested_armor) && isset($suggested_armor['name'])) {
                $armor_key = strtolower($suggested_armor['name']);
                $armor_data = $this->game_data['armor'][$armor_key] ?? null;

                if ($armor_data) {
                    $suggestions[] = [
                        'key' => $armor_key,
                        'type' => 'armor',
                        'category' => 'Armor',
                        'data' => $armor_data,
                        'reason' => 'Suggested armor for '.$class_data['name'],
                    ];
                }
            }
        }

        // Add starting inventory
        if (isset($classData['startingInventory'])) {
            $inventory = $classData['startingInventory'];

            // Handle 'always' items
            if (isset($inventory['always']) && is_array($inventory['always'])) {
                foreach ($inventory['always'] as $item) {
                    if (is_string($item)) {
                        $itemKey = strtolower($item);

                        // Try items first
                        $itemData = $this->game_data['items'][$itemKey] ?? null;
                        if ($itemData) {
                            $suggestions[] = [
                                'key' => $itemKey,
                                'type' => 'item',
                                'category' => 'Starting Gear',
                                'data' => $itemData,
                                'reason' => 'Essential starting item for '.$classData['name'],
                            ];

                            continue;
                        }

                        // Try consumables
                        $consumableData = $this->game_data['consumables'][$itemKey] ?? null;
                        if ($consumableData) {
                            $suggestions[] = [
                                'key' => $itemKey,
                                'type' => 'consumable',
                                'category' => 'Starting Consumables',
                                'data' => $consumableData,
                                'reason' => 'Essential starting consumable for '.$classData['name'],
                            ];
                        }
                    }
                }
            }

            // Handle 'chooseOne' items (pick the first one as suggestion)
            if (isset($inventory['chooseOne']) && is_array($inventory['chooseOne']) && ! empty($inventory['chooseOne'])) {
                $firstChoice = $inventory['chooseOne'][0];
                if (is_string($firstChoice)) {
                    $itemKey = strtolower($firstChoice);

                    // Try items first
                    $itemData = $this->game_data['items'][$itemKey] ?? null;
                    if ($itemData) {
                        $suggestions[] = [
                            'key' => $itemKey,
                            'type' => 'item',
                            'category' => 'Starting Options',
                            'data' => $itemData,
                            'reason' => 'Recommended choice for '.$classData['name'],
                        ];
                    } else {
                        // Try consumables
                        $consumableData = $this->game_data['consumables'][$itemKey] ?? null;
                        if ($consumableData) {
                            $suggestions[] = [
                                'key' => $itemKey,
                                'type' => 'consumable',
                                'category' => 'Starting Options',
                                'data' => $consumableData,
                                'reason' => 'Recommended choice for '.$classData['name'],
                            ];
                        }
                    }
                }
            }
        }

        return $suggestions;
    }

    /**
     * Get filtered data for current selections
     */
    public function getFilteredData(): array
    {
        return [
            // Filtered subclasses based on selected class
            'available_subclasses' => $this->character->getAvailableSubclasses(
                $this->game_data['classes'] ?? [],
                $this->game_data['subclasses'] ?? []
            ),

            // Filtered domain cards based on selected class
            'filtered_domain_cards' => $this->character->getFilteredDomainCards(
                $this->game_data['domains'] ?? [],
                $this->game_data['abilities'] ?? []
            ),

            // Background questions based on selected class
            'background_questions' => $this->character->getBackgroundQuestions(
                $this->game_data['classes'] ?? []
            ),

            // Connection questions based on selected class
            'connection_questions' => $this->character->getConnectionQuestions(
                $this->game_data['classes'] ?? []
            ),

            // Selected class details
            'selected_class_data' => ! empty($this->character->selected_class) && isset($this->game_data['classes'][$this->character->selected_class])
                ? $this->game_data['classes'][$this->character->selected_class]
                : null,

            // Selected subclass details
            'selected_subclass_data' => ! empty($this->character->selected_subclass) && isset($this->game_data['subclasses'][$this->character->selected_subclass])
                ? $this->game_data['subclasses'][$this->character->selected_subclass]
                : null,

            // Selected ancestry details
            'selected_ancestry_data' => ! empty($this->character->selected_ancestry) && isset($this->game_data['ancestries'][$this->character->selected_ancestry])
                ? $this->game_data['ancestries'][$this->character->selected_ancestry]
                : null,

            // Selected community details
            'selected_community_data' => ! empty($this->character->selected_community) && isset($this->game_data['communities'][$this->character->selected_community])
                ? $this->game_data['communities'][$this->character->selected_community]
                : null,

            // Class suggestions
            'suggested_equipment' => $this->getSuggestedEquipment(),
            'suggested_primary_weapon' => $this->getSuggestedWeaponData('primary'),
            'suggested_secondary_weapon' => $this->getSuggestedWeaponData('secondary'),
            'suggested_armor' => $this->getSuggestedArmorData(),

            // Pre-processed inventory items (to avoid @php tags in Blade)
            'processed_choose_one_items' => $this->getProcessedInventoryItems('chooseOne'),
            'processed_choose_extra_items' => $this->getProcessedInventoryItems('chooseExtra'),
        ];
    }

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
        $selectedPrimary = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'weapon' && ($eq['data']['type'] ?? 'Primary') === 'Primary'
        );

        $selectedSecondary = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'weapon' && ($eq['data']['type'] ?? '') === 'Secondary'
        );

        $selectedArmor = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['type'] === 'armor'
        );

        // Check if starting inventory requirements are met
        $hasSelectedChooseOne = true; // Default to true if no requirements
        $hasSelectedChooseExtra = true; // Default to true if no requirements
        $hasStartingInventory = false;

        if ($this->character->selected_class && isset($this->game_data['classes'][$this->character->selected_class]['startingInventory'])) {
            $startingInventory = $this->game_data['classes'][$this->character->selected_class]['startingInventory'];

            // Check Choose One items
            if (isset($startingInventory['chooseOne']) && is_array($startingInventory['chooseOne']) && ! empty($startingInventory['chooseOne'])) {
                $hasSelectedChooseOne = false;
                $hasStartingInventory = true;

                foreach ($startingInventory['chooseOne'] as $item) {
                    $itemKey = strtolower($item);
                    if (collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['key'] === $itemKey)) {
                        $hasSelectedChooseOne = true;
                        break;
                    }
                }
            }

            // Check Choose Extra items
            if (isset($startingInventory['chooseExtra']) && is_array($startingInventory['chooseExtra']) && ! empty($startingInventory['chooseExtra'])) {
                $hasSelectedChooseExtra = false;
                $hasStartingInventory = true;

                foreach ($startingInventory['chooseExtra'] as $item) {
                    $itemKey = strtolower($item);
                    if (collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['key'] === $itemKey)) {
                        $hasSelectedChooseExtra = true;
                        break;
                    }
                }
            }
        }

        return [
            'selectedPrimary' => $selectedPrimary,
            'selectedSecondary' => $selectedSecondary,
            'selectedArmor' => $selectedArmor,
            'hasSelectedChooseOne' => $hasSelectedChooseOne,
            'hasSelectedChooseExtra' => $hasSelectedChooseExtra,
            'hasStartingInventory' => $hasStartingInventory,
            'equipmentComplete' => $selectedPrimary && $selectedArmor && $hasSelectedChooseOne && $hasSelectedChooseExtra,
        ];
    }

    public function getSuggestedWeaponData(string $type): ?array
    {
        if (! $this->character->selected_class || ! isset($this->game_data['classes'][$this->character->selected_class]['suggestedWeapons'][$type])) {
            return null;
        }

        $suggestion = $this->game_data['classes'][$this->character->selected_class]['suggestedWeapons'][$type];
        $weaponKey = strtolower($suggestion['name']);
        $weaponData = $this->game_data['weapons'][$weaponKey] ?? null;

        if (! $weaponData) {
            return null;
        }

        $isSelected = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['key'] === $weaponKey && $eq['type'] === 'weapon'
        );

        return [
            'suggestion' => $suggestion,
            'weaponKey' => $weaponKey,
            'weaponData' => $weaponData,
            'isSelected' => $isSelected,
        ];
    }

    public function getSuggestedArmorData(): ?array
    {
        if (! $this->character->selected_class || ! isset($this->game_data['classes'][$this->character->selected_class]['suggestedArmor'])) {
            return null;
        }

        $suggestion = $this->game_data['classes'][$this->character->selected_class]['suggestedArmor'];
        $armorKey = strtolower($suggestion['name']);
        $armorData = $this->game_data['armor'][$armorKey] ?? null;

        if (! $armorData) {
            return null;
        }

        $isSelected = collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['key'] === $armorKey && $eq['type'] === 'armor'
        );

        return [
            'suggestion' => $suggestion,
            'armorKey' => $armorKey,
            'armorData' => $armorData,
            'isSelected' => $isSelected,
        ];
    }

    public function isWeaponSuggested(string $weaponKey, string $type): bool
    {
        if (! $this->character->selected_class) {
            return false;
        }

        $classData = $this->game_data['classes'][$this->character->selected_class] ?? null;
        if (! $classData || ! isset($classData['suggestedWeapons'][$type])) {
            return false;
        }

        return strtolower($classData['suggestedWeapons'][$type]['name']) === $weaponKey;
    }

    public function isArmorSuggested(string $armorKey): bool
    {
        if (! $this->character->selected_class) {
            return false;
        }

        $classData = $this->game_data['classes'][$this->character->selected_class] ?? null;
        if (! $classData || ! isset($classData['suggestedArmor'])) {
            return false;
        }

        return strtolower($classData['suggestedArmor']['name']) === $armorKey;
    }

    public function isInventoryItemSelected(string $itemName): bool
    {
        $itemKey = strtolower($itemName);

        return collect($this->character->selected_equipment)->contains(fn ($eq) => $eq['key'] === $itemKey);
    }

    public function syncEquipment(array $selected_equipment): void
    {
        // Update the character's selected equipment from Alpine.js state
        $this->character->selected_equipment = $selected_equipment;

        // Update completion state and save
        $this->updateStateOnly();
    }

    public function getInventoryItemData(string $item_name): array
    {
        $item_key = strtolower($item_name);
        $item_data = $this->game_data['consumables'][$item_key] ?? $this->game_data['items'][$item_key] ?? ['name' => $item_name];
        $item_type = isset($this->game_data['consumables'][$item_key]) ? 'consumable' : 'item';

        return [
            'item_key' => $item_key,
            'item_data' => $item_data,
            'item_type' => $item_type,
        ];
    }

    /**
     * Get processed inventory items for a specific category (chooseOne or chooseExtra)
     * This avoids using @php tags in Blade templates
     */
    private function getProcessedInventoryItems(string $category): array
    {
        $processedItems = [];

        if (! $this->character->selected_class || ! isset($this->game_data['classes'][$this->character->selected_class]['startingInventory'][$category])) {
            return $processedItems;
        }

        $items = $this->game_data['classes'][$this->character->selected_class]['startingInventory'][$category];

        if (! is_array($items)) {
            return $processedItems;
        }

        foreach ($items as $item) {
            $itemInfo = $this->getInventoryItemData($item);
            $processedItems[] = [
                'item_name' => $item,
                'item_key' => $itemInfo['item_key'],
                'item_data' => $itemInfo['item_data'],
                'item_type' => $itemInfo['item_type'],
            ];
        }

        return $processedItems;
    }

    public function getConnectionProgress(): array
    {
        $filtered_data = $this->getFilteredData();
        $total_connections = count($filtered_data['connection_questions'] ?? []);
        $answered_connections = count(array_filter($this->character->connection_answers ?? [], fn ($answer) => ! empty(trim($answer))));

        return [
            'total_connections' => $total_connections,
            'answered_connections' => $answered_connections,
        ];
    }

    public function render()
    {
        return view('livewire.character-builder', [
            'game_data' => $this->game_data,
            'filtered_data' => $this->getFilteredData(),
            'progress_percentage' => $this->character->getProgressPercentage(),
            'is_complete' => count($this->completed_steps) === count(\Domain\Character\Enums\CharacterBuilderStep::getAllInOrder()),
            'completed_steps' => $this->completed_steps,
            'tabs' => $this->getTabsData(),
            'equipment_progress' => $this->getEquipmentProgress(),
            'connection_progress' => $this->getConnectionProgress(),
            'computed_stats' => $this->getComputedStats(),
            'ancestry_bonuses' => $this->character->getAncestryBonuses(),
            'character_level' => $this->character_model?->level ?? 1,
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
