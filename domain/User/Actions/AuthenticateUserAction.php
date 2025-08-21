<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\Character\Actions\AssociateCharactersWithUserAction;
use Domain\User\Data\LoginUserData;
use Domain\User\Data\UserData;
use Domain\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticateUserAction
{
    public function __construct(
        private UserRepository $user_repository,
        private AssociateCharactersWithUserAction $associate_characters_action
    ) {}

    public function execute(LoginUserData $login_data, array $character_keys = []): ?UserData
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

        // Associate any anonymous characters with this user
        if (! empty($character_keys)) {
            $this->associate_characters_action->execute($user, $character_keys);
        }

        return $user_data;
    }
}
