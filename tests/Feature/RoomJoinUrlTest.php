<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('room join URL works with characters that have null fields', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    // Create characters with null fields that previously caused the error
    Character::factory()->count(3)->create([
        'user_id' => $user->id,
        'name' => null,
        'class' => null,
        'ancestry' => null,
        'community' => null,
    ]);

    // Also create one complete character to test both scenarios
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Complete Character',
        'class' => 'Wizard',
        'ancestry' => 'Elf',
        'community' => 'Loreborne',
    ]);

    // The join URL should load without errors
    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");

    $response->assertOk();
    $response->assertSee('Join Room');

    // Should show both unnamed characters (with defaults) and the complete character
    $response->assertSee('Unnamed Character');
    $response->assertSee('Complete Character');
    $response->assertSee('Warrior'); // Default class for unnamed characters
    $response->assertSee('Wizard'); // Class for complete character
});

test('unauthenticated user can access join URL without character loading errors', function () {
    $room = Room::factory()->passwordless()->create();

    // Unauthenticated user should be able to access join page
    $response = get("/rooms/join/{$room->invite_code}");

    $response->assertOk();
    $response->assertSee('Join Room');
    $response->assertSee('Create Temporary Character');
});

test('password-protected room join URL works with character errors fixed', function () {
    $user = User::factory()->create();
    $password = 'testpassword';
    $room = Room::factory()->create([
        'password' => bcrypt($password),
        'campaign_id' => null,
    ]);

    // Create character with null fields
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => null,
        'class' => null,
    ]);

    // Should work with password parameter
    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}?password={$password}");

    $response->assertOk();
    $response->assertSee('Join Room');
    $response->assertSee('Unnamed Character');
});
