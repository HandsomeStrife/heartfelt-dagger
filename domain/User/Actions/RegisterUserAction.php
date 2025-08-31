<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\Character\Actions\AssociateCharactersWithUserAction;
use Domain\User\Data\RegisterUserData;
use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function __construct(
        private AssociateCharactersWithUserAction $associate_characters_action
    ) {}

    public function execute(RegisterUserData $register_data, array $character_keys = []): UserData
    {
        $user = User::create([
            'username' => $register_data->username,
            'email' => $register_data->email,
            'password' => Hash::make($register_data->password),
        ]);

        // Associate any anonymous characters with this user
        if (! empty($character_keys)) {
            $this->associate_characters_action->execute($user, $character_keys);
        }

        return UserData::from($user);
    }
}
