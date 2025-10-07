<?php

declare(strict_types=1);

use App\Jobs\FinalizeStaleRecordings;
use Domain\Room\Enums\RecordingStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->room = Room::factory()->create(['creator_id' => $this->user->id]);

    $this->storageAccount = UserStorageAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'wasabi',
        'is_active' => true,
        'encrypted_credentials' => [
            'access_key' => 'fake_access_key',
            'secret_key' => 'fake_secret_key',
            'bucket' => 'fake_bucket',
            'region' => 'us-east-1',
        ],
    ]);

    // Create recording settings for the room
    RoomRecordingSettings::factory()->create([
        'room_id' => $this->room->id,
        'storage_account_id' => $this->storageAccount->id,
    ]);
});

test('job can be instantiated and dispatched', function () {
    Queue::fake();

    FinalizeStaleRecordings::dispatch();

    Queue::assertPushed(FinalizeStaleRecordings::class);
});

test('job handles no stale recordings', function () {
    $job = new FinalizeStaleRecordings();
    $job->handle();

    // Should complete without errors
    expect(true)->toBeTrue();
});

test('job processes stale recordings', function () {
    // Create a stale recording (updated more than 5 minutes ago, still in Recording status)
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'provider' => 'wasabi',
        'provider_file_id' => 'fake_file_id',
        'multipart_upload_id' => 'fake_upload_id',
        'status' => RecordingStatus::Recording,
        'updated_at' => now()->subMinutes(10),
    ]);

    $job = new FinalizeStaleRecordings();
    $job->handle();

    // Recording should be processed (will fail due to fake credentials, but that's expected)
    $recording->refresh();
    expect($recording->status)->toBe(RecordingStatus::Failed);
});

test('job uses service container to resolve action dependencies', function () {
    // This test verifies that the job correctly resolves the FinalizeRecording action
    // through the service container, which should inject the required LogRecordingError dependency
    
    $recording = RoomRecording::factory()->create([
        'room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'provider' => 'wasabi',
        'provider_file_id' => 'fake_file_id',
        'multipart_upload_id' => 'fake_upload_id',
        'status' => RecordingStatus::Recording,
        'updated_at' => now()->subMinutes(10),
    ]);

    // The job should complete without throwing an ArgumentCountError
    $job = new FinalizeStaleRecordings();
    
    expect(fn() => $job->handle())->not->toThrow(ArgumentCountError::class);
});
