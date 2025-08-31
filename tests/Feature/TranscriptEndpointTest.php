<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, postJson, getJson};

describe('Room Transcript API Endpoints', function () {
    test('stores transcript with valid data', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        
        // Enable STT for the room
        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        // Add user as participant with STT consent
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        $transcriptData = [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567895000, // 5 second window
            'text' => 'This is a test transcript from speech recognition.',
            'language' => 'en-GB',
            'confidence' => 0.95,
        ];

        $response = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson("/api/rooms/{$room->id}/transcripts", $transcriptData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'transcript',
            ]);

        // Verify database record was created
        $transcript = RoomTranscript::where('room_id', $room->id)->first();
        expect($transcript)->not()->toBeNull();
        expect($transcript->user_id)->toBe($user->id);
        expect($transcript->text)->toBe('This is a test transcript from speech recognition.');
        expect($transcript->language)->toBe('en-GB');
        expect($transcript->confidence)->toBe(0.95);
        expect($transcript->started_at_ms)->toBe(1234567890000);
        expect($transcript->ended_at_ms)->toBe(1234567895000);
    });

    test('validates required fields', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $response = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson("/api/rooms/{$room->id}/transcripts", [
                // Missing started_at_ms, ended_at_ms, text is provided but the others are required
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'messages' => [
                    'started_at_ms',
                    'ended_at_ms',
                    'text'
                ]
            ]);
    });

    test('validates time window bounds', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        $response = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson("/api/rooms/{$room->id}/transcripts", [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => 1234567895000, // End is before start
                'ended_at_ms' => 1234567890000,
                'text' => 'Invalid time window',
                'language' => 'en-US',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'messages' => ['ended_at_ms']
            ]);
    });

    test('validates language format', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        $response = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson("/api/rooms/{$room->id}/transcripts", [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => 1234567890000,
                'ended_at_ms' => 1234567895000,
                'text' => 'Test text',
                'language' => 'invalid_language_code',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'messages' => ['language']
            ]);
    });

    test('validates confidence range', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        $response = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson("/api/rooms/{$room->id}/transcripts", [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => 1234567890000,
                'ended_at_ms' => 1234567895000,
                'text' => 'Test text',
                'language' => 'en-US',
                'confidence' => 1.5, // Invalid - must be <= 1.0
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'messages' => ['confidence']
            ]);
    });

    test('requires STT consent from user', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        // Add user as participant WITHOUT STT consent
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => null, // No consent
        ]);

        $response = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson("/api/rooms/{$room->id}/transcripts", [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => 1234567890000,
                'ended_at_ms' => 1234567895000,
                'text' => 'Test text',
                'language' => 'en-US',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Speech-to-text consent required',
                'requires_consent' => true,
            ]);
    });

    test('fails when STT is disabled for room', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => false, // STT disabled
        ]);

        $response = actingAs($user)
            ->withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->postJson("/api/rooms/{$room->id}/transcripts", [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => 1234567890000,
                'ended_at_ms' => 1234567895000,
                'text' => 'Test text',
                'language' => 'en-US',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Speech-to-text is not enabled for this room'
            ]);
    });

    test('retrieves transcripts for room with time filtering', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        // Create multiple transcript entries
        $transcripts = [
            [
                'started_at_ms' => 1234567890000,
                'ended_at_ms' => 1234567895000,
                'text' => 'First transcript',
            ],
            [
                'started_at_ms' => 1234567900000,
                'ended_at_ms' => 1234567905000,
                'text' => 'Second transcript',
            ],
            [
                'started_at_ms' => 1234567910000,
                'ended_at_ms' => 1234567915000,
                'text' => 'Third transcript',
            ],
        ];

        foreach ($transcripts as $transcript) {
            RoomTranscript::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => $transcript['started_at_ms'],
                'ended_at_ms' => $transcript['ended_at_ms'],
                'text' => $transcript['text'],
                'language' => 'en-US',
                'confidence' => 0.9,
            ]);
        }

        // Test: Get all transcripts
        $response = actingAs($user)
            ->getJson("/api/rooms/{$room->id}/transcripts");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'transcripts' => [
                    '*' => [
                        'id',
                        'room_id',
                        'user_id',
                        'started_at_ms',
                        'ended_at_ms',
                        'text',
                        'language',
                        'confidence',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'count',
            ]);

        $responseData = $response->json();
        expect($responseData['count'])->toBe(3);
        expect($responseData['transcripts'])->toHaveCount(3);

        // Test: Filter by time range (should get middle transcript only)
        $filteredResponse = actingAs($user)
            ->getJson("/api/rooms/{$room->id}/transcripts?start_ms=1234567898000&end_ms=1234567908000");

        $filteredResponse->assertStatus(200);
        $filteredData = $filteredResponse->json();
        expect($filteredData['count'])->toBe(1);
        expect($filteredData['transcripts'][0]['text'])->toBe('Second transcript');
    });

    test('filters transcripts by user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user1->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        // Add both users as participants
        foreach ([$user1, $user2] as $user) {
            $room->participants()->create([
                'user_id' => $user->id,
                'character_name' => "Character {$user->id}",
                'stt_consent_given' => true,
            'stt_consent_at' => now(),
            ]);
        }

        // Create transcripts from both users
        RoomTranscript::create([
            'room_id' => $room->id,
            'user_id' => $user1->id,
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567895000,
            'text' => 'User 1 transcript',
            'language' => 'en-US',
        ]);

        RoomTranscript::create([
            'room_id' => $room->id,
            'user_id' => $user2->id,
            'started_at_ms' => 1234567900000,
            'ended_at_ms' => 1234567905000,
            'text' => 'User 2 transcript',
            'language' => 'en-US',
        ]);

        // Filter by user 1
        $response = actingAs($user1)
            ->getJson("/api/rooms/{$room->id}/transcripts?user_id={$user1->id}");

        $response->assertStatus(200);
        $data = $response->json();
        expect($data['count'])->toBe(1);
        expect($data['transcripts'][0]['text'])->toBe('User 1 transcript');
        expect($data['transcripts'][0]['user_id'])->toBe($user1->id);
    });

    test('limits transcript results', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        // Create 10 transcripts
        for ($i = 1; $i <= 10; $i++) {
            RoomTranscript::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => 1234567890000 + ($i * 5000),
                'ended_at_ms' => 1234567895000 + ($i * 5000),
                'text' => "Transcript {$i}",
                'language' => 'en-US',
            ]);
        }

        // Limit to 3 results
        $response = actingAs($user)
            ->getJson("/api/rooms/{$room->id}/transcripts?limit=3");

        $response->assertStatus(200);
        $data = $response->json();
        expect($data['transcripts'])->toHaveCount(3);
    });

    test('prevents access to other rooms transcripts', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room1 = Room::factory()->create(['creator_id' => $user1->id]);
        $room2 = Room::factory()->create(['creator_id' => $user2->id]);

        // User 1 tries to access User 2's room transcripts
        $response = actingAs($user1)
            ->getJson("/api/rooms/{$room2->id}/transcripts");

        $response->assertStatus(403)
            ->assertJson(['error' => 'Only room participants can view transcripts']);
    });
});
