<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\RegisterForm;
use Livewire\Component;

class Register extends Component
{
    public RegisterForm $form;

    public function register()
    {
        $user_data = $this->form->register();

        // Dispatch event to clear localStorage
        $this->dispatch('auth-success');

        return $this->redirect('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.app');
    }
}
