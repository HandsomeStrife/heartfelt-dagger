<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterNotes;
use Domain\User\Models\User;

class SaveCharacterNotesAction
{
    /**
     * Save or update character notes for a character and user
     */
    public function execute(Character $character, User $user, string $notes): CharacterNotes
    {
        return CharacterNotes::updateNotesForCharacterAndUser($character, $user, $notes);
    }
}
