<?php

declare(strict_types=1);

use App\Livewire\Auth\ForgotPassword;
use Domain\User\Models\User;
use Domain\User\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('forgot password component renders correctly', function () {
    Livewire::test(ForgotPassword::class)
        ->assertStatus(200)
        ->assertSee('Forgot Your Password?')
        ->assertSee('Enter your email address');
});

test('forgot password component sends reset link', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    Livewire::test(ForgotPassword::class)
        ->set('form.email', 'test@example.com')
        ->call('sendResetLink')
        ->assertSet('email_sent', true);

    Notification::assertSentTo($user, PasswordResetNotification::class);
});

test('forgot password component validates email', function () {
    Livewire::test(ForgotPassword::class)
        ->set('form.email', '')
        ->call('sendResetLink')
        ->assertHasErrors(['form.email' => 'required']);

    Livewire::test(ForgotPassword::class)
        ->set('form.email', 'invalid-email')
        ->call('sendResetLink')
        ->assertHasErrors(['form.email' => 'email']);
});

test('forgot password component shows success message after sending', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    Livewire::test(ForgotPassword::class)
        ->set('form.email', 'test@example.com')
        ->call('sendResetLink')
        ->assertSet('email_sent', true)
        ->assertSee('Email Sent!')
        ->assertSee('sent a password reset link');
});

test('forgot password component can be reset to try again', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    Livewire::test(ForgotPassword::class)
        ->set('form.email', 'test@example.com')
        ->call('sendResetLink')
        ->assertSet('email_sent', true)
        ->set('email_sent', false)
        ->assertSee('Forgot Your Password?')
        ->assertDontSee('Email Sent!');
});
