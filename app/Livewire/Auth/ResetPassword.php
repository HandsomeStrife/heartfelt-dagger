<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\ResetPasswordForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResetPassword extends Component
{
    public ResetPasswordForm $form;

    public function mount(string $token, string $email)
    {
        $this->form->token = $token;
        $this->form->email = $email;
    }

    public function resetPassword()
    {
        $user_data = $this->form->resetPassword();

        if ($user_data) {
            // Log the user in automatically after password reset
            $user = Auth::getProvider()->retrieveById($user_data->id);
            Auth::login($user);
            session()->regenerate();

            session()->flash('status', 'Your password has been reset successfully!');

            return $this->redirect('/dashboard');
        }

        $this->addError('form.token', 'This password reset token is invalid or has expired.');
    }

    public function render()
    {
        return view('livewire.auth.reset-password')
            ->layout('components.layouts.app');
    }
}
