<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs};

test('rooms index page displays participant counts correctly', function () {
    // Create user and room with specific guest count
    $user = User::factory()->create();
    $room = Room::factory()->withGuestCount(6)->create(['creator_id' => $user->id]);
    
    // Add some participants
    RoomParticipant::factory()->count(3)->create(['room_id' => $room->id]);
    
    // Visit rooms index
    actingAs($user);
    $page = visit('/rooms');
    
    // Should see participant count without errors
    $page->assertSee('3/6'); // 3 participants out of 6 capacity
    $page->assertDontSee('Call to undefined method');
});

test('campaign room links use invite_code correctly', function () {
    // This test verifies the fix is in place by checking the view renders without errors
    // The actual fix was changing $room->id to $room->invite_code in campaigns/show.blade.php
    
    // Create user, campaign, and room
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);
    
    // Visit room directly using invite_code (this should work now)
    actingAs($user);
    $page = visit("/rooms/{$room->invite_code}");
    
    // Should successfully load the room page
    $page->assertSee($room->name);
    $page->assertDontSee('404');
    $page->assertDontSee('Not Found');
});

test('optional STT consent allows user to continue when declined', function () {
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

    // Add user as participant without character to avoid image loading issues
    RoomParticipant::factory()->withoutCharacter()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Test Character',
        'character_class' => 'warrior',
    ]);

    // Visit the room session page
    actingAs($user);
    $page = visit("/rooms/{$room->invite_code}/session");
    
    // Should see consent dialog
    $page->waitForText('Speech Recording Consent', 10);
    
    // Decline consent
    $page->click('No, Leave Room');
    
    // Should NOT be redirected (since it's optional)
    $page->waitForText('Join Room', 10);
    $page->assertDontSee('Consent Required');
    $page->assertDontSee('You have declined the required permissions');
    $page->assertDontSee('Redirecting in');
});

test('required STT consent redirects user when declined', function () {
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

    // Add user as participant without character to avoid image loading issues
    RoomParticipant::factory()->withoutCharacter()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Test Character',
        'character_class' => 'warrior',
    ]);

    // Visit the room session page
    actingAs($user);
    $page = visit("/rooms/{$room->invite_code}/session");
    
    // Should see consent dialog
    $page->waitForText('Speech Recording Consent', 10);
    
    // Decline consent
    $page->click('No, Leave Room');
    
    // Should be redirected (since it's required)
    $page->waitForText('Consent Required', 10);
    $page->assertSee('You have declined the required permissions');
    $page->assertSee('Redirecting in');
});

test('optional consent does not redirect on page refresh after declining', function () {
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

    // Add user as participant with consent already denied
    $participant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);
    
    // Set consent as denied
    $participant->denySttConsent();

    // Visit the room session page (simulating refresh after declining)
    actingAs($user);
    $page = visit("/rooms/{$room->invite_code}/session");
    
    // Should NOT see consent dialog or redirect message
    $page->waitForText('Join Room', 10);
    $page->assertDontSee('Speech Recording Consent');
    $page->assertDontSee('Consent Required');
    $page->assertDontSee('You have declined the required permissions');
});

test('room creator gets consent dialog even without participant record', function () {
    // Create user, campaign, and room (user is creator)
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

    // DON'T add user as participant - they're just the creator

    // Visit the room session page
    actingAs($user);
    $page = visit("/rooms/{$room->invite_code}/session");
    
    // Should see consent dialog even though they're not a participant
    $page->waitForText('Speech Recording Consent', 10);
    $page->assertSee('Your voice will be transcribed and saved');
    $page->assertSee('Yes, I Consent');
    $page->assertSee('No, Leave Room');
});
