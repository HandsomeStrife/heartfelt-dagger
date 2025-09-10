<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;

test('room creator (GM) can view all transcripts in their room', function () {
    // Create GM (room creator) and players
    $gm = User::factory()->create();
    $player1 = User::factory()->create();
    $player2 = User::factory()->create();

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
        'character_name' => 'GM Character',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'character_name' => 'Player 1',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player2->id,
        'character_name' => 'Player 2',
        'character_class' => 'Rogue',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create transcripts from different users
    $transcript1 = RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'Welcome to the session, everyone!',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    $transcript2 = RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'started_at_ms' => 6000,
        'ended_at_ms' => 10000,
        'text' => 'I want to attack the goblin!',
        'language' => 'en-US',
        'confidence' => 0.88,
    ]);

    $transcript3 = RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player2->id,
        'started_at_ms' => 11000,
        'ended_at_ms' => 15000,
        'text' => 'I sneak around the corner.',
        'language' => 'en-US',
        'confidence' => 0.92,
    ]);

    // GM should be able to view all transcripts
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 3,
        ]);

    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(3);

    // Verify all transcripts are included
    $transcriptTexts = collect($transcripts)->pluck('text')->toArray();
    expect($transcriptTexts)->toContain('Welcome to the session, everyone!');
    expect($transcriptTexts)->toContain('I want to attack the goblin!');
    expect($transcriptTexts)->toContain('I sneak around the corner.');
});

test('active players can view transcripts in their room', function () {
    // Create GM and players
    $gm = User::factory()->create();
    $player1 = User::factory()->create();
    $player2 = User::factory()->create();

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
        'character_name' => 'GM Character',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'character_name' => 'Player 1',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create transcripts
    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'Roll for initiative!',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'started_at_ms' => 6000,
        'ended_at_ms' => 10000,
        'text' => 'I rolled a 15!',
        'language' => 'en-US',
        'confidence' => 0.88,
    ]);

    // Player 1 should be able to view all transcripts in the room
    $response = $this->actingAs($player1)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 2,
        ]);

    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(2);

    // Verify transcripts contain expected data
    $transcript = $transcripts[0];
    expect($transcript)->toHaveKey('id');
    expect($transcript)->toHaveKey('user_id');
    expect($transcript)->toHaveKey('text');
    expect($transcript)->toHaveKey('started_at_ms');
    expect($transcript)->toHaveKey('ended_at_ms');
    expect($transcript)->toHaveKey('confidence');
    expect($transcript)->toHaveKey('language');
});

test('non-participants cannot view transcripts', function () {
    // Create GM, participant, and non-participant
    $gm = User::factory()->create();
    $player = User::factory()->create();
    $outsider = User::factory()->create(); // Not in the room

    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participants (GM and player only)
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'character_name' => 'GM Character',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'character_name' => 'Player',
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
        'text' => 'This should be private!',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    // Outsider should not be able to view transcripts
    $response = $this->actingAs($outsider)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Only room participants can view transcripts',
        ]);
});

test('users from other rooms cannot view transcripts', function () {
    // Create two separate rooms with different users
    $gm1 = User::factory()->create();
    $player1 = User::factory()->create();
    $gm2 = User::factory()->create();
    $player2 = User::factory()->create();

    $room1 = Room::factory()->create(['creator_id' => $gm1->id]);
    $room2 = Room::factory()->create(['creator_id' => $gm2->id]);

    // Enable STT for both rooms
    RoomRecordingSettings::create([
        'room_id' => $room1->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    RoomRecordingSettings::create([
        'room_id' => $room2->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participants in room 1
    RoomParticipant::create([
        'room_id' => $room1->id,
        'user_id' => $gm1->id,
        'character_name' => 'GM 1',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room1->id,
        'user_id' => $player1->id,
        'character_name' => 'Player 1',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create participants in room 2
    RoomParticipant::create([
        'room_id' => $room2->id,
        'user_id' => $gm2->id,
        'character_name' => 'GM 2',
        'character_class' => 'Seraph',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room2->id,
        'user_id' => $player2->id,
        'character_name' => 'Player 2',
        'character_class' => 'Rogue',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create transcripts in room 1
    RoomTranscript::create([
        'room_id' => $room1->id,
        'user_id' => $player1->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'Secret room 1 conversation',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    // User from room 2 should not access room 1 transcripts
    $response = $this->actingAs($player2)
        ->getJson("/api/rooms/{$room1->id}/transcripts");

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Only room participants can view transcripts',
        ]);

    // User from room 1 should not access room 2 transcripts
    $response = $this->actingAs($player1)
        ->getJson("/api/rooms/{$room2->id}/transcripts");

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Only room participants can view transcripts',
        ]);
});

test('transcript filtering works correctly for authorized users', function () {
    $gm = User::factory()->create();
    $player1 = User::factory()->create();
    $player2 = User::factory()->create();

    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participants
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'character_name' => 'GM Character',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'character_name' => 'Player 1',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player2->id,
        'character_name' => 'Player 2',
        'character_class' => 'Rogue',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create transcripts with different properties
    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'I attack the dragon!',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'started_at_ms' => 6000,
        'ended_at_ms' => 10000,
        'text' => 'I cast fireball!',
        'language' => 'en-US',
        'confidence' => 0.70,
    ]);

    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player2->id,
        'started_at_ms' => 11000,
        'ended_at_ms' => 15000,
        'text' => 'I sneak behind the enemy.',
        'language' => 'en-US',
        'confidence' => 0.92,
    ]);

    // Test filtering by user_id
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts?user_id={$player1->id}");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(2);

    foreach ($transcripts as $transcript) {
        expect($transcript['user_id'])->toBe($player1->id);
    }

    // Test filtering by search term
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts?search=dragon");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(1);
    expect($transcripts[0]['text'])->toContain('dragon');

    // Test filtering by confidence
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts?min_confidence=0.9");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(2); // Only high confidence transcripts

    // Test filtering by time range
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts?start_ms=5000&end_ms=12000");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(1); // Only transcript 2 (6000-10000) matches the range

    // Test limit parameter
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts?limit=1");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(1);
});

test('players can filter transcripts to see only their own', function () {
    $gm = User::factory()->create();
    $player1 = User::factory()->create();
    $player2 = User::factory()->create();

    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participants
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'character_name' => 'Player 1',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player2->id,
        'character_name' => 'Player 2',
        'character_class' => 'Rogue',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create transcripts from both players
    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'Player 1 speaks here',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player2->id,
        'started_at_ms' => 6000,
        'ended_at_ms' => 10000,
        'text' => 'Player 2 speaks here',
        'language' => 'en-US',
        'confidence' => 0.88,
    ]);

    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player1->id,
        'started_at_ms' => 11000,
        'ended_at_ms' => 15000,
        'text' => 'Player 1 speaks again',
        'language' => 'en-US',
        'confidence' => 0.92,
    ]);

    // Player 1 can filter to see only their own transcripts
    $response = $this->actingAs($player1)
        ->getJson("/api/rooms/{$room->id}/transcripts?user_id={$player1->id}");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(2);

    foreach ($transcripts as $transcript) {
        expect($transcript['user_id'])->toBe($player1->id);
        expect($transcript['text'])->toContain('Player 1');
    }

    // Player 1 can also see all transcripts (including Player 2's)
    $response = $this->actingAs($player1)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(3);
});

test('transcript data includes all necessary fields', function () {
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

    // Create participant
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'character_name' => 'Test Player',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create a transcript with all fields
    $originalTranscript = RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $player->id,
        'started_at_ms' => 12345000,
        'ended_at_ms' => 12350000,
        'text' => 'This is a complete transcript!',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    // GM retrieves transcripts
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(200);
    $transcripts = $response->json('transcripts');
    expect($transcripts)->toHaveCount(1);

    $transcript = $transcripts[0];

    // Verify all expected fields are present
    expect($transcript)->toHaveKey('id');
    expect($transcript)->toHaveKey('room_id');
    expect($transcript)->toHaveKey('user_id');
    expect($transcript)->toHaveKey('started_at_ms');
    expect($transcript)->toHaveKey('ended_at_ms');
    expect($transcript)->toHaveKey('text');
    expect($transcript)->toHaveKey('language');
    expect($transcript)->toHaveKey('confidence');
    expect($transcript)->toHaveKey('created_at');
    expect($transcript)->toHaveKey('updated_at');

    // Verify field values match
    expect($transcript['id'])->toBe($originalTranscript->id);
    expect($transcript['room_id'])->toBe($room->id);
    expect($transcript['user_id'])->toBe($player->id);
    expect($transcript['started_at_ms'])->toBe(12345000);
    expect($transcript['ended_at_ms'])->toBe(12350000);
    expect($transcript['text'])->toBe('This is a complete transcript!');
    expect($transcript['language'])->toBe('en-US');
    expect($transcript['confidence'])->toBe(0.95);
});

test('campaign room transcripts follow same access rules', function () {
    // Create campaign with GM and members
    $gm = User::factory()->create();
    $campaignMember = User::factory()->create();
    $outsider = User::factory()->create();

    $campaign = \Domain\Campaign\Models\Campaign::factory()->create([
        'creator_id' => $gm->id,
    ]);

    // Add member to campaign
    \Domain\Campaign\Models\CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $campaignMember->id,
        'character_id' => null,
        'joined_at' => now(),
    ]);

    // Create campaign room
    $room = Room::factory()->create([
        'creator_id' => $gm->id,
        'campaign_id' => $campaign->id,
        'password' => null, // Campaign rooms don't use passwords
    ]);

    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create room participants
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $gm->id,
        'character_name' => 'Campaign GM',
        'character_class' => 'Guardian',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $campaignMember->id,
        'character_name' => 'Campaign Player',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    // Create transcript
    RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $campaignMember->id,
        'started_at_ms' => 1000,
        'ended_at_ms' => 5000,
        'text' => 'Campaign session transcript',
        'language' => 'en-US',
        'confidence' => 0.95,
    ]);

    // GM can view transcripts
    $response = $this->actingAs($gm)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'count' => 1]);

    // Campaign member can view transcripts
    $response = $this->actingAs($campaignMember)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'count' => 1]);

    // Outsider cannot view transcripts
    $response = $this->actingAs($outsider)
        ->getJson("/api/rooms/{$room->id}/transcripts");

    $response->assertStatus(403)
        ->assertJson(['error' => 'Access denied']); // Campaign rooms still use general access control first
});
