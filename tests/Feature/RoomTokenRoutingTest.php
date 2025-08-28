<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('room URLs use invite_code tokens instead of database IDs', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // Verify room has an 8-character invite_code
    expect($room->invite_code)->toHaveLength(8);
    expect($room->invite_code)->toMatch('/^[A-Z0-9]{8}$/');

    // Test room show URL uses invite_code, not ID
    $showUrl = route('rooms.show', $room);
    expect($showUrl)->toContain($room->invite_code);
    expect($showUrl)->not->toContain((string) $room->id);

    // Test room session URL uses invite_code, not ID
    $sessionUrl = route('rooms.session', $room);
    expect($sessionUrl)->toContain($room->invite_code);
    expect($sessionUrl)->not->toContain((string) $room->id);

    // Verify the URLs are accessible
    $response = actingAs($user)->get($showUrl);
    $response->assertOk();

    // Verify room can be accessed by invite_code
    $foundRoom = Room::where('invite_code', $room->invite_code)->first();
    expect($foundRoom->id)->toBe($room->id);
});

test('room creation redirects to token-based URLs', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('rooms.store'), [
        'name' => 'Token Test Room',
        'description' => 'Testing token-based URLs',
        'guest_count' => 4,
    ]);

    $room = Room::where('name', 'Token Test Room')->first();
    
    // Verify redirect uses invite_code, not ID
    $expectedUrl = route('rooms.show', $room);
    expect($expectedUrl)->toContain($room->invite_code);
    // URL should be in format /rooms/{invite_code}, not /rooms/{id}
    expect($expectedUrl)->toEndWith('/rooms/' . $room->invite_code);

    $response->assertRedirect($expectedUrl);
});

test('old ID-based URLs do not work', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // Try to access room using old ID-based URL pattern
    $oldStyleUrl = "/rooms/{$room->id}";
    
    $response = actingAs($user)->get($oldStyleUrl);
    
    // Should return 404 because route expects invite_code, not ID
    $response->assertNotFound();
});

test('room URLs are not predictable', function () {
    $user = User::factory()->create();
    
    // Create multiple rooms to test invite_code uniqueness
    $rooms = Room::factory()->count(5)->create(['creator_id' => $user->id]);
    
    $inviteCodes = $rooms->pluck('invite_code')->toArray();
    
    // All invite codes should be unique
    expect(array_unique($inviteCodes))->toHaveCount(5);
    
    // All should be 8 characters of A-Z and 0-9
    foreach ($inviteCodes as $code) {
        expect($code)->toHaveLength(8);
        expect($code)->toMatch('/^[A-Z0-9]{8}$/');
    }
    
    // Sequential room IDs should not result in sequential invite codes
    $rooms = $rooms->sortBy('id');
    $sortedCodes = $rooms->pluck('invite_code')->toArray();
    $alphabeticallySorted = $sortedCodes;
    sort($alphabeticallySorted);
    
    // Codes should not be in alphabetical order (randomness check)
    expect($sortedCodes)->not->toEqual($alphabeticallySorted);
});
