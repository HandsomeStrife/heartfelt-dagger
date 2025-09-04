<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Domain\Character\Models\Character;
use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;

class DebugSaveFlow extends Command
{
    protected $signature = 'debug:save-flow {character_key}';
    protected $description = 'Debug the save flow for character equipment';

    public function handle()
    {
        $character_key = $this->argument('character_key');
        
        $this->info("=== Debug Save Flow for Character: {$character_key} ===");
        
        // Load character from database
        $character = Character::where('character_key', $character_key)->first();
        if (!$character) {
            $this->error("Character not found: {$character_key}");
            return 1;
        }
        
        $this->info("Character found: {$character->name} (ID: {$character->id})");
        
        // Load equipment from CharacterEquipment table
        $equipment_records = $character->equipment()->get();
        $this->info("Equipment in database: " . $equipment_records->count() . " items");
        foreach ($equipment_records as $eq) {
            $this->line("  - {$eq->equipment_type}: {$eq->equipment_key}");
        }
        
        // Load character using LoadCharacterAction
        $load_action = new LoadCharacterAction();
        $character_data = $load_action->execute($character_key);
        
        $this->info("Selected equipment from LoadCharacterAction: " . count($character_data->selected_equipment) . " items");
        foreach ($character_data->selected_equipment as $eq) {
            $this->line("  - {$eq['type']}: {$eq['key']}");
        }
        
        // Check character_data JSON field
        $json_data = $character->character_data ?? [];
        $this->info("Character data JSON field contents:");
        $this->line(json_encode($json_data, JSON_PRETTY_PRINT));
        
        return 0;
    }
}