<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('reset password page renders correctly with valid token', function () {
    $token = Str::random(64);
    $email = 'test@example.com';

    User::factory()->create(['email' => $email]);

    DB::table('password_reset_tokens')->insert([
        'email' => $email,
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->get("/reset-password/{$token}?email={$email}");

    $response->assertStatus(200);
    $response->assertSee('Choose New Password');
    $response->assertSee($email);
});

test('reset password page redirects for missing email', function () {
    $token = Str::random(64);

    $response = $this->get("/reset-password/{$token}");

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors(['email']);
});

test('password can be reset with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Str::random(64);

    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('status', 'Your password has been reset successfully!');

    // Check password was updated
    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();

    // Check token was deleted
    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);

    // Check user was logged in
    $this->assertAuthenticated();
    expect(auth()->user()->id)->toBe($user->id);
});

test('password reset fails with invalid token', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['token']);
    $this->assertGuest();
});

test('password reset fails with expired token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Str::random(64);

    // Create expired token (61 minutes ago)
    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now()->subMinutes(61),
    ]);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['token']);
    $this->assertGuest();
});

test('password reset validates password requirements', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Str::random(64);

    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    // Test short password
    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => '12345',
        'password_confirmation' => '12345',
    ]);

    $response->assertSessionHasErrors(['password']);

    // Test password confirmation mismatch
    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('password reset fails for non-existent user', function () {
    $token = Str::random(64);

    DB::table('password_reset_tokens')->insert([
        'email' => 'nonexistent@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => 'nonexistent@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['token']);
    $this->assertGuest();
});
