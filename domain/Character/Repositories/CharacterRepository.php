<?php

declare(strict_types=1);

namespace Domain\Character\Repositories;

use Domain\Character\Data\CharacterData;
use Domain\Character\Data\CharacterStatusData;
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

        if (! $character) {
            return null;
        }

        return CharacterData::from($character);
    }

    /**
     * Find a character by character key
     */
    public function findByKey(string $key): ?CharacterData
    {
        $character = Character::where('character_key', $key)->first();

        if (! $character) {
            return null;
        }

        return CharacterData::from($character);
    }

    /**
     * Find a character by public key (for viewer)
     */
    public function findByPublicKey(string $public_key): ?CharacterData
    {
        $character = Character::where('public_key', $public_key)->first();

        if (! $character) {
            return null;
        }

        return CharacterData::from($character);
    }

    /**
     * Find a character with status by character key
     */
    public function findByKeyWithStatus(string $key): ?array
    {
        $character = Character::with('status')->where('character_key', $key)->first();

        if (! $character) {
            return null;
        }

        $character_data = CharacterData::from($character);
        $status_data = $character->status ? CharacterStatusData::fromModel($character->status) : null;

        return [
            'character' => $character_data,
            'status' => $status_data,
        ];
    }

    /**
     * Find a character with status by public key (for viewer)
     */
    public function findByPublicKeyWithStatus(string $public_key): ?array
    {
        $character = Character::with('status')->where('public_key', $public_key)->first();

        if (! $character) {
            return null;
        }

        $character_data = CharacterData::from($character);
        $status_data = $character->status ? CharacterStatusData::fromModel($character->status) : null;

        return [
            'character' => $character_data,
            'status' => $status_data,
        ];
    }

    /**
     * Get characters by user with filtering options
     */
    public function getByUserWithFilters(User $user, array $filters = []): Collection
    {
        $query = Character::where('user_id', $user->id);

        if (isset($filters['class'])) {
            $query->where('class', $filters['class']);
        }

        if (isset($filters['ancestry'])) {
            $query->where('ancestry', $filters['ancestry']);
        }

        if (isset($filters['completed']) && $filters['completed']) {
            $query->whereNotNull('class')
                ->whereNotNull('ancestry')
                ->whereNotNull('community');
        }

        $characters = $query->orderBy('updated_at', 'desc')->get();

        return $characters->map(fn ($character) => CharacterData::from($character));
    }
}
