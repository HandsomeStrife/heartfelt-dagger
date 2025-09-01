<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class CharacterViewer extends Component
{
    public string $public_key;
    public string $character_key;
    public bool $can_edit;

    public ?CharacterBuilderData $character = null;

    public ?string $pronouns = null;

    // Game Data
    public array $game_data = [];

    public function mount(string $publicKey, string $characterKey, bool $canEdit): void
    {
        $this->public_key = $publicKey;
        $this->character_key = $characterKey;
        $this->can_edit = $canEdit;

        $action = new LoadCharacterAction;
        $this->character = $action->execute($characterKey);

        if (! $this->character) {
            abort(404, 'Character not found');
        }

        // Load the character model to get pronouns from database
        $character_model = Character::where('character_key', $characterKey)->first();
        $this->pronouns = $character_model->pronouns ?? null;

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

    /**
     * Get selected class details
     */
    public function getClassData(): ?array
    {
        if (empty($this->character->selected_class) || !isset($this->game_data['classes'][$this->character->selected_class])) {
            return null;
        }

        return $this->game_data['classes'][$this->character->selected_class];
    }

    /**
     * Get formatted trait value with proper + prefix
     */
    public function getFormattedTraitValue(string $trait): string
    {
        $value = $this->character->assigned_traits[$trait] ?? 0;
        return $value >= 0 ? '+' . $value : (string) $value;
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
        if (empty($this->character->selected_subclass) || !isset($this->game_data['subclasses'][$this->character->selected_subclass])) {
            return null;
        }

        return $this->game_data['subclasses'][$this->character->selected_subclass];
    }

    /**
     * Get selected ancestry details
     */
    public function getAncestryData(): ?array
    {
        if (empty($this->character->selected_ancestry) || !isset($this->game_data['ancestries'][$this->character->selected_ancestry])) {
            return null;
        }

        return $this->game_data['ancestries'][$this->character->selected_ancestry];
    }

    /**
     * Get selected community details
     */
    public function getCommunityData(): ?array
    {
        if (empty($this->character->selected_community) || !isset($this->game_data['communities'][$this->character->selected_community])) {
            return null;
        }

        return $this->game_data['communities'][$this->character->selected_community];
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

        // Normalize singular types from storage to the pluralized keys used by the view
        $typeMap = [
            'weapon' => 'weapons',
            'armor' => 'armor',
            'item' => 'items',
            'consumable' => 'consumables',
        ];

        foreach ($this->character->selected_equipment as $equipment) {
            $type = $equipment['type'] ?? 'item';
            $normalized = $typeMap[$type] ?? 'items';
            $organized[$normalized][] = $equipment;
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
        
        foreach ($this->character->selected_domain_cards as $card) {
            $ability_key = $card['ability_key'] ?? null;
            $domain = $card['domain'] ?? null;
            
            if ($ability_key && isset($this->game_data['abilities'][$ability_key])) {
                $ability_data = $this->game_data['abilities'][$ability_key];
                $domain_cards[] = array_merge($card, ['ability_data' => $ability_data]);
            } else {
                $domain_cards[] = $card;
            }
        }

        return $domain_cards;
    }

    public function saveCharacterState(array $state): void
    {
        if (!$this->can_edit) {
            return;
        }

        // Find the character in the database
        $character = Character::where('character_key', $this->character_key)->first();
        
        if (!$character) {
            return;
        }

        // Get existing character_data or initialize empty array
        $characterData = $character->character_data ?? [];
        
        // Update the interactive_state section
        $characterData['interactive_state'] = $state;
        
        // Save to database
        $character->update(['character_data' => $characterData]);
    }

    public function getCharacterState(): ?array
    {
        // Find the character in the database
        $character = Character::where('character_key', $this->character_key)->first();
        
        if (!$character) {
            return null;
        }

        // Return the interactive_state from character_data
        return $character->character_data['interactive_state'] ?? null;
    }

    public function render()
    {
        return view('livewire.character-viewer', [
            'character' => $this->character,
            'pronouns' => $this->pronouns,
            'game_data' => $this->game_data,
            'computed_stats' => $this->getComputedStats(),
            'class_data' => $this->getClassData(),
            'subclass_data' => $this->getSubclassData(),
            'ancestry_data' => $this->getAncestryData(),
            'community_data' => $this->getCommunityData(),
            'organized_equipment' => $this->getOrganizedEquipment(),
            'domain_card_details' => $this->getDomainCardDetails(),
        ]);
    }
}
