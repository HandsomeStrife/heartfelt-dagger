<?php

declare(strict_types=1);

namespace Domain\User\Repositories;

use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class UserRepository
{
    public function findById(int $id): ?UserData
    {
        $user = User::find($id);

        return $user ? UserData::from($user) : null;
    }

    public function findByEmail(string $email): ?UserData
    {
        $user = User::where('email', $email)->first();

        return $user ? UserData::from($user) : null;
    }

    public function findByUsername(string $username): ?UserData
    {
        $user = User::where('username', $username)->first();

        return $user ? UserData::from($user) : null;
    }

    /**
     * @return Collection<UserData>
     */
    public function getAll(): Collection
    {
        return User::all()->map(fn ($user) => UserData::from($user));
    }
}
