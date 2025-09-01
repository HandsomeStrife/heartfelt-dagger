<?php

declare(strict_types=1);

use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('guest sees Characters and Tools dropdowns and fake profile', function () {
    $page = visit('/');

    // Characters dropdown
    $page->click('[data-testid="nav-characters"]')
        ->assertSee('Your Characters')
        ->assertSee('Character Builder');

    // Tools dropdown
    $page->click('[data-testid="nav-tools"]')
        ->assertSee('Visual Range Checker');

    // Profile dropdown
    $page->click('[data-testid="nav-profile"]')
        ->assertSee('Login')
        ->assertSee('Register');
});

test('authenticated sees Campaigns, Characters/Tools dropdowns and profile options', function () {
    $user = User::factory()->create();
    actingAs($user);

    $page = visit('/');

    $page->assertSee('Campaigns');

    // Characters dropdown
    $page->click('[data-testid="nav-characters"]')
        ->assertSee('Your Characters')
        ->assertSee('Character Builder');

    // Tools dropdown
    $page->click('[data-testid="nav-tools"]')
        ->assertSee('Visual Range Checker');

    // Profile dropdown
    $page->click('[data-testid="nav-profile"]')
        ->assertSee('Dashboard')
        ->assertSee('Rooms')
        ->assertSee('Discord')
        ->assertSee('Logout');
});


