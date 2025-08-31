<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;

test('transcript API rejects requests without consent', function () {
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

    // Create participant without consent
    $participant = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => null, // No consent decision
        'stt_consent_at' => null,
    ]);

    // Attempt to create transcript via API
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/transcripts", [
            'user_id' => $user->id,
            'started_at_ms' => now()->timestamp * 1000,
            'ended_at_ms' => (now()->timestamp + 5) * 1000,
            'text' => 'Test transcript text',
            'language' => 'en-US',
            'confidence' => 0.95,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Speech-to-text consent required',
            'requires_consent' => true
        ]);
});

test('transcript API accepts requests with valid consent', function () {
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

    // Create transcript via API
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/transcripts", [
            'user_id' => $user->id,
            'started_at_ms' => now()->timestamp * 1000,
            'ended_at_ms' => (now()->timestamp + 5) * 1000,
            'text' => 'Test transcript text',
            'language' => 'en-US',
            'confidence' => 0.95,
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Transcript saved successfully'
        ]);
});

test('consent API endpoint works correctly', function () {
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

    // Create participant without consent
    $participant = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'stt_consent_given' => null,
        'stt_consent_at' => null,
    ]);

    // Test getting consent status
    $response = $this->actingAs($user)
        ->getJson("/api/rooms/{$room->id}/stt-consent");

    $response->assertStatus(200)
        ->assertJson([
            'stt_enabled' => true,
            'requires_consent' => true,
            'consent_given' => false,
            'consent_denied' => false
        ]);

    // Test granting consent
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/stt-consent", [
            'consent_given' => true
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'consent_given' => true,
            'should_redirect' => false,
            'message' => 'Speech-to-text consent granted'
        ]);

    // Verify consent was saved
    $participant->refresh();
    expect($participant->hasSttConsent())->toBe(true);

    // Test denying consent
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/stt-consent", [
            'consent_given' => false
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'consent_given' => false,
            'should_redirect' => true,
            'message' => 'Speech-to-text consent denied'
        ]);

    // Verify consent denial was saved
    $participant->refresh();
    expect($participant->hasDeniedSttConsent())->toBe(true);
});

test('consent cannot be bypassed by malicious users', function () {
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

    // Try to create transcript despite denied consent
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/transcripts", [
            'user_id' => $user->id,
            'started_at_ms' => now()->timestamp * 1000,
            'ended_at_ms' => (now()->timestamp + 5) * 1000,
            'text' => 'Malicious transcript attempt',
            'language' => 'en-US',
            'confidence' => 0.95,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Speech-to-text consent required',
            'requires_consent' => true
        ]);

    // Try to submit transcript for another user (should also fail)
    $otherUser = User::factory()->create();
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/transcripts", [
            'user_id' => $otherUser->id, // Different user
            'started_at_ms' => now()->timestamp * 1000,
            'ended_at_ms' => (now()->timestamp + 5) * 1000,
            'text' => 'Malicious transcript attempt',
            'language' => 'en-US',
            'confidence' => 0.95,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'User is not an active participant in this room'
        ]);
});

test('STT is disabled when no recording settings exist', function () {
    // Create a test user and room
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // No recording settings created - STT should be disabled

    // Create participant 
    $participant = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);

    // Test getting consent status - should show STT disabled
    $response = $this->actingAs($user)
        ->getJson("/api/rooms/{$room->id}/stt-consent");

    $response->assertStatus(200)
        ->assertJson([
            'stt_enabled' => false,
            'requires_consent' => false,
            'consent_given' => null
        ]);

    // Try to create transcript - should fail because STT is disabled
    $response = $this->actingAs($user)
        ->postJson("/api/rooms/{$room->id}/transcripts", [
            'user_id' => $user->id,
            'started_at_ms' => now()->timestamp * 1000,
            'ended_at_ms' => (now()->timestamp + 5) * 1000,
            'text' => 'Test transcript text',
            'language' => 'en-US',
            'confidence' => 0.95,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Speech-to-text is not enabled for this room'
        ]);
});

test('room participant consent model methods work correctly', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // Test no consent decision
    $participant = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);

    expect($participant->hasNoSttConsentDecision())->toBe(true);
    expect($participant->hasSttConsent())->toBe(false);
    expect($participant->hasDeniedSttConsent())->toBe(false);

    // Test granting consent
    $participant->grantSttConsent();
    $participant->refresh();

    expect($participant->hasNoSttConsentDecision())->toBe(false);
    expect($participant->hasSttConsent())->toBe(true);
    expect($participant->hasDeniedSttConsent())->toBe(false);
    expect($participant->stt_consent_at)->not->toBeNull();

    // Test denying consent
    $participant->denySttConsent();
    $participant->refresh();

    expect($participant->hasNoSttConsentDecision())->toBe(false);
    expect($participant->hasSttConsent())->toBe(false);
    expect($participant->hasDeniedSttConsent())->toBe(true);
    expect($participant->stt_consent_at)->not->toBeNull();

    // Test resetting consent
    $participant->resetSttConsent();
    $participant->refresh();

    expect($participant->hasNoSttConsentDecision())->toBe(true);
    expect($participant->hasSttConsent())->toBe(false);
    expect($participant->hasDeniedSttConsent())->toBe(false);
    expect($participant->stt_consent_at)->toBeNull();
});

test('room participant scopes work correctly', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user1->id]);

    // Create participants with different consent states
    $participant1 = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user1->id,
        'character_name' => 'User 1',
        'character_class' => 'Warrior',
        'stt_consent_given' => true,
        'stt_consent_at' => now(),
    ]);

    $participant2 = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user2->id,
        'character_name' => 'User 2',
        'character_class' => 'Rogue',
        'stt_consent_given' => false,
        'stt_consent_at' => now(),
    ]);

    $participant3 = RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user3->id,
        'character_name' => 'User 3',
        'character_class' => 'Wizard',
        'stt_consent_given' => null,
        'stt_consent_at' => null,
    ]);

    // Test scopes
    $withConsent = RoomParticipant::where('room_id', $room->id)->withSttConsent()->get();
    expect($withConsent)->toHaveCount(1);
    expect($withConsent->first()->user_id)->toBe($user1->id);

    $withoutConsent = RoomParticipant::where('room_id', $room->id)->withoutSttConsent()->get();
    expect($withoutConsent)->toHaveCount(1);
    expect($withoutConsent->first()->user_id)->toBe($user2->id);

    $pendingConsent = RoomParticipant::where('room_id', $room->id)->pendingSttConsent()->get();
    expect($pendingConsent)->toHaveCount(1);
    expect($pendingConsent->first()->user_id)->toBe($user3->id);
});
