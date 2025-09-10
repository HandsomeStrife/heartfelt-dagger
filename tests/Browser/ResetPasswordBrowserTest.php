<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('user can reset password with valid token', function () {
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

    visit("/reset-password/{$token}?email=test%40example.com")
        ->assertSee('Choose New Password')
        ->type('#password', 'new-password')
        ->type('#password_confirmation', 'new-password')
        ->click('Reset Password')
        ->assertPathIs('/dashboard');

    // Verify password was changed and user is logged in
    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
    expect(auth()->user()->id)->toBe($user->id);
});

test('user sees error for invalid reset token', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    visit('/reset-password/invalid-token?email=test@example.com')
        ->assertSee('Choose New Password')
        ->type('#password', 'new-password')
        ->type('#password_confirmation', 'new-password')
        ->click('Reset Password')
        ->assertSee('This password reset token is invalid');
});

test('user can navigate back to login from reset password', function () {
    $token = Str::random(64);

    visit("/reset-password/{$token}?email=test@example.com")
        ->assertSee('Back to login')
        ->click('Back to login')
        ->assertPathIs('/login')
        ->assertSee('Enter the Realm');
});

test('reset password form validates password requirements in browser', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Str::random(64);

    DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    visit("/reset-password/{$token}?email=test@example.com")
        ->type('#password', '12345')
        ->type('#password_confirmation', '12345')
        ->click('Reset Password')
        ->assertSee('The password field must be at least 6 characters');
});
