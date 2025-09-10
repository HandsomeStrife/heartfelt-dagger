<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Domain\User\Actions\ResetPasswordAction;
use Domain\User\Data\ResetPasswordData;
use Domain\User\Data\UserData;
use Domain\User\Repositories\UserRepository;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ResetPasswordForm extends Form
{
    public string $token = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:6|confirmed')]
    public string $password = '';

    #[Validate('required')]
    public string $password_confirmation = '';

    public function resetPassword(): ?UserData
    {
        $this->validate();

        $reset_data = ResetPasswordData::from([
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        return (new ResetPasswordAction(
            user_repository: app(UserRepository::class)
        ))->execute($reset_data);
    }

    public function resetForm(): void
    {
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
    }
}
