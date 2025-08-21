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

    public function authenticate(): ?UserData
    {
        $this->validate();

        $login_data = LoginUserData::from([
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ]);

        return (new AuthenticateUserAction(
            user_repository: app(\Domain\User\Repositories\UserRepository::class)
        ))->execute($login_data);
    }

    public function resetForm(): void
    {
        $this->email = '';
        $this->password = '';
        $this->remember = false;
        $this->resetValidation();
    }
}
