<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    // Seed the test database with a user
    $this->testUser = User::factory()->create([
        'username' => 'TestRoomCreator',
        'email' => 'creator@example.com',
    ]);

    // Create a password-protected room as that user
    $this->testPassword = 'secret123';
    $this->testRoom = Room::factory()->create([
        'name' => 'Password Protected Test Room',
        'description' => 'A room that requires a password to join',
        'password' => bcrypt($this->testPassword),
        'creator_id' => $this->testUser->id,
        'campaign_id' => null, // Non-campaign room so anonymous users can join
        'guest_count' => 3,
    ]);
});

test('anonymous user can access password-protected room join page', function () {
    $response = get("/rooms/join/{$this->testRoom->invite_code}");

    $response->assertOk();
    $response->assertSee('Join Room');
    $response->assertSee('Room Password');
    $response->assertSee('Password Protected Test Room');
    $response->assertSee('Create Temporary Character');
});

test('anonymous user cannot join password-protected room without password', function () {
    $response = post(route('rooms.join', $this->testRoom), [
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
        // No password provided
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['password']);
});

test('anonymous user cannot join password-protected room with wrong password', function () {
    $response = post(route('rooms.join', $this->testRoom), [
        'password' => 'wrongpassword',
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['password' => 'Invalid room password.']);
});

test('anonymous user can successfully join password-protected room with correct password', function () {
    $response = post(route('rooms.join', $this->testRoom), [
        'password' => $this->testPassword,
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
    ]);

    // Should redirect to session page with password in URL
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Successfully joined the room!');

    // Verify the redirect URL includes the password
    $redirectUrl = $response->headers->get('Location');
    expect($redirectUrl)->toContain('/session');
    expect($redirectUrl)->toContain('password=');

    // Verify participant was created in database
    \Pest\Laravel\assertDatabaseHas('room_participants', [
        'room_id' => $this->testRoom->id,
        'user_id' => null, // Anonymous user
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
    ]);
});

test('anonymous user can access session page after joining password-protected room', function () {
    // First join the room
    $joinResponse = post(route('rooms.join', $this->testRoom), [
        'password' => $this->testPassword,
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
    ]);

    // Extract the redirect URL and follow it
    $redirectUrl = $joinResponse->headers->get('Location');

    $response = get($redirectUrl);

    $response->assertOk();
    $response->assertSee('Anonymous Hero');
    $response->assertSee('Warrior');
    $response->assertSee($this->testRoom->name);
});

test('anonymous user cannot access session page without password in URL for password-protected room', function () {
    // First join the room
    post(route('rooms.join', $this->testRoom), [
        'password' => $this->testPassword,
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
    ]);

    // Try to access session without password in URL
    $response = get(route('rooms.session', $this->testRoom));

    // Should redirect back to join page
    $response->assertRedirect("/rooms/join/{$this->testRoom->invite_code}");
});

test('anonymous user can rejoin password-protected room if already participating', function () {
    // First join the room
    post(route('rooms.join', $this->testRoom), [
        'password' => $this->testPassword,
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
    ]);

    // Try to access join page again - should redirect to session with password
    $response = get("/rooms/join/{$this->testRoom->invite_code}?password=".urlencode($this->testPassword));

    $response->assertRedirect();
    $redirectUrl = $response->headers->get('Location');
    expect($redirectUrl)->toContain('/session');
    expect($redirectUrl)->toContain('password=');
});

test('multiple anonymous users can join same password-protected room', function () {
    // First anonymous user joins
    $response1 = post(route('rooms.join', $this->testRoom), [
        'password' => $this->testPassword,
        'character_name' => 'Anonymous Hero 1',
        'character_class' => 'Warrior',
    ]);

    $response1->assertRedirect();
    $response1->assertSessionHas('success');

    // Start a new session for second anonymous user
    \Pest\Laravel\withSession([]);

    // Second anonymous user joins
    $response2 = post(route('rooms.join', $this->testRoom), [
        'password' => $this->testPassword,
        'character_name' => 'Anonymous Hero 2',
        'character_class' => 'Ranger',
    ]);

    $response2->assertRedirect();
    $response2->assertSessionHas('success');

    // Verify both participants exist in database
    \Pest\Laravel\assertDatabaseHas('room_participants', [
        'room_id' => $this->testRoom->id,
        'user_id' => null,
        'character_name' => 'Anonymous Hero 1',
        'character_class' => 'Warrior',
    ]);

    \Pest\Laravel\assertDatabaseHas('room_participants', [
        'room_id' => $this->testRoom->id,
        'user_id' => null,
        'character_name' => 'Anonymous Hero 2',
        'character_class' => 'Ranger',
    ]);
});

test('password is required field when room has password set', function () {
    $response = get("/rooms/join/{$this->testRoom->invite_code}");

    $response->assertOk();
    $response->assertSee('Room Password');
    $response->assertSee('required');
});
