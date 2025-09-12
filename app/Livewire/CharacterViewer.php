<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterStatusAction;
use Domain\Character\Actions\SaveCharacterStatusAction;
use Domain\Character\Data\CharacterData;
use Domain\Character\Data\CharacterStatusData;
use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterAdvancementRepository;
use Domain\Character\Repositories\CharacterRepository;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class CharacterViewer extends Component
{
    public string $public_key;

    public string $character_key;

    public bool $can_edit;

    public ?CharacterData $character = null;

    public ?CharacterStatusData $character_status = null;

    public ?string $pronouns = null;

    // Game Data
    public array $game_data = [];

    // Repositories
    private CharacterRepository $character_repository;

    private CharacterAdvancementRepository $advancement_repository;

    public function mount(string $publicKey, string $characterKey, bool $canEdit): void
    {
        $this->public_key = $publicKey;
        $this->character_key = $characterKey;
        $this->can_edit = $canEdit;

        // Initialize repositories
        $this->character_repository = new CharacterRepository;
        $this->advancement_repository = new CharacterAdvancementRepository;

        // Load character data using repository
        $this->character = $this->character_repository->findByKey($characterKey);

        if (! $this->character) {
            abort(404, 'Character not found');
        }

        $this->pronouns = $this->character->pronouns ?? null;

        // Load character status
        $this->loadCharacterStatus();

        $this->loadGameData();
    }

    public function loadCharacterStatus(): void
    {
        if (! $this->character) {
            return;
        }

        // Get computed stats for the character
        $computed_stats = $this->getComputedStats();

        // Load character status using Action
        $load_action = new LoadCharacterStatusAction;
        $this->character_status = $load_action->execute($this->character_key, $computed_stats);
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
     * Get computed character stats for display including advancement bonuses
     */
    public function getComputedStats(): array
    {
        if (! $this->character) {
            return [];
        }

        // Get the Character model to access advancement bonus methods
        $character_model = Character::where('character_key', $this->character_key)->first();
        if (! $character_model) {
            // Fallback to basic stats if no model found
            return [
                'evasion' => $this->character->stats->evasion,
                'hit_points' => $this->character->stats->hit_points,
                'final_hit_points' => $this->character->stats->hit_points,
                'stress' => $this->character->stats->stress,
                'hope' => $this->character->stats->hope,
                'major_threshold' => $this->character->getMajorThreshold(),
                'severe_threshold' => $this->character->getSevereThreshold(),
                'armor_score' => $this->character->getTotalArmorScore(),
            ];
        }

        // Calculate stats with advancement bonuses
        $base_evasion = $this->character->stats->evasion;
        $evasion_bonuses = $character_model->getTotalEvasionBonuses();
        $total_evasion = $base_evasion + array_sum($evasion_bonuses);

        $base_hit_points = $this->character->stats->hit_points;
        $hit_point_bonuses = $character_model->getTotalHitPointBonuses();
        $total_hit_points = $base_hit_points + array_sum($hit_point_bonuses);

        $base_stress = $this->character->stats->stress;
        $stress_bonuses = $character_model->getTotalStressBonuses();
        $total_stress = $base_stress + array_sum($stress_bonuses);

        return [
            'evasion' => $total_evasion,
            'hit_points' => $total_hit_points,
            'final_hit_points' => $total_hit_points,
            'stress' => $total_stress,
            'hope' => $this->character->stats->hope,
            'major_threshold' => $this->character->getMajorThreshold(),
            'severe_threshold' => $this->character->getSevereThreshold(),
            'armor_score' => $this->character->getTotalArmorScore(),
        ];
    }

    /**
     * Get selected class details
     */
    public function getClassData(): ?array
    {
        if (empty($this->character->class) || ! isset($this->game_data['classes'][$this->character->class])) {
            return null;
        }

        return $this->game_data['classes'][$this->character->class];
    }

    /**
     * Get formatted trait value with proper + prefix, including advancement bonuses
     */
    public function getFormattedTraitValue(string $trait): string
    {
        // Get the Character model to access effective trait values including advancement bonuses
        $character_model = Character::where('character_key', $this->character_key)->first();
        if (! $character_model) {
            // Fallback to basic traits if no model found
            $traits_array = $this->character->traits->toArray();
            $value = $traits_array[$trait] ?? 0;
            return $value >= 0 ? '+'.$value : (string) $value;
        }

        $trait_enum = \Domain\Character\Enums\TraitName::from($trait);
        $value = $character_model->getEffectiveTraitValue($trait_enum);
        return $value >= 0 ? '+'.$value : (string) $value;
    }

    /**
     * Get all trait values formatted for display, including advancement bonuses
     */
    public function getFormattedTraitValues(): array
    {
        $trait_values = [];
        $trait_names = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];

        // Get the Character model to access effective trait values including advancement bonuses
        $character_model = Character::where('character_key', $this->character_key)->first();
        if (! $character_model) {
            // Fallback to basic traits if no model found
            $traits_array = $this->character->traits->toArray();
            foreach ($trait_names as $trait) {
                $value = $traits_array[$trait] ?? 0;
                $trait_values[$trait] = $value >= 0 ? '+'.$value : (string) $value;
            }
            return $trait_values;
        }

        // Use effective trait values that include advancement bonuses
        foreach ($trait_names as $trait) {
            $trait_enum = \Domain\Character\Enums\TraitName::from($trait);
            $value = $character_model->getEffectiveTraitValue($trait_enum);
            $trait_values[$trait] = $value >= 0 ? '+'.$value : (string) $value;
        }

        return $trait_values;
    }

    /**
     * Get trait information
     */
    public function getTraitInfo(): array
    {
        return [
            'agility' => 'Agility',
            'strength' => 'Strength',
            'finesse' => 'Finesse',
            'instinct' => 'Instinct',
            'presence' => 'Presence',
            'knowledge' => 'Knowledge',
        ];
    }

    /**
     * Get selected subclass details
     */
    public function getSubclassData(): ?array
    {
        if (empty($this->character->subclass) || ! isset($this->game_data['subclasses'][$this->character->subclass])) {
            return null;
        }

        return $this->game_data['subclasses'][$this->character->subclass];
    }

    /**
     * Get selected ancestry details
     */
    public function getAncestryData(): ?array
    {
        if (empty($this->character->ancestry) || ! isset($this->game_data['ancestries'][$this->character->ancestry])) {
            return null;
        }

        return $this->game_data['ancestries'][$this->character->ancestry];
    }

    /**
     * Get selected community details
     */
    public function getCommunityData(): ?array
    {
        if (empty($this->character->community) || ! isset($this->game_data['communities'][$this->character->community])) {
            return null;
        }

        return $this->game_data['communities'][$this->character->community];
    }

    /**
     * Get equipment data organized by type, using current JSON data
     */
    public function getOrganizedEquipment(): array
    {
        $organized = [
            'weapons' => [],
            'armor' => [],
            'items' => [],
            'consumables' => [],
        ];

        // Group equipment by type, but use fresh JSON data
        foreach ($this->character->equipment as $equipment) {
            // Get the current data from JSON files based on equipment key and type
            $fresh_data = $this->getFreshEquipmentData($equipment->equipment_key, $equipment->equipment_type);
            
            $equipment_data = [
                'id' => $equipment->id,
                'type' => $equipment->equipment_type,
                'key' => $equipment->equipment_key,
                'data' => $fresh_data ?? $equipment->equipment_data, // Fallback to stored data if not found
                'is_equipped' => $equipment->is_equipped,
            ];

            if ($equipment->isWeapon()) {
                $organized['weapons'][] = $equipment_data;
            } elseif ($equipment->isArmor()) {
                $organized['armor'][] = $equipment_data;
            } elseif ($equipment->isItem()) {
                $organized['items'][] = $equipment_data;
            } elseif ($equipment->isConsumable()) {
                $organized['consumables'][] = $equipment_data;
            }
        }

        return $organized;
    }

    /**
     * Get fresh equipment data from JSON files
     */
    private function getFreshEquipmentData(string $equipment_key, string $equipment_type): ?array
    {
        $json_file = match($equipment_type) {
            'weapon' => 'weapons',
            'armor' => 'armor', 
            'item' => 'items',
            'consumable' => 'consumables',
            default => null,
        };

        if (!$json_file || !isset($this->game_data[$json_file][$equipment_key])) {
            return null;
        }

        return $this->game_data[$json_file][$equipment_key];
    }

    /**
     * Get character's current proficiency bonus
     */
    public function getProficiencyBonus(): int
    {
        return $this->character->proficiency;
    }

    /**
     * Get number of damage dice for weapons (equals proficiency)
     */
    public function getWeaponDamageCount(): int
    {
        return $this->character->proficiency;
    }

    /**
     * Get the primary weapon from organized equipment
     */
    public function getPrimaryWeapon(): ?array
    {
        $organized = $this->getOrganizedEquipment();
        $weapons = $organized['weapons'] ?? [];
        
        return collect($weapons)->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary');
    }

    /**
     * Get formatted weapon feature text for primary weapon
     */
    public function getPrimaryWeaponFeature(): string
    {
        $primary = $this->getPrimaryWeapon();
        if (!$primary) {
            return 'No feature present for the selected weapon.';
        }

        $feature = $primary['data']['feature'] ?? null;
        
        if (is_string($feature) && $feature !== '') {
            return $feature;
        } elseif (is_array($feature)) {
            $parts = [];
            if (function_exists('array_is_list') && array_is_list($feature)) {
                foreach ($feature as $entry) {
                    if (is_string($entry)) {
                        $parts[] = $entry;
                    } elseif (is_array($entry)) {
                        $parts[] = $entry['description'] ?? ($entry['name'] ?? '');
                    }
                }
            } else {
                $parts[] = $feature['description'] ?? ($feature['name'] ?? '');
            }
            $parts = array_filter($parts, fn ($p) => $p !== '');
            return empty($parts) ? 'No feature present for the selected weapon.' : implode('; ', $parts);
        }
        
        return 'No feature present for the selected weapon.';
    }

    /**
     * Produce a readable feature string for a weapon, regardless of data shape.
     */
    public function getWeaponFeatureText(array $weaponData): string
    {
        $feature = $weaponData['feature'] ?? null;

        if ($feature === null || $feature === '') {
            return 'No feature present for the selected weapon.';
        }

        if (is_string($feature)) {
            return $feature;
        }

        if (is_array($feature)) {
            // Numeric array → multiple features
            if (function_exists('array_is_list') && array_is_list($feature)) {
                $parts = [];
                foreach ($feature as $entry) {
                    if (is_string($entry)) {
                        $parts[] = $entry;
                    } elseif (is_array($entry)) {
                        $parts[] = $entry['description'] ?? ($entry['name'] ?? '');
                    }
                }
                $parts = array_filter($parts, fn ($p) => $p !== '');

                return empty($parts) ? 'No feature present for the selected weapon.' : implode('; ', $parts);
            }

            // Associative array → single feature object
            return $feature['description'] ?? ($feature['name'] ?? 'No feature present for the selected weapon.');
        }

        return 'No feature present for the selected weapon.';
    }

    /**
     * Get domain card details with ability data
     */
    public function getDomainCardDetails(): array
    {
        $domain_cards = [];

        foreach ($this->character->domain_cards as $card) {
            $ability_key = $card->ability_key ?? null;
            $domain = $card->domain ?? null;
            $ability_level = $card->level ?? 1;

            if ($ability_key && isset($this->game_data['abilities'][$ability_key])) {
                $ability_data = $this->game_data['abilities'][$ability_key];
                $domain_cards[] = [
                    'domain' => $domain,
                    'ability_key' => $ability_key,
                    'ability_level' => $ability_level,
                    'ability_data' => $ability_data,
                ];
            } else {
                $domain_cards[] = [
                    'domain' => $domain,
                    'ability_key' => $ability_key,
                    'ability_level' => $ability_level,
                    'ability_data' => null,
                ];
            }
        }

        return $domain_cards;
    }

    public function saveCharacterState(array $state): void
    {
        if (! $this->can_edit || ! $this->character) {
            return;
        }

        try {
            // Convert Alpine.js state to CharacterStatusData
            $status_data = CharacterStatusData::fromAlpineState($this->character->id, $state);

            // Save using Action
            $save_action = new SaveCharacterStatusAction;
            $this->character_status = $save_action->execute($this->character_key, $status_data);

        } catch (\Exception $e) {
            // Log error but don't break the UI
            \Illuminate\Support\Facades\Log::error('Failed to save character state: '.$e->getMessage());
        }
    }

    public function getCharacterState(): ?array
    {
        if (! $this->character_status) {
            return null;
        }

        // Return state in Alpine.js format
        return $this->character_status->toAlpineState();
    }

    /**
     * Check if character can level up
     */
    public function canLevelUp(): bool
    {
        // Only enable for non-production environments for now
        if (app()->environment('production')) {
            return false;
        }

        if (! $this->character || ! $this->can_edit) {
            return false;
        }

        // Ensure repository is initialized
        if (! isset($this->advancement_repository)) {
            $this->advancement_repository = new CharacterAdvancementRepository;
        }

        // Find the character model to access the level-up logic
        $character_model = Character::where('character_key', $this->character_key)->first();
        if (! $character_model) {
            return false;
        }

        return $this->advancement_repository->canLevelUp($character_model);
    }

    /**
     * Get character's current advancement status
     */
    public function getAdvancementStatus(): array
    {
        if (! $this->character) {
            return [
                'can_level_up' => false,
                'current_tier' => 1,
                'available_slots' => [],
                'advancements' => [],
            ];
        }

        // Ensure repository is initialized
        if (! isset($this->advancement_repository)) {
            $this->advancement_repository = new CharacterAdvancementRepository;
        }

        $character_model = Character::where('character_key', $this->character_key)->first();
        if (! $character_model) {
            return [
                'can_level_up' => false,
                'current_tier' => 1,
                'available_slots' => [],
                'advancements' => [],
            ];
        }

        $current_tier = $character_model->getTier();
        $available_slots = $this->advancement_repository->getAvailableSlots($character_model->id, $current_tier);
        $advancements = $this->advancement_repository->getCharacterAdvancements($character_model->id);

        return [
            'can_level_up' => $this->advancement_repository->canLevelUp($character_model),
            'current_tier' => $current_tier,
            'available_slots' => $available_slots,
            'advancements' => $advancements->toArray(),
        ];
    }

    public function render()
    {
        $class_data = $this->getClassData();
        $computed_stats = $this->getComputedStats();
        ray()->send($this->character);

        return view('livewire.character-viewer', [
            'character' => $this->character,
            'character_status' => $this->character_status,
            'pronouns' => $this->pronouns,
            'game_data' => $this->game_data,
            'computed_stats' => $computed_stats,
            'class_data' => $class_data,
            'subclass_data' => $this->getSubclassData(),
            'ancestry_data' => $this->getAncestryData(),
            'community_data' => $this->getCommunityData(),
            'organized_equipment' => $this->getOrganizedEquipment(),
            'domain_card_details' => $this->getDomainCardDetails(),
            'trait_values' => $this->getFormattedTraitValues(),
            'advancement_status' => $this->getAdvancementStatus(),
            'can_level_up' => $this->canLevelUp(),
            'proficiency_bonus' => $this->getProficiencyBonus(),
            'weapon_damage_count' => $this->getWeaponDamageCount(),
            'primary_weapon' => $this->getPrimaryWeapon(),
            'primary_weapon_feature' => $this->getPrimaryWeaponFeature(),
        ]);
    }
}
