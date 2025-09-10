<?php

declare(strict_types=1);

namespace App\Livewire\RoomSidebar;

use Domain\Character\Actions\LoadCharacterNotesAction;
use Domain\Character\Actions\LoadCharacterStatusAction;
use Domain\Character\Actions\SaveCharacterNotesAction;
use Domain\Character\Actions\SaveCharacterStatusAction;
use Domain\Character\Data\CharacterData;
use Domain\Character\Data\CharacterStatusData;
use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterRepository;
use Domain\Room\Data\RoomParticipantData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PlayerSidebar extends Component
{
    public ?RoomParticipantData $current_participant = null;

    public ?CharacterData $character = null;

    public ?CharacterStatusData $character_status = null;

    public array $game_data = [];

    public bool $can_edit = true;

    public string $character_notes = '';

    // Repository
    private CharacterRepository $character_repository;

    public function mount(?RoomParticipantData $currentParticipant): void
    {
        $this->current_participant = $currentParticipant;

        // Initialize repository
        $this->character_repository = new CharacterRepository;

        // Load character data if participant has a linked character
        if ($this->current_participant?->character_id) {
            $this->character = $this->character_repository->findById($this->current_participant->character_id);

            if ($this->character) {
                $this->loadCharacterStatus();
                $this->loadGameData();
                $this->loadCharacterNotes();
            }
        }
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
        $this->character_status = $load_action->execute($this->character->character_key, $computed_stats);
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
        if (! $this->character) {
            return [];
        }

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

    /**
     * Get selected class details
     */
    public function getClassData(): ?array
    {
        if (! $this->character || empty($this->character->class) || ! isset($this->game_data['classes'][$this->character->class])) {
            return null;
        }

        return $this->game_data['classes'][$this->character->class];
    }

    /**
     * Get all trait values formatted for display
     */
    public function getFormattedTraitValues(): array
    {
        if (! $this->character) {
            return [];
        }

        $trait_values = [];
        $trait_names = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];

        foreach ($trait_names as $trait) {
            $value = $this->character->traits->{$trait} ?? 0;
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
        if (! $this->character || empty($this->character->subclass) || ! isset($this->game_data['subclasses'][$this->character->subclass])) {
            return null;
        }

        return $this->game_data['subclasses'][$this->character->subclass];
    }

    /**
     * Get selected ancestry details
     */
    public function getAncestryData(): ?array
    {
        if (! $this->character || empty($this->character->ancestry) || ! isset($this->game_data['ancestries'][$this->character->ancestry])) {
            return null;
        }

        return $this->game_data['ancestries'][$this->character->ancestry];
    }

    /**
     * Get selected community details
     */
    public function getCommunityData(): ?array
    {
        if (! $this->character || empty($this->character->community) || ! isset($this->game_data['communities'][$this->character->community])) {
            return null;
        }

        return $this->game_data['communities'][$this->character->community];
    }

    /**
     * Get equipment data organized by type
     */
    public function getOrganizedEquipment(): array
    {
        if (! $this->character) {
            return [
                'weapons' => [],
                'armor' => [],
                'items' => [],
                'consumables' => [],
            ];
        }

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
     * Get domain card details with ability data
     */
    public function getDomainCardDetails(): array
    {
        if (! $this->character) {
            return [];
        }

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
            $this->character_status = $save_action->execute($this->character->character_key, $status_data);

        } catch (\Exception $e) {
            // Log error but don't break the UI
            \Log::error('Failed to save character state: '.$e->getMessage());
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

    public function loadCharacterNotes(): void
    {
        $user = Auth::user();
        if (! $user || ! $this->character) {
            return;
        }

        $character_model = Character::find($this->character->id);
        if (! $character_model) {
            return;
        }

        $load_action = new LoadCharacterNotesAction;
        $notes_record = $load_action->execute($character_model, $user);
        $this->character_notes = $notes_record->notes ?? '';
    }

    public function saveCharacterNotes(): void
    {
        $user = Auth::user();
        if (! $user || ! $this->character) {
            return;
        }

        $character_model = Character::find($this->character->id);
        if (! $character_model) {
            return;
        }

        $save_action = new SaveCharacterNotesAction;
        $save_action->execute($character_model, $user, $this->character_notes);

        // Dispatch a browser event to show success feedback
        $this->dispatch('character-notes-saved');
    }

    public function render()
    {
        if (! $this->character) {
            return view('livewire.room-sidebar.player-sidebar-empty');
        }

        $class_data = $this->getClassData();
        $computed_stats = $this->getComputedStats();

        return view('livewire.room-sidebar.player-sidebar', [
            'character' => $this->character,
            'character_status' => $this->character_status,
            'game_data' => $this->game_data,
            'computed_stats' => $computed_stats,
            'class_data' => $class_data,
            'subclass_data' => $this->getSubclassData(),
            'ancestry_data' => $this->getAncestryData(),
            'community_data' => $this->getCommunityData(),
            'organized_equipment' => $this->getOrganizedEquipment(),
            'domain_card_details' => $this->getDomainCardDetails(),
            'trait_values' => $this->getFormattedTraitValues(),
            'trait_info' => $this->getTraitInfo(),
        ]);
    }
}
