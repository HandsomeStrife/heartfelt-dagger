<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('forgot password page renders correctly', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
    $response->assertSee('Forgot Your Password?');
    $response->assertSee('Enter your email address');
});

test('password reset link is sent for valid email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'We have emailed your password reset link!');

    // Check that token was created
    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);

    // Check that notification was sent
    Notification::assertSentTo($user, PasswordResetNotification::class);
});

test('password reset always returns success even for invalid email', function () {
    Notification::fake();

    $response = $this->post('/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'We have emailed your password reset link!');

    // Check that no token was created
    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'nonexistent@example.com',
    ]);

    // Check that no notification was sent
    Notification::assertNothingSent();
});

test('forgot password form validates email requirement', function () {
    $response = $this->post('/forgot-password', [
        'email' => '',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('forgot password form validates email format', function () {
    $response = $this->post('/forgot-password', [
        'email' => 'invalid-email',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('existing password reset tokens are deleted when new one is created', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Create an existing token
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => 'old-token',
        'created_at' => now(),
    ]);

    $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    // Only one token should exist
    $tokens = DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->get();

    expect($tokens)->toHaveCount(1);
    expect($tokens->first()->token)->not()->toBe('old-token');
});
