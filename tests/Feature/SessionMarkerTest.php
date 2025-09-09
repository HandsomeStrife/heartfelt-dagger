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
        new \Domain\Room\Actions\CreateSessionMarker()
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
        new \Domain\Room\Actions\CreateSessionMarker()
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
        new \Domain\Room\Actions\CreateSessionMarker()
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
            ]
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
    
    $response = $this->getJson('/api/session-markers?' . http_build_query([
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
                ]
            ]
        ]);
    
    expect(count($response->json('data')))->toBe(3);
});
