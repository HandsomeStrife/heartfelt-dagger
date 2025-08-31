<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('join page shows character selection dropdown when user has characters', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    // Create some characters for the user
    Character::factory()->count(2)->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'Warrior',
    ]);

    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertSee('Character Selection');
    $response->assertSee('Use Existing Character');
    $response->assertSee('Select an existing character');
    $response->assertSee('Create temporary character'); // option in dropdown
    $response->assertSee('Test Character (Warrior)'); // character in dropdown
});

test('join page shows simplified form when user has no characters', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    // User has no characters
    
    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertSee('Create Temporary Character');
    $response->assertDontSee('Character Selection');
    $response->assertDontSee('Use Existing Character');
    $response->assertDontSee('Select an existing character'); // no dropdown needed
});

test('join page shows temporary character form for unauthenticated users', function () {
    $room = Room::factory()->passwordless()->create();
    
    $response = get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertSee('Create Temporary Character');
    $response->assertDontSee('Character Selection');
    $response->assertDontSee('Use Existing Character');
    $response->assertDontSee('Select an existing character');
});

test('join page temporary character fields are enabled when user has no characters', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    // User has no characters
    
    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    
    // Check that the temporary character fields are not disabled
    $content = $response->getContent();
    expect($content)->toContain('name="character_name"');
    expect($content)->toContain('name="character_class"');
    
    // The fields should not have 'disabled' attribute when no characters exist
    expect($content)->not->toContain('id="character_name".*disabled');
    expect($content)->not->toContain('id="character_class".*disabled');
});

test('join page temporary character fields are disabled when user has characters', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    // Create a character for the user
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'Warrior',
    ]);

    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    
    // The temporary character fields should be disabled when user has existing characters
    $content = $response->getContent();
    expect($content)->toContain('id="character_name"');
    expect($content)->toContain('id="character_class"');
    expect($content)->toContain('disabled');
});
