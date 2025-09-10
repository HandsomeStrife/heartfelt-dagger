<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

test('consent popup shows immediately when entering room with required STT', function () {
    // Create user, campaign, and room
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    // Create recording settings with required STT consent
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'stt_consent_requirement' => 'required',
    ]);

    // Add user as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);

    // Login and visit the room session page
    actingAs($user);
    $page = visit("/rooms/{$room->invite_code}/session");

    // Wait for consent dialog to appear and verify content
    $page->waitForText('Speech Recording Consent', 10);
    $page->assertSee('Your voice will be transcribed and saved');
    $page->assertSee('Yes, I Consent');
    $page->assertSee('No, Leave Room');
});

test('user can join room when no consent required', function () {
    // Create user, campaign, and room
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    // Create recording settings with both disabled
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => false,
    ]);

    // Add user as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);

    // Login and visit the room session page
    actingAs($user);
    $page = visit("/rooms/{$room->invite_code}/session");

    // Should see join button immediately without consent dialogs
    $page->waitForText('Join Room', 10);
    $page->assertDontSee('Speech Recording Consent');
    $page->assertDontSee('Video Recording Consent');
});
