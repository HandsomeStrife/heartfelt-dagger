<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Data\CharacterStatusData;
use Domain\Character\Models\Character;

class LoadCharacterStatusAction
{
    /**
     * Load character interactive status from database
     * If no status exists, create default based on computed stats
     */
    public function execute(string $character_key, array $computed_stats): CharacterStatusData
    {
        // Find the character
        $character = Character::where('character_key', $character_key)->first();
        
        if (!$character) {
            throw new \InvalidArgumentException("Character with key '{$character_key}' not found");
        }

        // Try to get existing status
        $status = $character->status()->first();
        
        if ($status) {
            // Adjust status arrays to match current computed stats (in case stats changed)
            $status->adjustToComputedStats($computed_stats);
            $status->save();
            
            return CharacterStatusData::fromModel($status);
        }

        // No status exists, create default
        return CharacterStatusData::createDefault($character->id, $computed_stats);
    }
}


