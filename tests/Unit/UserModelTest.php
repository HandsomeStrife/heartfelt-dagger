<?php

use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('user can be created', function () {
    $user = User::create([
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->username)->toEqual('testuser');
    expect($user->email)->toEqual('test@example.com');
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('user has fillable attributes', function () {
    $fillable = ['username', 'email', 'password'];
    $user = new User;

    expect($user->getFillable())->toEqual($fillable);
});

test('user has hidden attributes', function () {
    $hidden = ['password', 'remember_token'];
    $user = new User;

    expect($user->getHidden())->toEqual($hidden);
});

test('password is automatically hashed', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    expect($user->password)->not->toEqual('password123');
    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('email verified at is cast to datetime', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('user factory creates valid user', function () {
    $user = User::factory()->create();

    expect($user->username)->not->toBeNull();
    expect($user->email)->not->toBeNull();
    expect($user->password)->not->toBeNull();
    expect(str_contains($user->email, '@'))->toBeTrue();
});

test('user factory can override attributes', function () {
    $user = User::factory()->create([
        'username' => 'customuser',
        'email' => 'custom@example.com',
    ]);

    expect($user->username)->toEqual('customuser');
    expect($user->email)->toEqual('custom@example.com');
});

test('user model uses correct table', function () {
    $user = new User;
    expect($user->getTable())->toEqual('users');
});

test('user model has timestamps', function () {
    $user = User::factory()->create();

    expect($user->created_at)->not->toBeNull();
    expect($user->updated_at)->not->toBeNull();
});

test('user can be found by email', function () {
    $user = User::factory()->create([
        'email' => 'findme@example.com',
    ]);

    $foundUser = User::where('email', 'findme@example.com')->first();

    expect($foundUser->id)->toEqual($user->id);
});

test('user can be found by username', function () {
    $user = User::factory()->create([
        'username' => 'findmeuser',
    ]);

    $foundUser = User::where('username', 'findmeuser')->first();

    expect($foundUser->id)->toEqual($user->id);
});

test('remember token can be set', function () {
    $user = User::factory()->create();
    $token = 'test-remember-token';

    $user->remember_token = $token;
    $user->save();

    expect($user->fresh()->remember_token)->toEqual($token);
});

test('user attributes are properly cast', function () {
    $user = User::factory()->create();

    $casts = $user->getCasts();

    expect($casts)->toHaveKey('email_verified_at');
    expect($casts)->toHaveKey('password');
    expect($casts['email_verified_at'])->toEqual('datetime');
    expect($casts['password'])->toEqual('hashed');
});

test('user can be soft deleted if trait is added', function () {
    // This test assumes you might add soft deletes in the future
    $user = User::factory()->create();

    // For now, just test that delete works normally
    $userId = $user->id;
    $user->delete();

    expect(User::find($userId))->toBeNull();
});
