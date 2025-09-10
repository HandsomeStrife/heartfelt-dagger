<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Data\CharacterStatusData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterStatus;

class SaveCharacterStatusAction
{
    /**
     * Save character interactive status to database
     */
    public function execute(string $character_key, CharacterStatusData $status_data): CharacterStatusData
    {
        // Find the character
        $character = Character::where('character_key', $character_key)->first();

        if (! $character) {
            throw new \InvalidArgumentException("Character with key '{$character_key}' not found");
        }

        // Get or create the status record
        $status = $character->status()->first();

        if (! $status) {
            // Create new status record
            $status = CharacterStatus::create([
                'character_id' => $character->id,
                'hit_points' => $status_data->hit_points,
                'stress' => $status_data->stress,
                'hope' => $status_data->hope,
                'armor_slots' => $status_data->armor_slots,
                'gold_handfuls' => $status_data->gold_handfuls,
                'gold_bags' => $status_data->gold_bags,
                'gold_chest' => $status_data->gold_chest,
            ]);
        } else {
            // Update existing status record
            $status->update([
                'hit_points' => $status_data->hit_points,
                'stress' => $status_data->stress,
                'hope' => $status_data->hope,
                'armor_slots' => $status_data->armor_slots,
                'gold_handfuls' => $status_data->gold_handfuls,
                'gold_bags' => $status_data->gold_bags,
                'gold_chest' => $status_data->gold_chest,
            ]);
        }

        return CharacterStatusData::fromModel($status->fresh());
    }
}
