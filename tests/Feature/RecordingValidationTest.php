<?php

declare(strict_types=1);

use Domain\Room\Enums\RecordingStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;

describe('Recording Validation API', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->room = Room::factory()->create(['creator_id' => $this->user->id]);
        
        // Create a recording-enabled room
        $this->recordingSettings = RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
        ]);
        
        $this->room->load('recordingSettings');
    });

    test('validates session when recording is disabled', function () {
        // Disable recording
        $this->recordingSettings->update(['recording_enabled' => false]);
        $this->room->refresh();

        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertOk()
            ->assertJson([
                'recording_enabled' => false,
                'recording_entry_exists' => false,
                'message' => 'Recording is not enabled for this room'
            ]);
    });

    test('validates session when no active recording exists', function () {
        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertOk()
            ->assertJson([
                'recording_enabled' => true,
                'recording_entry_exists' => false,
                'message' => 'No active recording session found'
            ]);
    });

    test('validates session when active recording exists', function () {
        // Create an active recording
        $recording = RoomRecording::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'status' => RecordingStatus::Recording,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertOk()
            ->assertJson([
                'recording_enabled' => true,
                'recording_entry_exists' => true,
                'recording_id' => $recording->id,
                'recording_status' => 'recording',
                'message' => 'Active recording session found'
            ]);
    });

    test('validates session when finalizing recording exists', function () {
        // Create a finalizing recording
        $recording = RoomRecording::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'status' => RecordingStatus::Finalizing,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertOk()
            ->assertJson([
                'recording_enabled' => true,
                'recording_entry_exists' => true,
                'recording_id' => $recording->id,
                'recording_status' => 'finalizing',
                'message' => 'Active recording session found'
            ]);
    });

    test('ignores completed recordings', function () {
        // Create a completed recording (should be ignored)
        RoomRecording::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'status' => RecordingStatus::Uploaded,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertOk()
            ->assertJson([
                'recording_enabled' => true,
                'recording_entry_exists' => false,
                'message' => 'No active recording session found'
            ]);
    });

    test('returns most recent active recording', function () {
        // Create multiple active recordings
        $oldRecording = RoomRecording::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'status' => RecordingStatus::Recording,
            'created_at' => now()->subMinutes(10),
        ]);

        $newRecording = RoomRecording::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'status' => RecordingStatus::Recording,
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertOk()
            ->assertJson([
                'recording_enabled' => true,
                'recording_entry_exists' => true,
                'recording_id' => $newRecording->id,
            ]);
    });

    test('requires authentication', function () {
        $response = $this->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertUnauthorized()
            ->assertJson(['error' => 'Authentication required']);
    });

    test('handles missing room gracefully', function () {
        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/99999/recordings/validate-session");

        $response->assertNotFound();
    });

    test('only shows recordings for current user', function () {
        $otherUser = User::factory()->create();
        
        // Create recording for other user
        RoomRecording::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $otherUser->id,
            'status' => RecordingStatus::Recording,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/rooms/{$this->room->id}/recordings/validate-session");

        $response->assertOk()
            ->assertJson([
                'recording_enabled' => true,
                'recording_entry_exists' => false,
                'message' => 'No active recording session found'
            ]);
    });
});
