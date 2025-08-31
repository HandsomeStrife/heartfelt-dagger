<?php

declare(strict_types=1);

test('login page renders correctly in browser', function () {
    $page = visit('/login');
    
    $page
                ->assertSee('HeartfeltDagger')
                ->assertSee('Enter the Realm')
                ->assertPresent('form')
                ->assertPresent('input[type="email"]')
                ->assertPresent('input[type="password"]')
                ->assertPresent('input[type="checkbox"]')
                ->assertPresent('button[type="submit"]')
                ->assertSee('Create your legend');
});

test('register page renders correctly in browser', function () {
    $page = visit('/register');
    
    $page
                ->assertSee('HeartfeltDagger')
                ->assertSee('Join the Adventure')
                ->assertPresent('form')
                ->assertPresent('input[type="text"]')
                ->assertPresent('input[type="email"]')
                ->assertPresent('input[type="password"]')
                ->assertPresent('button[type="submit"]')
                ->assertSee('Enter the realm');
});

test('login form fields are interactive', function () {
    $page = visit('/login');
    
    $page
                ->type('#email', 'test@example.com')
                ->type('#password', 'password123')
                ->check('#remember')
                ->assertInputValue('#email', 'test@example.com')
                ->assertInputValue('#password', 'password123')
                ->assertChecked('#remember');
});

test('register form fields are interactive', function () {
    $page = visit('/register');
    
    $page
                ->type('#username', 'testuser')
                ->type('#email', 'test@example.com')
                ->type('#password', 'password123')
                ->type('#password_confirmation', 'password123')
                ->assertInputValue('#username', 'testuser')
                ->assertInputValue('#email', 'test@example.com')
                ->assertInputValue('#password', 'password123')
                ->assertInputValue('#password_confirmation', 'password123');
});

test('auth pages have proper styling', function () {
    $page = visit('/login');
    
    $page
                ->assertPresent('svg')
                ->visit('/register')
                ->assertPresent('svg');
});