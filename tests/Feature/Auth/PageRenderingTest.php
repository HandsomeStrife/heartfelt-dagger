<?php

declare(strict_types=1);
use PHPUnit\Framework\Attributes\Test;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login page renders successfully', function () {
    $response = get('/login');

    $response->assertStatus(200)
            ->assertSee('HeartfeltDagger')
            ->assertSee('Enter the Realm')
            ->assertSee('Email')
            ->assertSee('Password')
            ->assertSee('Enter the Realm')
            ->assertSee('Create your legend');
});
test('register page renders successfully', function () {
    $response = get('/register');

    $response->assertStatus(200)
            ->assertSee('HeartfeltDagger')
            ->assertSee('Join the Adventure')
            ->assertSee('Username')
            ->assertSee('Email')
            ->assertSee('Password')
            ->assertSee('Confirm Password')
            ->assertSee('Begin Adventure')
            ->assertSee('Enter the realm');
});
test('login page contains proper form elements', function () {
    $response = get('/login');

    $response->assertStatus(200)
            ->assertSee('wire:submit="login"', false)
            ->assertSee('wire:model="form.email"', false)
            ->assertSee('wire:model="form.password"', false)
            ->assertSee('wire:model="form.remember"', false)
            ->assertSee('type="email"', false)
            ->assertSee('type="password"', false)
            ->assertSee('type="checkbox"', false);
});
test('register page contains proper form elements', function () {
    $response = get('/register');

    $response->assertStatus(200)
            ->assertSee('wire:submit="register"', false)
            ->assertSee('wire:model="form.username"', false)
            ->assertSee('wire:model="form.email"', false)
            ->assertSee('wire:model="form.password"', false)
            ->assertSee('wire:model="form.password_confirmation"', false)
            ->assertSee('type="text"', false)
            ->assertSee('type="email"', false)
            ->assertSee('type="password"', false);
});
test('login page has proper navigation links', function () {
    $response = get('/login');

    $response->assertStatus(200)
            ->assertSee('href="/register"', false)
            ->assertSee('Create your legend');
});
test('register page has proper navigation links', function () {
    $response = get('/register');

    $response->assertStatus(200)
            ->assertSee('href="/login"', false)
            ->assertSee('Enter the realm');
});
test('auth pages have proper css classes', function () {
    $loginResponse = get('/login');
    $registerResponse = get('/register');

    // Check for body background
    $loginResponse->assertSee('bg-slate-900', false);
    $registerResponse->assertSee('bg-slate-900', false);

    // Check for form styling
    $loginResponse->assertSee('bg-gradient-to-br from-slate-800 to-slate-900', false);
    $registerResponse->assertSee('bg-gradient-to-br from-slate-800 to-slate-900', false);

    // Check for button styling
    $loginResponse->assertSee('bg-gradient-to-r from-amber-500 to-yellow-500', false);
    $registerResponse->assertSee('bg-gradient-to-r from-amber-500 to-yellow-500', false);
});
test('auth pages include decorative elements', function () {
    $loginResponse = get('/login');
    $registerResponse = get('/register');

    // Check for SVG stars
    $starPath = 'M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z';

    $loginResponse->assertSee($starPath, false);
    $registerResponse->assertSee($starPath, false);
});
test('auth pages use correct fonts', function () {
    $loginResponse = get('/login');
    $registerResponse = get('/register');

    // Check for fantasy font usage
    $loginResponse->assertSee('font-federant', false);
    $registerResponse->assertSee('font-federant', false);

    // Check for roboto font usage
    $loginResponse->assertSee('font-roboto', false);
    $registerResponse->assertSee('font-roboto', false);
});
test('auth pages have loading states', function () {
    $loginResponse = get('/login');
    $registerResponse = get('/register');

    // Check for wire:loading directives
    $loginResponse->assertSee('wire:loading.remove', false)
                ->assertSee('wire:loading', false)
                ->assertSee('wire:loading.attr="disabled"', false);

    $registerResponse->assertSee('wire:loading.remove', false)
                    ->assertSee('wire:loading', false)
                    ->assertSee('wire:loading.attr="disabled"', false);

    // Check for loading spinner
    $loginResponse->assertSee('animate-spin', false);
    $registerResponse->assertSee('animate-spin', false);
});
