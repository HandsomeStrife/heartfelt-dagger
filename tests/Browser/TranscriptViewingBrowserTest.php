<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('GM can access transcript API in browser context', function () {
    // Create GM and player
    $gm = User::factory()->create();
    $player = User::factory()->create();
    
    $room = Room::factory()->create(['creator_id' => $gm->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participants with consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'character_name' => 'Test GM',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'character_name' => 'Test Player',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create some transcripts
    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'Welcome to our session!',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'started_at_ms' => 6000,
        'ended_at_ms' => 10000,
        'text' => 'I roll for initiative!',
        'language' => 'en-US',
        'confidence' => 0.88,
    ]);

    actingAs($gm);
    $page = visit("/api/rooms/{$room->id}/transcripts");
    
    // Should get successful JSON response
    $page->assertSee('success')
        ->assertSee('transcripts')
        ->assertSee('Welcome to our session')
        ->assertSee('I roll for initiative');
});

test('player can access transcript API in browser context', function () {
    // Create GM and player
    $gm = User::factory()->create();
    $player = User::factory()->create();
    
    $room = Room::factory()->create(['creator_id' => $gm->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participants with consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'character_name' => 'Test GM',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'character_name' => 'Test Player',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create some transcripts
    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'The dragon approaches!',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'started_at_ms' => 6000,
        'ended_at_ms' => 10000,
        'text' => 'I attack with my sword!',
        'language' => 'en-US',
        'confidence' => 0.92,
    ]);

    actingAs($player);
    $page = visit("/api/rooms/{$room->id}/transcripts");
    
    // Player should also get successful JSON response
    $page->assertSee('success')
        ->assertSee('transcripts')
        ->assertSee('The dragon approaches')
        ->assertSee('I attack with my sword');
});

test('non-participant cannot access transcript API in browser context', function () {
    // Create GM, player, and outsider
    $gm = User::factory()->create();
    $player = User::factory()->create();
    $outsider = User::factory()->create();
    
    $room = Room::factory()->create(['creator_id' => $gm->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participants (but not outsider)
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'character_name' => 'Test GM',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'character_name' => 'Test Player',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create a transcript
    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'Secret conversation',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    actingAs($outsider);
    $page = visit("/api/rooms/{$room->id}/transcripts");
    
    // Outsider should get access denied error
    $page->assertSee('Only room participants can view transcripts');
});
