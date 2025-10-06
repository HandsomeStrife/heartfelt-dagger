<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

describe('Room Consent Error Handling', function () {
    beforeEach(function () {
        $this->gm = User::factory()->create();
        $this->room = Room::factory()->create([
            'creator_id' => $this->gm->id,
            'guest_count' => 2,
        ]);

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
        ]);
    });

    test('stt consent endpoint returns consent status', function () {
        actingAs($this->gm);

        // Enable STT for room
        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'stt_enabled' => true,
            'stt_provider' => 'browser',
        ]);

        $response = $this->get("/api/rooms/{$this->room->id}/stt-consent");

        $response->assertOk();
        $response->assertJsonStructure([
            'requires_consent',
            'consent_given',
            'consent_denied',
            'consent_required',
        ]);
    });

    test('recording consent endpoint returns consent status', function () {
        actingAs($this->gm);

        // Enable recording for room
        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'recording_enabled' => true,
            'storage_provider' => 'local_device',
        ]);

        $response = $this->get("/api/rooms/{$this->room->id}/recording-consent");

        $response->assertOk();
        $response->assertJsonStructure([
            'requires_consent',
            'consent_given',
            'consent_denied',
            'consent_required',
        ]);
    });

    test('local save consent endpoint returns consent status', function () {
        actingAs($this->gm);

        // Enable recording with remote storage
        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
        ]);

        $response = $this->get("/api/rooms/{$this->room->id}/local-save-consent");

        $response->assertOk();
        $response->assertJsonStructure([
            'requires_consent',
            'consent_given',
            'consent_denied',
        ]);
    });

    test('can save stt consent decision', function () {
        actingAs($this->gm);

        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'stt_enabled' => true,
            'stt_provider' => 'browser',
        ]);

        $response = $this->post("/api/rooms/{$this->room->id}/stt-consent", [
            'consent_given' => true,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'consent_given' => true,
        ]);

        $this->assertDatabaseHas('room_participants', [
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
            'stt_consent_given' => true,
        ]);
    });

    test('can save recording consent decision', function () {
        actingAs($this->gm);

        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'recording_enabled' => true,
            'storage_provider' => 'local_device',
        ]);

        $response = $this->post("/api/rooms/{$this->room->id}/recording-consent", [
            'consent_given' => true,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'consent_given' => true,
        ]);

        $this->assertDatabaseHas('room_participants', [
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
            'recording_consent_given' => true,
        ]);
    });

    test('can deny consent', function () {
        actingAs($this->gm);

        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'stt_enabled' => true,
            'stt_provider' => 'browser',
        ]);

        $response = $this->post("/api/rooms/{$this->room->id}/stt-consent", [
            'consent_given' => false,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'consent_given' => false,
        ]);

        $this->assertDatabaseHas('room_participants', [
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
            'stt_consent_given' => false,
        ]);
    });

    test('consent endpoint allows unauthenticated access for guests', function () {
        // Consent endpoints don't require authentication since guests can join rooms
        $response = $this->get("/api/rooms/{$this->room->id}/stt-consent");

        // Should return OK or redirect, not 401
        $response->assertStatus(200);
    });

    test('consent save requires valid consent_given boolean', function () {
        actingAs($this->gm);

        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'stt_enabled' => true,
        ]);

        $response = $this->post("/api/rooms/{$this->room->id}/stt-consent", [
            'consent_given' => 'invalid',
        ]);

        $response->assertStatus(422);
    });

    test('returns 404 for non-existent room', function () {
        actingAs($this->gm);

        $response = $this->get('/api/rooms/99999/stt-consent');

        $response->assertStatus(404);
    });

    test('consent persists across multiple checks', function () {
        actingAs($this->gm);

        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'stt_enabled' => true,
        ]);

        // Give consent
        $this->post("/api/rooms/{$this->room->id}/stt-consent", [
            'consent_given' => true,
        ]);

        // Check consent again
        $response = $this->get("/api/rooms/{$this->room->id}/stt-consent");

        $response->assertOk();
        $response->assertJson([
            'consent_given' => true,
            'requires_consent' => false,
        ]);
    });

    test('different users have independent consent status', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $user1->id,
        ]);

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $user2->id,
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'stt_enabled' => true,
        ]);

        // User 1 gives consent
        actingAs($user1);
        $this->post("/api/rooms/{$this->room->id}/stt-consent", [
            'consent_given' => true,
        ]);

        // User 2 checks their consent (should still require consent)
        actingAs($user2);
        $response = $this->get("/api/rooms/{$this->room->id}/stt-consent");

        $response->assertOk();
        $response->assertJson([
            'requires_consent' => true,
            'consent_given' => false,
        ]);
    });
});

