<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\User\Data\LoginUserData;
use Domain\User\Data\UserData;
use Domain\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticateUserAction
{
    public function __construct(
        private UserRepository $user_repository
    ) {}

    public function execute(LoginUserData $login_data): ?UserData
    {
        $user_data = $this->user_repository->findByEmail($login_data->email);

        if (! $user_data) {
            return null;
        }

        $user = Auth::getProvider()->retrieveById($user_data->id);

        if (! $user || ! Hash::check($login_data->password, $user->password)) {
            return null;
        }

        Auth::login($user, $login_data->remember);
        session()->regenerate();

        return $user_data;
    }
}
