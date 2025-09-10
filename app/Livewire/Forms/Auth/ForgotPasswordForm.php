<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Domain\User\Actions\SendPasswordResetAction;
use Domain\User\Data\PasswordResetRequestData;
use Domain\User\Repositories\UserRepository;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ForgotPasswordForm extends Form
{
    #[Validate('required|email')]
    public string $email = '';

    public function sendResetLink(): bool
    {
        $this->validate();

        $request_data = PasswordResetRequestData::from([
            'email' => $this->email,
        ]);

        return (new SendPasswordResetAction(
            user_repository: app(UserRepository::class)
        ))->execute($request_data);
    }

    public function resetForm(): void
    {
        $this->email = '';
        $this->resetValidation();
    }
}
