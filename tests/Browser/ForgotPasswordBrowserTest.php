<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('user can access forgot password page from login', function () {
    visit('/login')
        ->assertSee('Forgot your password?')
        ->click('Forgot your password?')
        ->assertPathIs('/forgot-password')
        ->assertSee('Forgot Your Password?')
        ->assertSee('Enter your email address');
});

test('user can submit forgot password form', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    visit('/forgot-password')
        ->type('#email', 'test@example.com')
        ->click('Send Reset Link')
        ->assertSee('Email Sent!')
        ->assertSee('We\'ve sent a password reset link');

    Notification::assertSentTo($user, PasswordResetNotification::class);
});

test('user can try again after email sent', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    visit('/forgot-password')
        ->type('#email', 'test@example.com')
        ->click('Send Reset Link')
        ->assertSee('Email Sent!')
        ->click('try again')
        ->assertSee('Forgot Your Password?')
        ->assertDontSee('Email Sent!');
});

test('user can navigate back to login from forgot password', function () {
    visit('/forgot-password')
        ->assertSee('Back to login')
        ->click('Back to login')
        ->assertPathIs('/login')
        ->assertSee('Enter the Realm');
});
