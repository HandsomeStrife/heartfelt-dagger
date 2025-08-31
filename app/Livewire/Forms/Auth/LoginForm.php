<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Domain\User\Actions\AuthenticateUserAction;
use Domain\User\Data\LoginUserData;
use Domain\User\Data\UserData;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:6')]
    public string $password = '';

    public bool $remember = false;

    public array $character_keys = [];

    public function authenticate(): ?UserData
    {
        $this->validate();

        $login_data = LoginUserData::from([
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ]);

        return (new AuthenticateUserAction(
            user_repository: app(\Domain\User\Repositories\UserRepository::class),
            associate_characters_action: app(\Domain\Character\Actions\AssociateCharactersWithUserAction::class)
        ))->execute($login_data, $this->character_keys);
    }

    public function resetForm(): void
    {
        $this->email = '';
        $this->password = '';
        $this->remember = false;
        $this->resetValidation();
    }
}
