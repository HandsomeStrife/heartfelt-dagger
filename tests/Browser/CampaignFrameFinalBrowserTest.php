<?php

declare(strict_types=1);

test('login page renders correctly', function () {
    visit('/login')
        ->assertSee('Enter the Realm')
        ->assertPresent('#email')
        ->assertPresent('#password')
        ->assertPresent('[data-testid="login-submit-button"]');
})->group('browser');
