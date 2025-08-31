<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('non-campaign rooms can be accessed without authentication', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'creator_id' => $creator->id,
        'campaign_id' => null, // Not attached to campaign
    ]);

    // Unauthenticated users can view non-campaign rooms
    $response = get("/rooms/{$room->invite_code}");
    $response->assertOk();
    
    // Should see room information
    $response->assertSee($room->name);
    $response->assertSee($room->description);
});

test('campaign rooms require authentication', function () {
    $creator = User::factory()->create();
    $campaign = \Domain\Campaign\Models\Campaign::factory()->create(['creator_id' => $creator->id]);
    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'campaign_id' => $campaign->id, // Attached to campaign
        'password' => null,
    ]);

    // Unauthenticated users cannot access campaign rooms
    $response = get("/rooms/{$room->invite_code}");
    $response->assertRedirect('/login');
    $response->assertSessionHas('error', 'Please log in to access this campaign room.');
});

test('password-protected rooms can use URL password parameter', function () {
    $creator = User::factory()->create();
    $password = 'secret123';
    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'campaign_id' => null,
        'password' => bcrypt($password),
    ]);

    // Without password parameter, should redirect to join page
    $response = get("/rooms/{$room->invite_code}");
    $response->assertRedirect("/rooms/join/{$room->invite_code}");

    // With correct password parameter, should access room
    $response = get("/rooms/{$room->invite_code}?password={$password}");
    $response->assertOk();
    $response->assertSee($room->name);

    // With incorrect password parameter, should redirect to join page
    $response = get("/rooms/{$room->invite_code}?password=wrongpassword");
    $response->assertRedirect("/rooms/join/{$room->invite_code}");
});

test('unauthenticated users can join password-protected rooms via URL', function () {
    $creator = User::factory()->create();
    $password = 'secret123';
    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'campaign_id' => null,
        'password' => bcrypt($password),
    ]);

    // Can access join page without password
    $response = get("/rooms/join/{$room->invite_code}");
    $response->assertOk();
    $response->assertSee('Room Password');

    // Can access join page with password parameter (shows password is valid)
    $response = get("/rooms/join/{$room->invite_code}?password={$password}");
    $response->assertOk();
});

test('room sessions can be accessed with URL password for non-campaign rooms', function () {
    $creator = User::factory()->create();
    $password = 'secret123';
    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'campaign_id' => null,
        'password' => bcrypt($password),
    ]);

    // Without password, should redirect to join page
    $response = get("/rooms/{$room->invite_code}/session");
    $response->assertRedirect("/rooms/join/{$room->invite_code}");

    // With correct password, should access session (even without being participant for non-campaign rooms)
    $response = get("/rooms/{$room->invite_code}/session?password={$password}");
    $response->assertOk();
    $response->assertSee($room->name);
});

test('joining rooms still requires authentication', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'creator_id' => $creator->id,
        'campaign_id' => null,
    ]);

    // Unauthenticated users cannot join rooms (requires account)
    $response = post("/rooms/{$room->invite_code}/join", [
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('error', 'Please log in to join this room.');
});

test('room show page handles unauthenticated users correctly', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'creator_id' => $creator->id,
        'campaign_id' => null,
    ]);

    $response = get("/rooms/{$room->invite_code}");
    $response->assertOk();
    
    // Should show join button for unauthenticated users
    $response->assertSee('Join Room');
    
    // Should not show creator/participant specific buttons
    $response->assertDontSee('Start Session');
    $response->assertDontSee('Delete Room');
});
