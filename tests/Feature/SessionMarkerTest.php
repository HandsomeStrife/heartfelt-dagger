<?php

use Domain\Room\Actions\CreateSessionMarkerForAllParticipants;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Models\SessionMarker;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->room = Room::factory()->create([
        'creator_id' => $this->user->id,
        'name' => 'Test Room',
    ]);

    // Add participants to the room
    $this->room->participants()->create([
        'user_id' => $this->user->id,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);

    $this->room->participants()->create([
        'user_id' => $this->otherUser->id,
        'character_name' => 'Other Character',
        'character_class' => 'Wizard',
    ]);

    // Create recording settings with STT and recording enabled
    $this->recordingSettings = RoomRecordingSettings::factory()->create([
        'room_id' => $this->room->id,
        'stt_enabled' => true,
        'recording_enabled' => true,
        'storage_provider' => 'wasabi',
    ]);

    $this->room->refresh();
});

test('can create session marker for all participants', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    $markers = $action->execute(
        identifier: 'Session Start',
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 120, // 2 minutes
        sttTime: 115    // 1 minute 55 seconds
    );

    expect($markers)->toHaveCount(2);

    // Check that all markers share the same UUID
    $uuids = $markers->pluck('uuid')->unique();
    expect($uuids)->toHaveCount(1);

    // Check that markers were created for both users
    $userIds = $markers->pluck('user_id')->sort()->values()->toArray();
    expect($userIds)->toEqual([$this->user->id, $this->otherUser->id]);

    // Check marker properties
    $marker = $markers->first();
    expect($marker->identifier)->toBe('Session Start');
    expect($marker->creator_id)->toBe($this->user->id);
    expect($marker->room_id)->toBe($this->room->id);
    expect($marker->video_time)->toBe(120);
    expect($marker->stt_time)->toBe(115);
});

test('can create session marker with active recording', function () {
    // Create an active recording
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'status' => 'recording',
        'provider' => 'wasabi',
    ]);

    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    $markers = $action->execute(
        identifier: 'Break Start',
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 300,
        sttTime: 295
    );

    // Check that recording_id is set
    $markers->each(function ($marker) use ($recording) {
        expect($marker->recording_id)->toBe($recording->id);
    });
});

test('can create session marker without identifier', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    $markers = $action->execute(
        identifier: null,
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 60,
        sttTime: null
    );

    expect($markers)->toHaveCount(2);

    $marker = $markers->first();
    expect($marker->identifier)->toBeNull();
    expect($marker->video_time)->toBe(60);
    expect($marker->stt_time)->toBeNull();
});

test('session marker model has correct relationships', function () {
    $marker = SessionMarker::factory()->create([
        'room_id' => $this->room->id,
        'creator_id' => $this->user->id,
        'user_id' => $this->otherUser->id,
        'identifier' => 'Test Marker',
        'video_time' => 180,
        'stt_time' => 175,
    ]);

    // Test relationships
    expect($marker->room)->toBeInstanceOf(Room::class);
    expect($marker->room->id)->toBe($this->room->id);

    expect($marker->creator)->toBeInstanceOf(User::class);
    expect($marker->creator->id)->toBe($this->user->id);

    expect($marker->user)->toBeInstanceOf(User::class);
    expect($marker->user->id)->toBe($this->otherUser->id);
});

test('session marker model has correct formatted time methods', function () {
    $marker = SessionMarker::factory()->create([
        'video_time' => 125, // 2:05
        'stt_time' => 3661,  // 61:01
    ]);

    expect($marker->getFormattedVideoTime())->toBe('2:05');
    expect($marker->getFormattedSttTime())->toBe('61:01');
});

test('session marker model handles null times correctly', function () {
    $marker = SessionMarker::factory()->create([
        'video_time' => null,
        'stt_time' => null,
    ]);

    expect($marker->getFormattedVideoTime())->toBeNull();
    expect($marker->getFormattedSttTime())->toBeNull();
    expect($marker->hasVideoTime())->toBeFalse();
    expect($marker->hasSttTime())->toBeFalse();
});

test('session marker model scopes work correctly', function () {
    // Create markers for different rooms and users
    $otherRoom = Room::factory()->create();

    SessionMarker::factory()->create([
        'room_id' => $this->room->id,
        'creator_id' => $this->user->id,
        'user_id' => $this->user->id,
        'uuid' => 'test-uuid-1',
    ]);

    SessionMarker::factory()->create([
        'room_id' => $otherRoom->id,
        'creator_id' => $this->user->id,
        'user_id' => $this->user->id,
        'uuid' => 'test-uuid-2',
    ]);

    SessionMarker::factory()->create([
        'room_id' => $this->room->id,
        'creator_id' => $this->otherUser->id,
        'user_id' => $this->otherUser->id,
        'uuid' => 'test-uuid-3',
    ]);

    // Test scopes
    expect(SessionMarker::forRoom($this->room->id)->count())->toBe(2);
    expect(SessionMarker::byCreator($this->user->id)->count())->toBe(2);
    expect(SessionMarker::byUuid('test-uuid-1')->count())->toBe(1);
});

test('api endpoint creates session marker successfully', function () {
    $this->actingAs($this->user);

    $response = $this->postJson('/api/session-markers', [
        'room_id' => $this->room->id,
        'identifier' => 'Session Start',
        'video_time' => 60,
        'stt_time' => 58,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'uuid',
                'identifier',
                'markers_created',
                'video_time',
                'stt_time',
            ],
        ]);

    expect($response->json('data.markers_created'))->toBe(2);
    expect($response->json('data.identifier'))->toBe('Session Start');

    // Check database
    expect(SessionMarker::count())->toBe(2);
    expect(SessionMarker::where('identifier', 'Session Start')->count())->toBe(2);
});

test('api endpoint requires authentication', function () {
    $response = $this->postJson('/api/session-markers', [
        'room_id' => $this->room->id,
        'identifier' => 'Session Start',
    ]);

    $response->assertStatus(401);
});

test('api endpoint validates required fields', function () {
    $this->actingAs($this->user);

    $response = $this->postJson('/api/session-markers', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['room_id']);
});

test('api endpoint prevents access to non-participant rooms', function () {
    $this->actingAs($this->user);

    // Create a room where user is not a participant
    $otherRoom = Room::factory()->create();

    $response = $this->postJson('/api/session-markers', [
        'room_id' => $otherRoom->id,
        'identifier' => 'Session Start',
    ]);

    $response->assertStatus(403);
});

test('api endpoint can retrieve session markers for room', function () {
    $this->actingAs($this->user);

    // Create some markers
    SessionMarker::factory()->count(3)->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'creator_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/session-markers?'.http_build_query([
        'room_id' => $this->room->id,
    ]));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'uuid',
                    'identifier',
                    'creator_id',
                    'user_id',
                    'room_id',
                    'video_time',
                    'stt_time',
                    'created_at',
                ],
            ],
        ]);

    expect(count($response->json('data')))->toBe(3);
});

test('can create automatic join markers for participants', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    // Simulate automatic join markers
    $markers = $action->execute(
        identifier: 'Test Character joined',
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 0, // Just joined, so video time is 0 or very low
        sttTime: 0    // Just joined, so STT time is 0 or very low
    );

    expect($markers)->toHaveCount(2);

    // Check that the identifier indicates a join event
    $marker = $markers->first();
    expect($marker->identifier)->toBe('Test Character joined');
    expect($marker->creator_id)->toBe($this->user->id);
    expect($marker->video_time)->toBe(0);
    expect($marker->stt_time)->toBe(0);
});

test('join markers are created with correct timing when recording already started', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    // Simulate a player joining after recording has been running for 5 minutes
    $markers = $action->execute(
        identifier: 'Late Joiner joined',
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 300, // 5 minutes of video recording
        sttTime: 295    // 4 minutes 55 seconds of STT
    );

    expect($markers)->toHaveCount(2);

    $marker = $markers->first();
    expect($marker->identifier)->toBe('Late Joiner joined');
    expect($marker->video_time)->toBe(300);
    expect($marker->stt_time)->toBe(295);

    // Verify formatted times
    expect($marker->getFormattedVideoTime())->toBe('5:00');
    expect($marker->getFormattedSttTime())->toBe('4:55');
});

test('join markers include character names when available', function () {
    // Test that the join marker uses character name when available
    $characterName = 'Aragorn the Ranger';

    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    $markers = $action->execute(
        identifier: "{$characterName} joined",
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 30,
        sttTime: 25
    );

    $marker = $markers->first();
    expect($marker->identifier)->toBe("{$characterName} joined");
    expect($marker->getDisplayName())->toBe("{$characterName} joined");
});

test('automatic join markers work with different recording states', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    // Test 1: Only STT enabled (no video recording)
    $markers1 = $action->execute(
        identifier: 'Player joined',
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: null, // No video recording
        sttTime: 60      // STT is running
    );

    $marker1 = $markers1->first();
    expect($marker1->video_time)->toBeNull();
    expect($marker1->stt_time)->toBe(60);
    expect($marker1->hasSttTime())->toBeTrue();
    expect($marker1->hasVideoTime())->toBeFalse();

    // Test 2: Only video recording enabled (no STT)
    $markers2 = $action->execute(
        identifier: 'Another Player joined',
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 120,  // Video recording is running
        sttTime: null    // No STT
    );

    $marker2 = $markers2->first();
    expect($marker2->video_time)->toBe(120);
    expect($marker2->stt_time)->toBeNull();
    expect($marker2->hasVideoTime())->toBeTrue();
    expect($marker2->hasSttTime())->toBeFalse();
});

test('can create automatic leave markers for participants', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    // Simulate automatic leave markers after some session time
    $markers = $action->execute(
        identifier: 'Test Character left',
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 1800, // 30 minutes into the session
        sttTime: 1795    // 29 minutes 55 seconds of STT
    );

    expect($markers)->toHaveCount(2);

    // Check that the identifier indicates a leave event
    $marker = $markers->first();
    expect($marker->identifier)->toBe('Test Character left');
    expect($marker->creator_id)->toBe($this->user->id);
    expect($marker->video_time)->toBe(1800);
    expect($marker->stt_time)->toBe(1795);

    // Verify formatted times
    expect($marker->getFormattedVideoTime())->toBe('30:00');
    expect($marker->getFormattedSttTime())->toBe('29:55');
});

test('join and leave markers create a complete player timeline', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    $playerName = 'Gimli the Dwarf';

    // Player joins at the beginning
    $joinMarkers = $action->execute(
        identifier: "{$playerName} joined",
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 0,
        sttTime: 0
    );

    // Player leaves after 45 minutes
    $leaveMarkers = $action->execute(
        identifier: "{$playerName} left",
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 2700, // 45 minutes
        sttTime: 2695    // 44 minutes 55 seconds
    );

    // Check join markers
    expect($joinMarkers)->toHaveCount(2);
    $joinMarker = $joinMarkers->first();
    expect($joinMarker->identifier)->toBe("{$playerName} joined");
    expect($joinMarker->video_time)->toBe(0);
    expect($joinMarker->stt_time)->toBe(0);

    // Check leave markers
    expect($leaveMarkers)->toHaveCount(2);
    $leaveMarker = $leaveMarkers->first();
    expect($leaveMarker->identifier)->toBe("{$playerName} left");
    expect($leaveMarker->video_time)->toBe(2700);
    expect($leaveMarker->stt_time)->toBe(2695);

    // Verify we have a complete timeline for this player across all participants
    $allMarkers = SessionMarker::where('room_id', $this->room->id)
        ->where('identifier', 'like', "%{$playerName}%")
        ->orderBy('created_at')
        ->get();

    expect($allMarkers)->toHaveCount(4); // 2 join + 2 leave (2 participants × 2 events each)
});

test('automatic markers work when players join and leave multiple times', function () {
    $action = new CreateSessionMarkerForAllParticipants(
        new \Domain\Room\Actions\CreateSessionMarker
    );

    $playerName = 'Legolas the Elf';

    // First session: Join at start, leave after 20 minutes
    $firstJoin = $action->execute(
        identifier: "{$playerName} joined",
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 0,
        sttTime: 0
    );

    $firstLeave = $action->execute(
        identifier: "{$playerName} left",
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 1200, // 20 minutes
        sttTime: 1195
    );

    // Second session: Rejoin after 5 minutes break, leave at end
    $secondJoin = $action->execute(
        identifier: "{$playerName} joined",
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 1500, // 25 minutes (after 5 minute break)
        sttTime: 1495
    );

    $secondLeave = $action->execute(
        identifier: "{$playerName} left",
        creatorId: $this->user->id,
        roomId: $this->room->id,
        videoTime: 3600, // 60 minutes (end of session)
        sttTime: 3595
    );

    // Verify all markers were created
    expect($firstJoin)->toHaveCount(2);
    expect($firstLeave)->toHaveCount(2);
    expect($secondJoin)->toHaveCount(2);
    expect($secondLeave)->toHaveCount(2);

    // Check that we have a complete history
    $playerMarkers = SessionMarker::where('room_id', $this->room->id)
        ->where('identifier', 'like', "%{$playerName}%")
        ->orderBy('video_time')
        ->get();

    expect($playerMarkers)->toHaveCount(8); // 4 events × 2 participants each

    // Verify the timeline makes sense
    $userMarkers = $playerMarkers->where('user_id', $this->user->id)->values();
    expect($userMarkers[0]->identifier)->toBe("{$playerName} joined"); // First join
    expect($userMarkers[0]->video_time)->toBe(0);
    expect($userMarkers[1]->identifier)->toBe("{$playerName} left");   // First leave
    expect($userMarkers[1]->video_time)->toBe(1200);
    expect($userMarkers[2]->identifier)->toBe("{$playerName} joined"); // Second join
    expect($userMarkers[2]->video_time)->toBe(1500);
    expect($userMarkers[3]->identifier)->toBe("{$playerName} left");   // Second leave
    expect($userMarkers[3]->video_time)->toBe(3600);
});
