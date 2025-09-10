<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\ForgotPasswordForm;
use Livewire\Component;

class ForgotPassword extends Component
{
    public ForgotPasswordForm $form;

    public bool $email_sent = false;

    public function sendResetLink()
    {
        $success = $this->form->sendResetLink();

        if ($success) {
            $this->email_sent = true;
            $this->form->resetForm();
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('components.layouts.app');
    }
}
