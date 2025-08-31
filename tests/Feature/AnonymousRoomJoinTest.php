<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('anonymous user can access join page with enabled temporary character fields', function () {
    $room = Room::factory()->passwordless()->create();
    
    // Anonymous user (not authenticated)
    $response = get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertSee('Create Temporary Character');
    
    // Check that fields are enabled for anonymous users
    $content = $response->getContent();
    expect($content)->toContain('name="character_name"');
    expect($content)->toContain('name="character_class"');
    
    // Check that the character name field has required attribute (meaning it's enabled)
    expect($content)->toMatch('/<input[^>]*id="character_name"[^>]*required[^>]*>/');
    
    // Check that the character class field has required attribute (meaning it's enabled)  
    expect($content)->toMatch('/<select[^>]*id="character_class"[^>]*required[^>]*>/');
    
    // Check that temporary character fields container doesn't have opacity-50 class
    expect($content)->toMatch('/<div[^>]*id="temporary-character-fields"[^>]*class="[^"]*space-y-3[^"]*"[^>]*>/'); // Should have space-y-3 but not opacity-50
});

test('anonymous user can submit form with temporary character for non-campaign room', function () {
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => null, // Ensure it's not a campaign room
    ]);
    
    // Anonymous user trying to join with temporary character
    $response = post(route('rooms.join', $room), [
        'character_name' => 'Anonymous Character',
        'character_class' => 'Warrior'
    ]);

    // Should successfully join and redirect to session
    $response->assertRedirect(route('rooms.session', $room));
    $response->assertSessionHas('success', 'Successfully joined the room!');
    
    // Verify participant was created
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => null, // Anonymous user
        'character_name' => 'Anonymous Character',
        'character_class' => 'Warrior'
    ]);
});

test('anonymous user cannot join campaign room', function () {
    $campaign = \Domain\Campaign\Models\Campaign::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => $campaign->id,
    ]);
    
    // Anonymous user trying to join campaign room
    $response = post(route('rooms.join', $room), [
        'character_name' => 'Campaign Character',
        'character_class' => 'Rogue'
    ]);

    // Should redirect to login for campaign rooms
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Please log in to join this campaign room.');
});

test('anonymous user sees clean interface without character selection dropdown', function () {
    $room = Room::factory()->passwordless()->create();
    
    $response = get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    
    // Should not see character selection dropdown or related elements
    $response->assertDontSee('Character Selection');
    $response->assertDontSee('Use Existing Character');
    $response->assertDontSee('Select an existing character');
    
    // Should see simplified temporary character form
    $response->assertSee('Create Temporary Character');
    $response->assertSee('Character name (required for temporary character)');
    $response->assertSee('Select class');
});

test('password protected room shows form for anonymous users', function () {
    $password = 'testpassword';
    $room = Room::factory()->create([
        'password' => bcrypt($password),
        'campaign_id' => null, // Not a campaign room
    ]);
    
    // Anonymous user can access join page with password in URL
    $response = get("/rooms/join/{$room->invite_code}?password={$password}");
    
    $response->assertOk();
    $response->assertSee('Create Temporary Character');
    
    // Should see password field pre-filled or handled
    $response->assertSee('Room Password');
});

test('user with no characters gets enabled temporary fields', function () {
    $user = \Domain\User\Models\User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    
    // User with no characters
    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertSee('Create Temporary Character');
    
    // Check that fields are enabled for users with no characters
    $content = $response->getContent();
    expect($content)->toContain('name="character_name"');
    expect($content)->toContain('name="character_class"');
    
    // Check that the character name field has required attribute (meaning it's enabled)
    expect($content)->toMatch('/<input[^>]*id="character_name"[^>]*required[^>]*>/');
    
    // Check that the character class field has required attribute (meaning it's enabled)  
    expect($content)->toMatch('/<select[^>]*id="character_class"[^>]*required[^>]*>/');
    
    // Check that temporary character fields container doesn't have opacity-50 class
    expect($content)->toMatch('/<div[^>]*id="temporary-character-fields"[^>]*class="[^"]*space-y-3[^"]*"[^>]*>/'); // Should have space-y-3 but not opacity-50
});
