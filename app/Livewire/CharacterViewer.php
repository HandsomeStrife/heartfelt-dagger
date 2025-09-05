<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterStatusAction;
use Domain\Character\Actions\SaveCharacterStatusAction;
use Domain\Character\Data\CharacterData;
use Domain\Character\Data\CharacterStatusData;
use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterRepository;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use GrahamCampbell\Markdown\Facades\Markdown;

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

    // Repository
    private CharacterRepository $character_repository;

    public function mount(string $publicKey, string $characterKey, bool $canEdit): void
    {
        $this->public_key = $publicKey;
        $this->character_key = $characterKey;
        $this->can_edit = $canEdit;

        // Initialize repository
        $this->character_repository = new CharacterRepository();

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
        if (!$this->character) {
            return;
        }

        // Get computed stats for the character
        $computed_stats = $this->getComputedStats();

        // Load character status using Action
        $load_action = new LoadCharacterStatusAction();
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
     * Get computed character stats for display
     */
    public function getComputedStats(): array
    {
        if (!$this->character) {
            return [];
        }

        // Convert CharacterStatsData to the array format expected by the frontend
        return [
            'evasion' => $this->character->stats->evasion,
            'hit_points' => $this->character->stats->hit_points,
            'final_hit_points' => $this->character->stats->hit_points,
            'stress' => $this->character->stats->stress,
            'hope' => $this->character->stats->hope,
            'major_threshold' => $this->character->stats->major_threshold,
            'severe_threshold' => $this->character->stats->severe_threshold,
            'armor_score' => $this->character->stats->armor_score,
        ];
    }

    /**
     * Get selected class details
     */
    public function getClassData(): ?array
    {
        if (empty($this->character->class) || !isset($this->game_data['classes'][$this->character->class])) {
            return null;
        }

        return $this->game_data['classes'][$this->character->class];
    }

    /**
     * Get formatted trait value with proper + prefix
     */
    public function getFormattedTraitValue(string $trait): string
    {
        $trait_record = $this->character->traits()->where('trait_name', $trait)->first();
        $value = $trait_record ? $trait_record->trait_value : 0;
        return $value >= 0 ? '+' . $value : (string) $value;
    }

    /**
     * Get all trait values formatted for display
     */
    public function getFormattedTraitValues(): array
    {
        $trait_values = [];
        $trait_names = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];
        
        foreach ($trait_names as $trait) {
            $value = $this->character->traits->{$trait} ?? 0;
            $trait_values[$trait] = $value >= 0 ? '+' . $value : (string) $value;
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
        if (empty($this->character->subclass) || !isset($this->game_data['subclasses'][$this->character->subclass])) {
            return null;
        }

        return $this->game_data['subclasses'][$this->character->subclass];
    }

    /**
     * Get selected ancestry details
     */
    public function getAncestryData(): ?array
    {
        if (empty($this->character->ancestry) || !isset($this->game_data['ancestries'][$this->character->ancestry])) {
            return null;
        }

        return $this->game_data['ancestries'][$this->character->ancestry];
    }

    /**
     * Get selected community details
     */
    public function getCommunityData(): ?array
    {
        if (empty($this->character->community) || !isset($this->game_data['communities'][$this->character->community])) {
            return null;
        }

        return $this->game_data['communities'][$this->character->community];
    }

    /**
     * Get equipment data organized by type
     */
    public function getOrganizedEquipment(): array
    {
        $organized = [
            'weapons' => [],
            'armor' => [],
            'items' => [],
            'consumables' => [],
        ];

        // Group equipment by type
        foreach ($this->character->equipment as $equipment) {
            $equipment_data = [
                'id' => $equipment->id,
                'type' => $equipment->equipment_type,
                'key' => $equipment->equipment_key,
                'data' => $equipment->equipment_data,
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
                    'ability_data' => $ability_data
                ];
            } else {
                $domain_cards[] = [
                    'domain' => $domain,
                    'ability_key' => $ability_key,
                    'ability_level' => $ability_level,
                    'ability_data' => null
                ];
            }
        }

        return $domain_cards;
    }

    public function saveCharacterState(array $state): void
    {
        if (!$this->can_edit || !$this->character) {
            return;
        }

        try {
            // Convert Alpine.js state to CharacterStatusData
            $status_data = CharacterStatusData::fromAlpineState($this->character->id, $state);
            
            // Save using Action
            $save_action = new SaveCharacterStatusAction();
            $this->character_status = $save_action->execute($this->character_key, $status_data);
            
        } catch (\Exception $e) {
            // Log error but don't break the UI
            \Log::error('Failed to save character state: ' . $e->getMessage());
        }
    }

    public function getCharacterState(): ?array
    {
        if (!$this->character_status) {
            return null;
        }

        // Return state in Alpine.js format
        return $this->character_status->toAlpineState();
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
        ]);
    }
}
