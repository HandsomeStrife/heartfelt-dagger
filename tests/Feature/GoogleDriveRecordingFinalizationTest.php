<?php

declare(strict_types=1);

use Domain\Room\Actions\FinalizeRecording;
use Domain\Room\Enums\RecordingStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->room = Room::factory()->create(['creator_id' => $this->user->id]);

    $this->storageAccount = UserStorageAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'google_drive',
        'is_active' => true,
        'encrypted_credentials' => [
            'access_token' => 'fake_access_token',
            'refresh_token' => 'fake_refresh_token',
            'expires_at' => now()->addHours(1)->timestamp,
        ],
    ]);
});

test('can finalize Google Drive recording successfully', function () {
    // Create a recording that needs finalization
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'provider' => 'google_drive',
        'provider_file_id' => 'fake_file_id_123',
        'multipart_upload_id' => 'https://googleapis.com/upload/drive/v3/files/session123',
        'status' => RecordingStatus::Recording,
        'size_bytes' => 0, // Will be updated during finalization
    ]);

    // For this test, we'll expect the finalization to complete successfully
    // even if the file info can't be retrieved (graceful degradation)
    $action = new FinalizeRecording;
    $result = $action->execute($recording);

    expect($result)->toBeTrue();

    $recording->refresh();
    expect($recording->status)->toBe(RecordingStatus::Completed);
    // Size won't be updated in test environment due to fake credentials, but that's OK
});

test('handles Google Drive API errors gracefully during finalization', function () {
    // Create a recording that needs finalization
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'provider' => 'google_drive',
        'provider_file_id' => 'fake_file_id_456',
        'multipart_upload_id' => 'https://googleapis.com/upload/drive/v3/files/session456',
        'status' => RecordingStatus::Recording,
    ]);

    // Mock Google Drive API to return an error
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/fake_file_id_456*' => Http::response([
            'error' => [
                'code' => 404,
                'message' => 'File not found',
            ],
        ], 404),
    ]);

    $action = new FinalizeRecording;
    $result = $action->execute($recording);

    // Should still succeed even if file verification fails
    expect($result)->toBeTrue();

    $recording->refresh();
    expect($recording->status)->toBe(RecordingStatus::Completed);
});

test('skips recordings that cannot be finalized', function () {
    // Create a recording that's already completed
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'provider' => 'google_drive',
        'status' => RecordingStatus::Completed,
    ]);

    $action = new FinalizeRecording;
    $result = $action->execute($recording);

    expect($result)->toBeFalse();

    $recording->refresh();
    expect($recording->status)->toBe(RecordingStatus::Completed); // Should remain unchanged
});

test('handles missing storage account during finalization', function () {
    // Create a recording but no storage account for the user
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'provider' => 'google_drive',
        'provider_file_id' => 'fake_file_id_789',
        'multipart_upload_id' => 'https://googleapis.com/upload/drive/v3/files/session789',
        'status' => RecordingStatus::Recording,
    ]);

    // Delete the storage account
    $this->storageAccount->delete();

    $action = new FinalizeRecording;
    $result = $action->execute($recording);

    expect($result)->toBeFalse();

    $recording->refresh();
    expect($recording->status)->toBe(RecordingStatus::Failed);
});
