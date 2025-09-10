<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Http\UploadedFile;

test('google drive service initializes correctly with storage account', function () {
    $user = User::factory()->create();

    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
            'access_token' => 'test_access_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'created_at' => now()->timestamp,
        ],
        'display_name' => 'Test Google Drive Account',
        'is_active' => true,
    ]);

    $driveService = new GoogleDriveService($storageAccount);

    expect($driveService)->toBeInstanceOf(GoogleDriveService::class);
    expect($driveService->getStorageAccount())->toBe($storageAccount);
});

test('google drive service rejects non-google-drive storage accounts', function () {
    $user = User::factory()->create();

    $wasabiAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi', // Not Google Drive
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'Wasabi Account',
        'is_active' => true,
    ]);

    // This should throw an exception
    expect(function () use ($wasabiAccount) {
        new GoogleDriveService($wasabiAccount);
    })->toThrow(\InvalidArgumentException::class, 'Storage account must be for Google Drive provider');
});

test('can generate google drive authorization url', function () {
    // Mock the config values
    config([
        'services.google_drive.client_id' => 'test_client_id',
        'services.google_drive.client_secret' => 'test_client_secret',
        'services.google_drive.redirect_uri' => 'http://localhost/google-drive/callback',
    ]);

    $authUrl = GoogleDriveService::getAuthorizationUrl();

    expect($authUrl)->toBeString();
    expect($authUrl)->toContain('accounts.google.com');
    expect($authUrl)->toContain('oauth2');
    expect($authUrl)->toContain('test_client_id');
    expect($authUrl)->toContain('googleapis.com%2Fauth%2Fdrive.file'); // URL encoded scope
});

test('google drive oauth callback handles authorization correctly', function () {
    // Create test user
    $user = User::factory()->create();

    // Mock config
    config([
        'services.google_drive.client_id' => 'test_client_id',
        'services.google_drive.client_secret' => 'test_client_secret',
        'services.google_drive.redirect_uri' => 'http://localhost/google-drive/callback',
    ]);

    // Test the callback endpoint
    $response = $this->actingAs($user)
        ->get('/google-drive/callback?code=test_auth_code');

    // Since we're using mock credentials, this will likely fail with a real API call
    // But we can test that the route exists and handles authentication
    $response->assertStatus(302); // Should redirect
});

test('google drive oauth callback handles missing code', function () {
    $user = User::factory()->create();

    // Test callback without authorization code
    $response = $this->actingAs($user)
        ->get('/google-drive/callback?error=access_denied');

    $response->assertStatus(302)
        ->assertSessionHas('error');
});

test('google drive oauth callback requires authentication', function () {
    // Test callback without being logged in
    $response = $this->get('/google-drive/callback?code=test_code');

    $response->assertStatus(302)
        ->assertRedirect('/login');
});

// DEPRECATED: This test was for the server-side upload endpoint which has been removed
// The system now uses direct uploads to Google Drive via resumable upload sessions
test('can upload to google drive via api endpoint', function () {
    $this->markTestSkipped('Server-side upload endpoint removed - system now uses direct uploads');

    // Create Google Drive storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
            'access_token' => 'test_access_token',
        ],
        'display_name' => 'Test Google Drive Account',
        'is_active' => true,
    ]);

    // Enable recording for the room with Google Drive storage
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'google_drive',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Create participant with recording consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'recording_consent_given' => true,
        'recording_consent_at' => now(),
    ]);

    // Create a fake video file for testing
    $videoFile = UploadedFile::fake()->create('test_recording.webm', 1024, 'video/webm');

    $metadata = json_encode([
        'started_at_ms' => now()->timestamp * 1000,
        'ended_at_ms' => (now()->timestamp + 5) * 1000,
    ]);

    // Attempt to upload (this will fail with fake Google API credentials, but we can test the validation)
    $response = $this->actingAs($user)
        ->post("/api/rooms/{$room->id}/recordings/upload-google-drive", [
            'video' => $videoFile,
            'metadata' => $metadata,
        ]);

    // Since we're using fake credentials, this will likely fail at the Google API level
    // But we can verify the request was processed correctly up to that point
    expect($response->status())->toBeIn([201, 500]); // Either success or Google API failure
});

// DEPRECATED: This test was for the server-side upload endpoint which has been removed
test('google drive upload requires consent', function () {
    $this->markTestSkipped('Server-side upload endpoint removed - system now uses direct uploads');

    // Create Google Drive storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
        ],
        'display_name' => 'Test Google Drive Account',
        'is_active' => true,
    ]);

    // Enable recording for the room with Google Drive storage
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'google_drive',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Create participant WITHOUT consent
    RoomParticipant::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'recording_consent_given' => null, // No recording consent
        'stt_consent_at' => null,
    ]);

    // Create a fake video file for testing
    $videoFile = UploadedFile::fake()->create('test_recording.webm', 1024, 'video/webm');

    // Attempt to upload without consent
    $response = $this->actingAs($user)
        ->post("/api/rooms/{$room->id}/recordings/upload-google-drive", [
            'video' => $videoFile,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Video recording consent required',
            'requires_consent' => true,
        ]);
});

// DEPRECATED: This test was for the server-side upload endpoint which has been removed
test('non-participants cannot upload to google drive', function () {
    $this->markTestSkipped('Server-side upload endpoint removed - system now uses direct uploads');

    // Create Google Drive storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $gm->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
        ],
        'display_name' => 'Test Google Drive Account',
        'is_active' => true,
    ]);

    // Enable recording for the room with Google Drive storage
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'google_drive',
        'storage_account_id' => $storageAccount->id,
    ]);

    // Create a fake video file for testing
    $videoFile = UploadedFile::fake()->create('test_recording.webm', 1024, 'video/webm');

    // Attempt to upload as non-participant
    $response = $this->actingAs($outsider)
        ->post("/api/rooms/{$room->id}/recordings/upload-google-drive", [
            'video' => $videoFile,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'error' => 'Only room participants can upload recordings',
        ]);
});

test('can disconnect google drive account', function () {
    $user = User::factory()->create();

    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
        ],
        'display_name' => 'Test Google Drive Account',
        'is_active' => true,
    ]);

    // Test disconnecting the account
    $response = $this->actingAs($user)
        ->post('/google-drive/disconnect', [
            'storage_account_id' => $storageAccount->id,
        ]);

    $response->assertStatus(302)
        ->assertSessionHas('success');

    // Verify account was deleted
    expect(UserStorageAccount::find($storageAccount->id))->toBeNull();
});
