<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\User\Data\RegisterUserData;
use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function execute(RegisterUserData $register_data): UserData
    {
        $user = User::create([
            'username' => $register_data->username,
            'email' => $register_data->email,
            'password' => Hash::make($register_data->password),
        ]);

        return UserData::from($user);
    }
}
