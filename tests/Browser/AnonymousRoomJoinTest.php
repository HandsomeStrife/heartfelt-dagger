<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;

test('anonymous user can join non-campaign room successfully', function () {
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => null, // Ensure it's not a campaign room
    ]);

    get("/rooms/join/{$room->invite_code}")
         ->assertOk()
         ->assertSee('Join Room')
         ->assertSee('Create Temporary Character');
         
    // Submit the form as anonymous user
    $response = post(route('rooms.join', $room), [
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior'
    ]);
    
    $response->assertRedirect("/rooms/{$room->invite_code}/session");
    
    // Verify participant was created
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => null, // Anonymous user
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior'
    ]);
    
    // Check session page loads correctly
    get("/rooms/{$room->invite_code}/session")
         ->assertOk()
         ->assertSee('Anonymous Hero');
});

test('anonymous user cannot join campaign room', function () {
    $campaign = \Domain\Campaign\Models\Campaign::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => $campaign->id,
    ]);

    // Anonymous users should be redirected to login when trying to access campaign room join page
    $response = get("/rooms/join/{$room->invite_code}");
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Please log in to access this campaign room.');
});

test('anonymous user can join password-protected room with URL parameter', function () {
    $password = 'secretpass';
    $room = Room::factory()->create([
        'password' => bcrypt($password),
        'campaign_id' => null,
    ]);

    get("/rooms/join/{$room->invite_code}?password={$password}")
         ->assertOk()
         ->assertSee('Join Room')
         ->assertSee('Create Temporary Character');
         
    // Submit form with password in URL
    $response = post(route('rooms.join', $room) . "?password={$password}", [
        'character_name' => 'Secret Character',
        'character_class' => 'Wizard',
        'password' => $password
    ]);
    
    $response->assertRedirect("/rooms/{$room->invite_code}/session?password={$password}");
    
    // Verify session page loads
    get("/rooms/{$room->invite_code}/session?password={$password}")
         ->assertOk()
         ->assertSee('Secret Character');
});

test('anonymous user cannot join password-protected room without correct password', function () {
    $password = 'secretpass';
    $room = Room::factory()->create([
        'password' => bcrypt($password),
        'campaign_id' => null,
    ]);

    get("/rooms/join/{$room->invite_code}")
         ->assertOk()
         ->assertSee('Join Room')
         ->assertSee('Room Password');
         
    // Submit form with wrong password
    $response = post(route('rooms.join', $room), [
        'password' => 'wrongpassword',
        'character_name' => 'Wrong Password Character',
        'character_class' => 'Bard'
    ]);
    
    $response->assertSessionHasErrors(['password' => 'Invalid room password.']);
});

test('anonymous user sees session page without header and footer', function () {
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => null,
    ]);

    // First join the room
    $response = post(route('rooms.join', $room), [
        'character_name' => 'Session Tester',
        'character_class' => 'Guardian'
    ]);
    
    $response->assertRedirect("/rooms/{$room->invite_code}/session");
    
    // Check session page layout
    $sessionResponse = get("/rooms/{$room->invite_code}/session");
    $sessionResponse->assertOk();
    $sessionResponse->assertSee('Session Tester');
    
    // Verify minimal layout (no header/footer)
    $content = $sessionResponse->getContent();
    expect($content)->not->toContain('data-testid="main-navigation"');
    expect($content)->not->toContain('data-testid="main-footer"');
    
    // Should have video slots
    expect($content)->toContain('data-testid="video-slot"');
});

test('anonymous user form validation works correctly', function () {
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => null,
    ]);

    get("/rooms/join/{$room->invite_code}")
         ->assertOk()
         ->assertSee('Join Room');
         
    // Try to submit without filling required fields
    $response = post(route('rooms.join', $room), []);
    
    $response->assertSessionHasErrors(['character_name', 'character_class']);
});

test('anonymous user cannot use existing character option', function () {
    $user = User::factory()->create();
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Existing Character',
        'class' => 'Druid',
    ]);
    
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => null,
    ]);

    $response = get("/rooms/join/{$room->invite_code}");
    $response->assertOk();
    $response->assertSee('Join Room');
    $response->assertSee('Create Temporary Character');
    
    // Anonymous users shouldn't see character selection dropdown
    $response->assertDontSee('Character Selection');
    $response->assertDontSee('Use Existing Character');
    $response->assertDontSee('Existing Character');
});
