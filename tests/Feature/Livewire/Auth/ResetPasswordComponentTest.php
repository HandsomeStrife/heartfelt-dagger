<?php

declare(strict_types=1);

use App\Livewire\Auth\ResetPassword;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('reset password component renders correctly', function () {
    $token = Str::random(64);
    $email = 'test@example.com';

    Livewire::test(ResetPassword::class, ['token' => $token, 'email' => $email])
        ->assertStatus(200)
        ->assertSee('Choose New Password')
        ->assertSet('form.token', $token)
        ->assertSet('form.email', $email);
});

test('reset password component resets password successfully', function () {
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

    Livewire::test(ResetPassword::class, ['token' => $token, 'email' => 'test@example.com'])
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertRedirect('/dashboard');

    // Check password was updated
    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();

    // Check token was deleted
    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);
});

test('reset password component validates password requirements', function () {
    $token = Str::random(64);
    $email = 'test@example.com';

    User::factory()->create(['email' => $email]);

    DB::table('password_reset_tokens')->insert([
        'email' => $email,
        'token' => hash('sha256', $token),
        'created_at' => now(),
    ]);

    // Test short password
    Livewire::test(ResetPassword::class, ['token' => $token, 'email' => $email])
        ->set('form.password', '12345')
        ->set('form.password_confirmation', '12345')
        ->call('resetPassword')
        ->assertHasErrors(['form.password' => 'min']);

    // Test password confirmation mismatch
    Livewire::test(ResetPassword::class, ['token' => $token, 'email' => $email])
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'different-password')
        ->call('resetPassword')
        ->assertHasErrors(['form.password' => 'confirmed']);
});

test('reset password component fails with invalid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    Livewire::test(ResetPassword::class, ['token' => 'invalid-token', 'email' => 'test@example.com'])
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertHasErrors(['form.token']);
});

test('reset password component fails with expired token', function () {
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

    Livewire::test(ResetPassword::class, ['token' => $token, 'email' => 'test@example.com'])
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'new-password')
        ->call('resetPassword')
        ->assertHasErrors(['form.token']);
});
