<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\LoginForm;
use Livewire\Component;

class Login extends Component
{
    public LoginForm $form;

    public function login()
    {
        $user_data = $this->form->authenticate();

        if ($user_data) {
            // Dispatch event to clear localStorage
            $this->dispatch('auth-success');
            return $this->redirect('/dashboard');
        }

        $this->addError('form.email', 'The provided credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.app');
    }
}
