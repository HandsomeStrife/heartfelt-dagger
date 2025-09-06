<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;

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

    // Visit the room session page
    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->assertSee('Speech Recording Consent') // Should see consent dialog
        ->assertSee('Your voice will be transcribed and saved')
        ->assertSee('Yes, I Consent')
        ->assertSee('No, Leave Room');
});

test('consent popup shows immediately when entering room with required recording', function () {
    // Create user, campaign, and room
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    // Create recording settings with required video recording consent
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'recording_consent_requirement' => 'required',
        'storage_provider' => 'local_device',
    ]);

    // Visit the room session page
    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->assertSee('Video Recording Consent') // Should see consent dialog
        ->assertSee('Your video will be recorded and saved')
        ->assertSee('Yes, I Consent')
        ->assertSee('No, Leave Room');
});

test('user can consent to required STT and continue', function () {
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

    // Visit the room session page and consent
    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->assertSee('Speech Recording Consent')
        ->click('Yes, I Consent')
        ->waitForText('Join Room') // Should see join button after consenting
        ->assertDontSee('Speech Recording Consent'); // Dialog should be gone
});

test('user is redirected when declining required STT consent', function () {
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

    // Visit the room session page and decline
    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->assertSee('Speech Recording Consent')
        ->click('No, Leave Room')
        ->waitForText('Consent Required') // Should see denial message
        ->assertSee('You have declined the required permissions')
        ->assertSee('Redirecting in'); // Should show countdown
});

test('user can decline optional STT consent and continue', function () {
    // Create user, campaign, and room
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    // Create recording settings with optional STT consent
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'stt_consent_requirement' => 'optional',
    ]);

    // Visit the room session page and decline
    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->assertSee('Speech Recording Consent')
        ->click('No, Leave Room')
        ->waitForText('Join Room') // Should see join button after declining optional consent
        ->assertDontSee('Speech Recording Consent'); // Dialog should be gone
});

test('no consent popup shows when STT and recording are disabled', function () {
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

    // Visit the room session page
    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->assertSee('Join Room') // Should see join button immediately
        ->assertDontSee('Speech Recording Consent')
        ->assertDontSee('Video Recording Consent');
});

test('both STT and recording consent popups show sequentially when both required', function () {
    // Create user, campaign, and room
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    // Create recording settings with both required
    RoomRecordingSettings::factory()->create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => true,
        'stt_consent_requirement' => 'required',
        'recording_consent_requirement' => 'required',
        'storage_provider' => 'local_device',
    ]);

    // Visit the room session page
    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->assertSee('Speech Recording Consent') // Should see first consent dialog
        ->click('Yes, I Consent')
        ->waitForText('Video Recording Consent') // Should see second consent dialog
        ->click('Yes, I Consent')
        ->waitForText('Join Room') // Should see join button after both consents
        ->assertDontSee('Speech Recording Consent')
        ->assertDontSee('Video Recording Consent');
});
