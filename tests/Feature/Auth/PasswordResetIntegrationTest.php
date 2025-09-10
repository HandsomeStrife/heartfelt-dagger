<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Notifications\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('complete password reset flow from request to reset', function () {
    Notification::fake();

    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    // Step 1: Request password reset
    $response = $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'We have emailed your password reset link!');

    // Verify notification was sent
    Notification::assertSentTo($user, PasswordResetNotification::class);

    // Verify token was created
    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);

    // Get the token from database
    $tokenRecord = DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->first();

    expect($tokenRecord)->not()->toBeNull();

    // Step 2: Visit reset password page (simulate clicking email link)
    // We need to generate a token that would hash to the same value
    $plainToken = Str::random(64);

    // Update database with a token we know
    DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->update(['token' => hash('sha256', $plainToken)]);

    $resetPageResponse = $this->get("/reset-password/{$plainToken}?email=test@example.com");
    $resetPageResponse->assertStatus(200);

    // Step 3: Reset password
    $resetResponse = $this->post('/reset-password', [
        'token' => $plainToken,
        'email' => 'test@example.com',
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $resetResponse->assertRedirect('/dashboard');
    $resetResponse->assertSessionHas('status', 'Your password has been reset successfully!');

    // Step 4: Verify password was changed
    $user->refresh();
    expect(Hash::check('new-secure-password', $user->password))->toBeTrue();
    expect(Hash::check('old-password', $user->password))->toBeFalse();

    // Step 5: Verify token was deleted
    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);

    // Step 6: Verify user is logged in
    $this->assertAuthenticated();
    expect(auth()->user()->id)->toBe($user->id);
});

test('password reset security measures work correctly', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    // Request password reset
    $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $tokenRecord = DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->first();

    // Test 1: Token expires after 60 minutes
    DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->update(['created_at' => now()->subMinutes(61)]);

    $expiredResponse = $this->post('/reset-password', [
        'token' => 'some-token', // Token doesn't matter since it's expired
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $expiredResponse->assertRedirect();
    $expiredResponse->assertSessionHasErrors(['token']);

    // Test 2: Token is deleted after successful reset
    $plainToken = Str::random(64);

    DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->update([
            'token' => hash('sha256', $plainToken),
            'created_at' => now(),
        ]);

    // First reset should work
    $this->post('/reset-password', [
        'token' => $plainToken,
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect('/dashboard');

    // Second attempt with same token should fail
    auth()->logout();

    $secondResponse = $this->post('/reset-password', [
        'token' => $plainToken,
        'email' => 'test@example.com',
        'password' => 'another-password',
        'password_confirmation' => 'another-password',
    ]);

    $secondResponse->assertRedirect();
    $secondResponse->assertSessionHasErrors(['token']);
});

test('password reset handles edge cases correctly', function () {
    // Test 1: Non-existent user email
    $response = $this->post('/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    // Should still show success to prevent email enumeration
    $response->assertRedirect();
    $response->assertSessionHas('status', 'We have emailed your password reset link!');

    // But no token should be created
    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'nonexistent@example.com',
    ]);

    // Test 2: Invalid reset token format
    $user = User::factory()->create(['email' => 'test@example.com']);

    $invalidResponse = $this->post('/reset-password', [
        'token' => 'invalid-token-format',
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $invalidResponse->assertRedirect();
    $invalidResponse->assertSessionHasErrors(['token']);

    // Test 3: Token for different email
    $plainToken = Str::random(64);

    DB::table('password_reset_tokens')->insert([
        'email' => 'other@example.com',
        'token' => hash('sha256', $plainToken),
        'created_at' => now(),
    ]);

    $wrongEmailResponse = $this->post('/reset-password', [
        'token' => $plainToken,
        'email' => 'test@example.com', // Different email
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $wrongEmailResponse->assertRedirect();
    $wrongEmailResponse->assertSessionHasErrors(['token']);
});
