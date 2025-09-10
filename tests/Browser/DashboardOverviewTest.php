<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\Character\Models\Character;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;

use function Pest\Laravel\actingAs;

test('dashboard shows recent characters section', function () {
    $user = User::factory()->create();
    actingAs($user);

    visit('/dashboard')
        ->assertSee('Characters')
        ->assertSee('No characters yet')
        ->assertSee('Create your first character')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');

test('dashboard shows user characters when available', function () {
    $user = User::factory()->create();
    
    // Create a test character
    $character = Character::factory()->for($user)->create([
        'name' => 'Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
        'level' => 3,
    ]);
    
    actingAs($user);

    visit('/dashboard')
        ->assertSee('Characters')
        ->assertSee('Test Character')
        ->assertSee('Warrior')
        ->assertSee('Human')
        ->assertSee('Level 3')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');

test('dashboard shows recent campaigns section', function () {
    $user = User::factory()->create();
    actingAs($user);

    visit('/dashboard')
        ->assertSee('Campaigns')
        ->assertSee('No campaigns yet')
        ->assertSee('Create your first campaign')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');

test('dashboard shows user campaigns when available', function () {
    $user = User::factory()->create();
    
    // Create a test campaign
    $campaign = Campaign::factory()->for($user, 'creator')->create([
        'name' => 'Test Campaign',
    ]);
    
    actingAs($user);

    visit('/dashboard')
        ->assertSee('Campaigns')
        ->assertSee('Test Campaign')
        ->assertSee('Created by you')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');

test('dashboard shows recent rooms section', function () {
    $user = User::factory()->create();
    actingAs($user);

    visit('/dashboard')
        ->assertSee('Rooms')
        ->assertSee('No rooms yet')
        ->assertSee('Create your first room')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');

test('dashboard shows user rooms when available', function () {
    $user = User::factory()->create();
    
    // Create a test room
    $room = Room::factory()->for($user, 'creator')->create([
        'name' => 'Test Room',
    ]);
    
    actingAs($user);

    visit('/dashboard')
        ->assertSee('Rooms')
        ->assertSee('Test Room')
        ->assertSee('Created by you')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');

test('dashboard shows see more links when user has 3 or more items', function () {
    $user = User::factory()->create();
    
    // Create 3 characters
    Character::factory()->for($user)->count(3)->create();
    
    // Create 3 campaigns
    Campaign::factory()->for($user, 'creator')->count(3)->create();
    
    // Create 3 rooms
    Room::factory()->for($user, 'creator')->count(3)->create();
    
    actingAs($user);

    visit('/dashboard')
        ->assertSee('See all characters')
        ->assertSee('See all campaigns')
        ->assertSee('See all rooms')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');

test('dashboard three-column layout classes are present', function () {
    $user = User::factory()->create();
    actingAs($user);

    visit('/dashboard')
        ->assertPresent('[class*="grid-cols-1"]')
        ->assertPresent('[class*="md:grid-cols-3"]')
        ->assertNoJavaScriptErrors();
})->group('dashboard', 'browser');
