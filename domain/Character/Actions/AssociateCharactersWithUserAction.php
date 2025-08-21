<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class AssociateCharactersWithUserAction
{
    public function execute(User $user, array $character_keys): int
    {
        if (empty($character_keys)) {
            return 0;
        }

        return DB::transaction(function () use ($user, $character_keys) {
            // Find characters with the provided keys that have null user_id
            $characters = Character::whereIn('character_key', $character_keys)
                ->whereNull('user_id')
                ->get();

            if ($characters->isEmpty()) {
                return 0;
            }

            // Associate the characters with the user
            $updated_count = 0;
            foreach ($characters as $character) {
                $character->update(['user_id' => $user->id]);
                $updated_count++;
            }

            return $updated_count;
        });
    }
}
