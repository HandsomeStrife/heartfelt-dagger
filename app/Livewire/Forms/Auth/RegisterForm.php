<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Auth;

use Domain\User\Actions\RegisterUserAction;
use Domain\User\Data\RegisterUserData;
use Domain\User\Data\UserData;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RegisterForm extends Form
{
    #[Validate('required|string|max:255|unique:users')]
    public string $username = '';

    #[Validate('required|email|max:255|unique:users')]
    public string $email = '';

    #[Validate('required|min:6|confirmed')]
    public string $password = '';

    #[Validate('required')]
    public string $password_confirmation = '';

    public array $character_keys = [];

    public function register(): UserData
    {
        $this->validate();

        $register_data = RegisterUserData::from([
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        $user_data = (new RegisterUserAction(
            associate_characters_action: app(\Domain\Character\Actions\AssociateCharactersWithUserAction::class)
        ))->execute($register_data, $this->character_keys);

        // Log the user in automatically after registration
        $user = Auth::getProvider()->retrieveById($user_data->id);
        Auth::login($user);
        session()->regenerate();

        return $user_data;
    }

    public function resetForm(): void
    {
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
    }
}
