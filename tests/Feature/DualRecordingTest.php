<?php

declare(strict_types=1);

use Domain\Room\Actions\LogRecordingError;
use Domain\Room\Actions\UpdateLocalSaveConsent;
use Domain\Room\Actions\UpdateRecordingConsent;
use Domain\Room\Enums\RecordingErrorType;
use Domain\Room\Enums\RecordingStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingError;
use Domain\User\Models\User;

describe('Dual Recording System', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->room = Room::factory()->create(['creator_id' => $this->user->id]);
        
        // Set up room recording settings with remote storage
        $this->room->recordingSettings()->create([
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => null, // Don't require actual storage account for these tests
            'recording_consent_requirement' => 'required',
        ]);
    });

    test('user can give both recording and local save consent', function () {
    $recordingAction = new UpdateRecordingConsent();
    $localSaveAction = new UpdateLocalSaveConsent();
    
    // Create participant record
    $participant = RoomParticipant::create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'character_name' => 'Test Character',
        'joined_at' => now(),
    ]);
    
    // Give recording consent
    $recordingAction->execute($this->room, $this->user, true);
    $participant->refresh();
    expect($participant->hasRecordingConsent())->toBeTrue();
    
    // Give local save consent
    $localSaveAction->execute($this->room, $this->user, true);
    $participant->refresh();
    expect($participant->hasLocalSaveConsent())->toBeTrue();
    
    // Both consents should be active
    expect($participant->hasRecordingConsent())->toBeTrue();
    expect($participant->hasLocalSaveConsent())->toBeTrue();
    expect($participant->hasLocalSaveConsentDenied())->toBeFalse();
});

    test('user can deny local save consent while keeping recording consent', function () {
    $recordingAction = new UpdateRecordingConsent();
    $localSaveAction = new UpdateLocalSaveConsent();
    
    // Create participant record
    $participant = RoomParticipant::create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'character_name' => 'Test Character',
        'joined_at' => now(),
    ]);
    
    // Give recording consent
    $recordingAction->execute($this->room, $this->user, true);
    
    // Deny local save consent
    $localSaveAction->execute($this->room, $this->user, false);
    
    $participant->refresh();
    expect($participant->hasRecordingConsent())->toBeTrue();
    expect($participant->hasLocalSaveConsent())->toBeFalse();
    expect($participant->hasLocalSaveConsentDenied())->toBeTrue();
});

    test('local save consent api endpoints work correctly', function () {
    $participant = RoomParticipant::create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'character_name' => 'Test Character',
        'joined_at' => now(),
    ]);
    
    // Check initial status
    $response = $this->actingAs($this->user)
        ->getJson("/api/rooms/{$this->room->id}/local-save-consent");
    
    $response->assertOk()
        ->assertJson([
            'local_save_enabled' => true,
            'requires_consent' => true,
            'consent_given' => false,
            'consent_denied' => false,
        ]);
    
    // Give consent
    $response = $this->actingAs($this->user)
        ->postJson("/api/rooms/{$this->room->id}/local-save-consent", [
            'consent_given' => true
        ]);
    
    $response->assertOk()
        ->assertJson([
            'success' => true,
            'consent_given' => true,
        ]);
    
    // Check updated status
    $response = $this->actingAs($this->user)
        ->getJson("/api/rooms/{$this->room->id}/local-save-consent");
    
    $response->assertOk()
        ->assertJson([
            'local_save_enabled' => true,
            'requires_consent' => false,
            'consent_given' => true,
            'consent_denied' => false,
        ]);
});

    test('local save consent not applicable for local device storage', function () {
    // Update room to use local device storage
    $this->room->recordingSettings()->update([
        'storage_provider' => 'local_device',
    ]);
    
    $response = $this->actingAs($this->user)
        ->getJson("/api/rooms/{$this->room->id}/local-save-consent");
    
    $response->assertOk()
        ->assertJson([
            'local_save_enabled' => false,
            'requires_consent' => false,
            'consent_given' => null,
        ]);
    
    // Trying to give consent should fail
    $response = $this->actingAs($this->user)
        ->postJson("/api/rooms/{$this->room->id}/local-save-consent", [
            'consent_given' => true
        ]);
    
    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Local save consent not applicable for local storage',
        ]);
});

    test('recording errors are logged to database', function () {
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'status' => RecordingStatus::Recording,
    ]);
    
    $logAction = new LogRecordingError();
    
    $error = $logAction->execute(
        room: $this->room,
        errorType: RecordingErrorType::FinalizationFailed,
        errorMessage: 'Test error message',
        user: $this->user,
        recording: $recording,
        errorCode: '500',
        errorContext: ['test' => 'context'],
        provider: 'wasabi',
        multipartUploadId: 'test-upload-id',
        providerFileId: 'test-file-id'
    );
    
    expect($error)->toBeInstanceOf(RoomRecordingError::class);
    expect($error->room_id)->toBe($this->room->id);
    expect($error->user_id)->toBe($this->user->id);
    expect($error->recording_id)->toBe($recording->id);
    expect($error->error_type)->toBe(RecordingErrorType::FinalizationFailed);
    expect($error->error_message)->toBe('Test error message');
    expect($error->error_code)->toBe('500');
    expect($error->error_context)->toBe(['test' => 'context']);
    expect($error->provider)->toBe('wasabi');
    expect($error->multipart_upload_id)->toBe('test-upload-id');
    expect($error->provider_file_id)->toBe('test-file-id');
    expect($error->resolved)->toBeFalse();
    
    // Test error resolution
    $error->markAsResolved('Issue was resolved by restarting upload');
    expect($error->resolved)->toBeTrue();
    expect($error->resolution_notes)->toBe('Issue was resolved by restarting upload');
    expect($error->resolved_at)->not()->toBeNull();
});
});
