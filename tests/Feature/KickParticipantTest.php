<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Domain\Room\Models\RoomParticipant;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

beforeEach(function () {
    // Create room creator
    $this->roomCreator = User::factory()->create([
        'username' => 'RoomCreator',
        'email' => 'creator@example.com',
    ]);
    
    // Create participants
    $this->participant1 = User::factory()->create([
        'username' => 'Participant1',
        'email' => 'participant1@example.com',
    ]);
    
    $this->participant2 = User::factory()->create([
        'username' => 'Participant2', 
        'email' => 'participant2@example.com',
    ]);
    
    // Create a room
    $this->room = Room::factory()->create([
        'name' => 'Test Room for Kicking',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 3,
    ]);
    
    // Add participants to the room
    $this->roomParticipant1 = RoomParticipant::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->participant1->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    $this->roomParticipant2 = RoomParticipant::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->participant2->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
});

test('room creator can kick participants from room show page', function () {
    actingAs($this->roomCreator);
    
    $response = delete(route('rooms.kick', [$this->room, $this->roomParticipant1->id]));
    
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Participant has been removed from the room.');
    
    // Verify participant was marked as left
    $this->roomParticipant1->refresh();
    expect($this->roomParticipant1->left_at)->not()->toBeNull();
});

test('room creator can kick participants from session page', function () {
    actingAs($this->roomCreator);
    
    // Access session page first
    get(route('rooms.session', $this->room))->assertOk();
    
    // Kick participant
    $response = delete(route('rooms.kick', [$this->room, $this->roomParticipant2->id]));
    
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Participant has been removed from the room.');
    
    // Verify participant was marked as left
    $this->roomParticipant2->refresh();
    expect($this->roomParticipant2->left_at)->not()->toBeNull();
});

test('non-creator cannot kick participants', function () {
    actingAs($this->participant1);
    
    $response = delete(route('rooms.kick', [$this->room, $this->roomParticipant2->id]));
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['error']);
    
    // Verify participant was NOT marked as left
    $this->roomParticipant2->refresh();
    expect($this->roomParticipant2->left_at)->toBeNull();
});

test('room creator cannot kick themselves', function () {
    // Create creator as participant
    $creatorParticipant = RoomParticipant::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = delete(route('rooms.kick', [$this->room, $creatorParticipant->id]));
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['error']);
    
    // Verify creator was NOT marked as left
    $creatorParticipant->refresh();
    expect($creatorParticipant->left_at)->toBeNull();
});

test('cannot kick non-existent participant', function () {
    actingAs($this->roomCreator);
    
    $response = delete(route('rooms.kick', [$this->room, 99999]));
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['error']);
});

test('cannot kick already left participant', function () {
    // Mark participant as already left
    $this->roomParticipant1->update(['left_at' => now()]);
    
    actingAs($this->roomCreator);
    
    $response = delete(route('rooms.kick', [$this->room, $this->roomParticipant1->id]));
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['error']);
});

test('kicked participant no longer shows as active participant', function () {
    actingAs($this->roomCreator);
    
    // Verify participant is active before kick
    expect($this->room->activeParticipants()->count())->toBe(2);
    expect($this->room->hasActiveParticipant($this->participant1))->toBeTrue();
    
    // Kick participant
    delete(route('rooms.kick', [$this->room, $this->roomParticipant1->id]));
    
    // Verify participant is no longer active
    expect($this->room->fresh()->activeParticipants()->count())->toBe(1);
    expect($this->room->hasActiveParticipant($this->participant1))->toBeFalse();
    expect($this->room->hasActiveParticipant($this->participant2))->toBeTrue();
});

test('anonymous participant can be kicked', function () {
    // Create anonymous participant
    $anonymousParticipant = RoomParticipant::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => null, // Anonymous
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = delete(route('rooms.kick', [$this->room, $anonymousParticipant->id]));
    
    $response->assertRedirect();
    $response->assertSessionHas('success', 'Participant has been removed from the room.');
    
    // Verify anonymous participant was marked as left
    $anonymousParticipant->refresh();
    expect($anonymousParticipant->left_at)->not()->toBeNull();
});

test('room show page displays remove buttons for creator', function () {
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.show', $this->room));
    
    $response->assertOk();
    $response->assertSee('Remove'); // Remove button text
    $response->assertSee('Are you sure you want to remove this participant?'); // Confirmation text
});

test('room show page does not display remove buttons for non-creator', function () {
    actingAs($this->participant1);
    
    // Join the room first
    $joinResponse = post(route('rooms.join', $this->room), [
        'character_name' => 'Test Character',
        'character_class' => 'Warrior'
    ]);
    
    // Follow the redirect from join to session, then go to show page
    $response = get(route('rooms.show', $this->room));
    
    if ($response->status() === 302) {
        // If redirected, follow the redirect
        $response = $this->followRedirects($response);
    }
    
    $response->assertOk();
    $response->assertDontSee('Remove'); // Should not see remove buttons
});

test('session page shows clickable participant count for creator', function () {
    actingAs($this->roomCreator);
    
    // Creator should be able to access session page
    $response = get(route('rooms.session', $this->room));
    
    $response->assertOk();
    $response->assertSee('toggleParticipantsList()'); // JavaScript function
    $response->assertSee('Manage Participants'); // Dropdown header
});


