<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;

test('user sees consent dialog when STT is enabled and no prior consent given', function () {
    // Create a test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    $page = loginAs($user)->visit("/rooms/{$room->invite_code}/session");
    
    $page->waitFor('[data-slot-id="1"]')
        ->click('.join-btn')
        ->waitFor('#stt-consent-backdrop', 10)
        ->assertSee('Speech Recording Consent')
        ->assertSee('This room has speech-to-text recording enabled')
        ->assertSee('Yes, I Consent')
        ->assertSee('No, Leave Room');
});

test('user can grant STT consent and speech recognition starts', function () {
    // Create a test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    $page = loginAs($user)->visit("/rooms/{$room->invite_code}/session");
    
    $page->waitFor('[data-slot-id="1"]')
        ->click('.join-btn')
        ->waitFor('#stt-consent-backdrop', 10)
        ->click('#stt-consent-accept')
        ->waitUntilMissing('#stt-consent-backdrop', 5);

    // Verify consent was saved in database
    $participant = RoomParticipant::where('room_id', $room->id)
        ->where('user_id', $user->id)
        ->first();
    
    expect($participant->hasSttConsent())->toBe(true);
    expect($participant->stt_consent_at)->not->toBeNull();
});

test('user can deny STT consent and gets redirected out of room', function () {
    // Create a test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    $page = loginAs($user)->visit("/rooms/{$room->invite_code}/session");
    
    $page->waitFor('[data-slot-id="1"]')
        ->click('.join-btn')
        ->waitFor('#stt-consent-backdrop', 10)
        ->click('#stt-consent-deny')
        ->waitUntilMissing('#stt-consent-backdrop', 5)
        ->assertSee('Consent Required')
        ->assertSee('You have declined speech recording consent')
        ->assertSee('Redirecting in');

    // Wait for redirect (should happen in 3 seconds)
    $page->waitForLocation("/rooms/{$room->invite_code}", 10);

    // Verify consent denial was saved in database
    $participant = RoomParticipant::where('room_id', $room->id)
        ->where('user_id', $user->id)
        ->first();
    
    expect($participant->hasDeniedSttConsent())->toBe(true);
    expect($participant->stt_consent_at)->not->toBeNull();
});

test('user with prior consent denial gets immediately redirected', function () {
    // Create a test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participant with denied consent
    $participant = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => false,
        'stt_consent_at' => now(),
    ]);

    $page = loginAs($user)->visit("/rooms/{$room->invite_code}/session");
    
    $page->waitFor('[data-slot-id="1"]')
        ->click('.join-btn')
        ->assertSee('Consent Required')
        ->assertSee('You have declined speech recording consent')
        ->waitForLocation("/rooms/{$room->invite_code}", 10);
});

test('user with prior consent approval automatically starts speech recognition', function () {
    // Create a test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Enable STT for the room
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => true,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    // Create participant with granted consent
    $participant = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    $page = loginAs($user)->visit("/rooms/{$room->invite_code}/session");
    
    $page->waitFor('[data-slot-id="1"]')
        ->click('.join-btn')
        ->pause(2000); // Wait for potential consent dialog

    // Should NOT see consent dialog
    $page->assertMissing('#stt-consent-backdrop');
    
    // Should see speech recognition active (console logs would show this)
    // We can't directly test speech recognition in browser tests, but we can verify
    // that the consent flow was bypassed
});

test('STT consent dialog does not appear when STT is disabled', function () {
    // Create a test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Leave STT disabled (default) - no recording settings created

    $page = loginAs($user)->visit("/rooms/{$room->invite_code}/session");
    
    $page->waitFor('[data-slot-id="1"]')
        ->click('.join-btn')
        ->pause(2000); // Wait for potential consent dialog

    // Should NOT see consent dialog
    $page->assertMissing('#stt-consent-backdrop');
});

// Additional browser tests for the UI interactions can be added here
// API tests have been moved to tests/Feature/SttConsentApiTest.php
