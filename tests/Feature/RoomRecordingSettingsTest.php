<?php

declare(strict_types=1);

use Domain\Room\Actions\UpdateRoomRecordingSettings;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

test('room creator can update recording settings', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    $action = new UpdateRoomRecordingSettings();
    
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: true,
        sttEnabled: true,
        storageProvider: 'local_device',
        storageAccountId: null
    );

    expect($settings)->toBeInstanceOf(RoomRecordingSettings::class);
    expect($settings->recording_enabled)->toBeTrue();
    expect($settings->stt_enabled)->toBeTrue();
    expect($settings->storage_provider)->toBe('local_device');
    expect($settings->storage_account_id)->toBeNull();
});

test('non-creator cannot update recording settings', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    $action = new UpdateRoomRecordingSettings();
    
    expect(function () use ($action, $room, $otherUser) {
        $action->execute(
            $room,
            $otherUser,
            recordingEnabled: true,
            sttEnabled: false,
            storageProvider: 'local_device',
            storageAccountId: null
        );
    })->toThrow(\Exception::class, 'Only the room creator can modify recording settings');
});

test('can configure wasabi storage for recording', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create Wasabi storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.us-east-1.wasabisys.com',
        ],
        'display_name' => 'Test Wasabi Account',
        'is_active' => true,
    ]);

    $action = new UpdateRoomRecordingSettings();
    
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: true,
        sttEnabled: false,
        storageProvider: 'wasabi',
        storageAccountId: $storageAccount->id
    );

    expect($settings->recording_enabled)->toBeTrue();
    expect($settings->stt_enabled)->toBeFalse();
    expect($settings->storage_provider)->toBe('wasabi');
    expect($settings->storage_account_id)->toBe($storageAccount->id);
});

test('can configure google drive storage for recording', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create Google Drive storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
            'access_token' => 'test_access_token',
            'expires_in' => 3600,
        ],
        'display_name' => 'Test Google Drive Account',
        'is_active' => true,
    ]);

    $action = new UpdateRoomRecordingSettings();
    
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: true,
        sttEnabled: true,
        storageProvider: 'google_drive',
        storageAccountId: $storageAccount->id
    );

    expect($settings->recording_enabled)->toBeTrue();
    expect($settings->stt_enabled)->toBeTrue();
    expect($settings->storage_provider)->toBe('google_drive');
    expect($settings->storage_account_id)->toBe($storageAccount->id);
});

test('validates storage account belongs to user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user1->id]);
    
    // Create storage account for user2
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user2->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'Other User Account',
        'is_active' => true,
    ]);

    $action = new UpdateRoomRecordingSettings();
    
    expect(function () use ($action, $room, $user1, $storageAccount) {
        $action->execute(
            $room,
            $user1,
            recordingEnabled: true,
            sttEnabled: false,
            storageProvider: 'wasabi',
            storageAccountId: $storageAccount->id
        );
    })->toThrow(\Exception::class, 'Storage account not found or not accessible');
});

test('disabling recording clears storage settings', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'Test Account',
        'is_active' => true,
    ]);

    $action = new UpdateRoomRecordingSettings();
    
    // First enable recording with storage
    $action->execute(
        $room,
        $user,
        recordingEnabled: true,
        sttEnabled: true,
        storageProvider: 'wasabi',
        storageAccountId: $storageAccount->id
    );
    
    // Then disable recording
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: false,
        sttEnabled: false,
        storageProvider: null,
        storageAccountId: null
    );

    expect($settings->recording_enabled)->toBeFalse();
    expect($settings->stt_enabled)->toBeFalse();
    expect($settings->storage_provider)->toBeNull();
    expect($settings->storage_account_id)->toBeNull();
});

test('defaults to local storage when recording enabled without provider', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    $action = new UpdateRoomRecordingSettings();
    
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: true,
        sttEnabled: false,
        storageProvider: null,
        storageAccountId: null
    );

    expect($settings->recording_enabled)->toBeTrue();
    expect($settings->storage_provider)->toBe('local_device');
    expect($settings->storage_account_id)->toBeNull();
});

test('can update existing room recording settings', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create initial settings
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => false,
        'stt_enabled' => false,
        'storage_provider' => null,
        'storage_account_id' => null,
    ]);

    $action = new UpdateRoomRecordingSettings();
    
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: true,
        sttEnabled: true,
        storageProvider: 'local_device',
        storageAccountId: null
    );

    expect($settings->recording_enabled)->toBeTrue();
    expect($settings->stt_enabled)->toBeTrue();
    expect($settings->storage_provider)->toBe('local_device');
    
    // Verify only one settings record exists
    expect(RoomRecordingSettings::where('room_id', $room->id)->count())->toBe(1);
});

test('validates storage provider matches account provider', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'Wasabi Account',
        'is_active' => true,
    ]);

    $action = new UpdateRoomRecordingSettings();
    
    expect(function () use ($action, $room, $user, $storageAccount) {
        $action->execute(
            $room,
            $user,
            recordingEnabled: true,
            sttEnabled: false,
            storageProvider: 'google_drive', // Wrong provider
            storageAccountId: $storageAccount->id
        );
    })->toThrow(\Exception::class, 'Storage provider mismatch with selected account');
});

test('can enable speech-to-text without video recording', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    $action = new UpdateRoomRecordingSettings();
    
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: false,
        sttEnabled: true,
        storageProvider: null,
        storageAccountId: null
    );

    expect($settings->recording_enabled)->toBeFalse();
    expect($settings->stt_enabled)->toBeTrue();
    expect($settings->storage_provider)->toBeNull();
    expect($settings->storage_account_id)->toBeNull();
});

test('can enable both recording and speech-to-text independently', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    $action = new UpdateRoomRecordingSettings();
    
    // First enable only STT
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: false,
        sttEnabled: true,
        storageProvider: null,
        storageAccountId: null
    );

    expect($settings->recording_enabled)->toBeFalse();
    expect($settings->stt_enabled)->toBeTrue();
    
    // Then enable recording while keeping STT
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: true,
        sttEnabled: true,
        storageProvider: 'local_device',
        storageAccountId: null
    );

    expect($settings->recording_enabled)->toBeTrue();
    expect($settings->stt_enabled)->toBeTrue();
    expect($settings->storage_provider)->toBe('local_device');
    
    // Then disable recording while keeping STT
    $settings = $action->execute(
        $room,
        $user,
        recordingEnabled: false,
        sttEnabled: true,
        storageProvider: null,
        storageAccountId: null
    );

    expect($settings->recording_enabled)->toBeFalse();
    expect($settings->stt_enabled)->toBeTrue();
    expect($settings->storage_provider)->toBeNull();
});

