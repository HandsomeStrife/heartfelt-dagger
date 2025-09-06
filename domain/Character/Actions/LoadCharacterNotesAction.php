<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterNotes;
use Domain\User\Models\User;

class LoadCharacterNotesAction
{
    /**
     * Load character notes for a character and user
     */
    public function execute(Character $character, User $user): CharacterNotes
    {
        return CharacterNotes::getOrCreateForCharacterAndUser($character, $user);
    }
}
