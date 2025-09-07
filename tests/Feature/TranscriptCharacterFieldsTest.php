<?php

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;

test('transcript stores character information correctly', function () {
    // Create test data
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'stt_enabled' => true,
    ]);
    
    // Create participant with character
    $participant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
        'character_name' => $character->name,
        'character_class' => $character->class,
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);
    
    // Test transcript upload with character information
    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/transcripts", [
        'user_id' => $user->id,
        'character_id' => $character->id,
        'character_name' => $character->name,
        'character_class' => $character->class,
        'started_at_ms' => 1000,
        'ended_at_ms' => 2000,
        'text' => 'Hello, this is my character speaking!',
        'language' => 'en-US',
        'confidence' => 0.95,
        'provider' => 'assemblyai'
    ]);
    
    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Transcript saved successfully'
    ]);
    
    // Verify the transcript was saved with character information
    $this->assertDatabaseHas('room_transcripts', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
        'character_name' => $character->name,
        'character_class' => $character->class,
        'text' => 'Hello, this is my character speaking!',
        'provider' => 'assemblyai'
    ]);
});

test('transcript works without character information', function () {
    // Create test data
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'stt_enabled' => true,
    ]);
    
    // Create participant without character
    $participant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => null,
        'character_class' => null,
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);
    
    // Test transcript upload without character information
    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/transcripts", [
        'user_id' => $user->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 2000,
        'text' => 'Hello, this is just a user speaking!',
        'language' => 'en-US',
        'confidence' => 0.95,
        'provider' => 'browser'
    ]);
    
    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Transcript saved successfully'
    ]);
    
    // Verify the transcript was saved without character information
    $this->assertDatabaseHas('room_transcripts', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => null,
        'character_class' => null,
        'text' => 'Hello, this is just a user speaking!',
        'provider' => 'browser'
    ]);
});

test('transcript validates provider field', function () {
    // Create test data
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'stt_enabled' => true,
    ]);
    
    // Create participant
    $participant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);
    
    // Test with invalid provider
    $response = $this->actingAs($user)->postJson("/api/rooms/{$room->id}/transcripts", [
        'user_id' => $user->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 2000,
        'text' => 'Test transcript',
        'provider' => 'invalid_provider'
    ]);
    
    $response->assertStatus(422);
    $response->assertJsonStructure([
        'error',
        'messages' => [
            'provider'
        ]
    ]);
    $response->assertJson([
        'error' => 'Validation failed',
        'messages' => [
            'provider' => ['The selected provider is invalid.']
        ]
    ]);
});