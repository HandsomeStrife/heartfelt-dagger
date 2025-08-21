<?php

declare(strict_types=1);

namespace Domain\Character\Repositories;

use Domain\Character\Data\CharacterData;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class CharacterRepository
{
    /**
     * Get all characters belonging to a user
     */
    public function getByUser(User $user): Collection
    {
        $characters = Character::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return $characters->map(fn ($character) => CharacterData::from($character));
    }

    /**
     * Find a character by ID
     */
    public function findById(int $id): ?CharacterData
    {
        $character = Character::find($id);

        if (!$character) {
            return null;
        }

        return CharacterData::from($character);
    }

    /**
     * Find a character by key
     */
    public function findByKey(string $key): ?CharacterData
    {
        $character = Character::where('character_key', $key)->first();

        if (!$character) {
            return null;
        }

        return CharacterData::from($character);
    }

    /**
     * Get characters by user with filtering options
     */
    public function getByUserWithFilters(User $user, array $filters = []): Collection
    {
        $query = Character::where('user_id', $user->id);

        if (isset($filters['class'])) {
            $query->where('selected_class', $filters['class']);
        }

        if (isset($filters['ancestry'])) {
            $query->where('selected_ancestry', $filters['ancestry']);
        }

        if (isset($filters['completed']) && $filters['completed']) {
            $query->whereNotNull('selected_class')
                  ->whereNotNull('selected_ancestry')
                  ->whereNotNull('selected_community');
        }

        $characters = $query->orderBy('updated_at', 'desc')->get();

        return $characters->map(fn ($character) => CharacterData::from($character));
    }
}
