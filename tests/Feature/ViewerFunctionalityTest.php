<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;

test('viewer can access room without password', function () {
    $room = Room::factory()->create();

    $response = $this->get(route('rooms.viewer', $room->viewer_code));

    $response->assertOk();
    $response->assertViewIs('rooms.viewer');
});

test('viewer password form shows when password is set', function () {
    $room = Room::factory()->create();

    // Create recording settings with viewer password
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => false,
        'viewer_password' => \Hash::make('secret123'),
    ]);

    $response = $this->get(route('rooms.viewer', $room->viewer_code));

    $response->assertOk();
    $response->assertViewIs('rooms.viewer-password');
});

test('viewer can access with correct password', function () {
    $room = Room::factory()->create();

    // Create recording settings with viewer password
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => false,
        'viewer_password' => \Hash::make('secret123'),
    ]);

    $response = $this->get(route('rooms.viewer', $room->viewer_code).'?password=secret123');

    $response->assertOk();
    $response->assertViewIs('rooms.viewer');
});

test('viewer password form submission works', function () {
    $room = Room::factory()->create();

    // Create recording settings with viewer password
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => false,
        'viewer_password' => \Hash::make('secret123'),
    ]);

    $response = $this->post(route('rooms.viewer.password', $room->viewer_code), [
        'password' => 'secret123',
    ]);

    $response->assertRedirect(route('rooms.viewer', $room->viewer_code).'?password=secret123');
});

test('viewer password form rejects wrong password', function () {
    $room = Room::factory()->create();

    // Create recording settings with viewer password
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => false,
        'viewer_password' => \Hash::make('secret123'),
    ]);

    $response = $this->post(route('rooms.viewer.password', $room->viewer_code), [
        'password' => 'wrong_password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['password' => 'Invalid viewer password.']);
});
