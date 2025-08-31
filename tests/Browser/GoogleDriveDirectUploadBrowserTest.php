<?php

use Illuminate\Support\Facades\Http;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Laravel\Dusk\Browser;

describe('Google Drive Direct Upload Browser Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->room = Room::factory()->create([
            'creator_id' => $this->user->id,
            'name' => 'Test Room for Google Drive Direct Upload'
        ]);
        
        // Create Google Drive storage account
        $this->storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $this->user->id,
            'provider' => 'google_drive',
            'is_active' => true,
            'encrypted_credentials' => [
                'refresh_token' => 'test_refresh_token',
                'access_token' => 'test_access_token',
                'expires_in' => 3600,
                'created_at' => now()->timestamp - 1800,
            ],
        ]);
        
        // Create recording settings for Google Drive
        RoomRecordingSettings::factory()->create([
            'room_id' => $this->room->id,
            'recording_enabled' => true,
            'stt_enabled' => true,
            'storage_provider' => 'google_drive',
            'storage_account_id' => $this->storageAccount->id,
        ]);
        
        // Create participant with consent
        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
            'left_at' => null,
        ]);
    });

    test('can initiate and complete Google Drive direct upload flow', function () {
        // Mock HTTP responses for the browser test
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::sequence()
                ->push('', 200, ['Location' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable&upload_id=test123'])
                ->push([
                    'id' => 'drive_file_123',
                    'name' => 'test-recording.webm',
                    'size' => '1048576',
                    'webViewLink' => 'https://drive.google.com/file/d/drive_file_123/view',
                    'mimeType' => 'video/webm',
                ]),
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit("/rooms/{$this->room->id}/session")
                ->wait(2) // Wait for content to load
            ->assertSee('Test Room for Google Drive Direct Upload')
                ->assertSee('Test Room for Google Drive Direct Upload');

            // Wait for JavaScript to initialize
            $browser->pause(2000);

            // Inject test recording blob and trigger upload
            $browser->script([
                // Create fake media streams and recording data
                'window.testRecordingBlob = new Blob(["fake video data"], { type: "video/webm" });',
                'window.testRecordingBlob.name = "test-recording.webm";',
                
                // Add metadata
                'window.testRecordingBlob.meta = {',
                '    started_at_ms: Date.now() - 30000,', // 30 seconds ago
                '    ended_at_ms: Date.now()',
                '};',
                
                // Simulate room uppy initialization if not already done
                'if (!window.roomUppy) {',
                '    const roomData = { id: ' . $this->room->id . ' };',
                '    const recordingSettings = { storage_provider: "google_drive" };',
                '    window.roomUppy = new RoomUppy(roomData, recordingSettings);',
                '}',
                
                // Add the blob to uppy for upload
                'window.roomUppy.uploadVideoBlob(window.testRecordingBlob);'
            ]);

            // Wait for upload initiation
            $browser->pause(3000);

            // Check console for expected log messages
            $logs = $browser->driver->manage()->getLog('browser');
            $hasUploadUrlLog = false;
            $hasConfirmationLog = false;
            
            foreach ($logs as $log) {
                if (strpos($log['message'], 'Got Google Drive direct upload URL') !== false) {
                    $hasUploadUrlLog = true;
                }
                if (strpos($log['message'], 'Google Drive upload confirmed') !== false) {
                    $hasConfirmationLog = true;
                }
            }
            
            expect($hasUploadUrlLog)->toBe(true, 'Should log Google Drive upload URL generation');
            
            // Wait for upload confirmation (this depends on mock HTTP responses)
            $browser->pause(2000);
            
            // Check that the recording was created in the database
            $this->assertDatabaseHas('room_recordings', [
                'room_id' => $this->room->id,
                'user_id' => $this->user->id,
                'provider' => 'google_drive',
                'provider_file_id' => 'drive_file_123',
                'status' => 'uploaded',
            ]);
        });
    })->timeout(30);

    test('handles Google Drive upload URL generation failure gracefully', function () {
        // Mock failed upload URL generation
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'error' => ['message' => 'Insufficient permissions']
            ], 403),
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit("/rooms/{$this->room->id}/session")
                ->wait(2) // Wait for content to load
            ->assertSee('Test Room for Google Drive Direct Upload')
                ->pause(2000);

            // Inject test recording blob and trigger upload
            $browser->script([
                'window.testRecordingBlob = new Blob(["fake video data"], { type: "video/webm" });',
                'window.testRecordingBlob.name = "test-recording.webm";',
                'window.testRecordingBlob.meta = {',
                '    started_at_ms: Date.now() - 30000,',
                '    ended_at_ms: Date.now()',
                '};',
                
                'if (!window.roomUppy) {',
                '    const roomData = { id: ' . $this->room->id . ' };',
                '    const recordingSettings = { storage_provider: "google_drive" };',
                '    window.roomUppy = new RoomUppy(roomData, recordingSettings);',
                '}',
                
                'window.roomUppy.uploadVideoBlob(window.testRecordingBlob);'
            ]);

            // Wait for upload attempt
            $browser->pause(3000);

            // Check console for error messages
            $logs = $browser->driver->manage()->getLog('browser');
            $hasErrorLog = false;
            
            foreach ($logs as $log) {
                if (strpos($log['message'], 'Failed to get Google Drive upload URL') !== false) {
                    $hasErrorLog = true;
                    break;
                }
            }
            
            expect($hasErrorLog)->toBe(true, 'Should log Google Drive upload URL generation failure');
            
            // Verify no recording was created in the database
            $this->assertDatabaseMissing('room_recordings', [
                'room_id' => $this->room->id,
                'user_id' => $this->user->id,
                'provider' => 'google_drive',
            ]);
        });
    })->timeout(20);

    test('handles consent requirements properly', function () {
        // Remove user consent
        RoomParticipant::where('room_id', $this->room->id)
            ->where('user_id', $this->user->id)
            ->update([
                'stt_consent_given' => false,
                'stt_consent_at' => null,
            ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit("/rooms/{$this->room->id}/session")
                ->wait(2) // Wait for content to load
            ->assertSee('Test Room for Google Drive Direct Upload')
                ->pause(2000);

            // Try to trigger upload without consent
            $browser->script([
                'window.testRecordingBlob = new Blob(["fake video data"], { type: "video/webm" });',
                'window.testRecordingBlob.name = "test-recording.webm";',
                'window.testRecordingBlob.meta = {',
                '    started_at_ms: Date.now() - 30000,',
                '    ended_at_ms: Date.now()',
                '};',
                
                'if (!window.roomUppy) {',
                '    const roomData = { id: ' . $this->room->id . ' };',
                '    const recordingSettings = { storage_provider: "google_drive" };',
                '    window.roomUppy = new RoomUppy(roomData, recordingSettings);',
                '}',
                
                'window.roomUppy.uploadVideoBlob(window.testRecordingBlob);'
            ]);

            // Wait for upload attempt
            $browser->pause(3000);

            // Verify no recording was created due to consent requirement
            $this->assertDatabaseMissing('room_recordings', [
                'room_id' => $this->room->id,
                'user_id' => $this->user->id,
                'provider' => 'google_drive',
            ]);
        });
    })->timeout(20);

    test('displays appropriate UI feedback during upload process', function () {
        // Mock successful responses with delays to test UI states
        Http::fake([
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::sequence()
                ->push('', 200, ['Location' => 'https://www.googleapis.com/upload/drive/v3/files/resumable?uploadType=resumable&upload_id=test123'])
                ->pushWithDelay([
                    'id' => 'drive_file_123',
                    'name' => 'test-recording.webm',
                    'size' => '1048576',
                    'webViewLink' => 'https://drive.google.com/file/d/drive_file_123/view',
                    'mimeType' => 'video/webm',
                ], 2000), // 2 second delay
            'https://www.googleapis.com/oauth2/v4/token' => Http::response([
                'access_token' => 'new_access_token',
                'expires_in' => 3600,
            ]),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit("/rooms/{$this->room->id}/session")
                ->wait(2) // Wait for content to load
            ->assertSee('Test Room for Google Drive Direct Upload')
                ->pause(2000);

            // Look for upload progress or status indicators
            // Note: These selectors would need to match your actual UI implementation
            $browser->script([
                'window.testRecordingBlob = new Blob(["fake video data"], { type: "video/webm" });',
                'window.testRecordingBlob.name = "test-recording.webm";',
                'window.testRecordingBlob.meta = {',
                '    started_at_ms: Date.now() - 30000,',
                '    ended_at_ms: Date.now()',
                '};',
                
                'if (!window.roomUppy) {',
                '    const roomData = { id: ' . $this->room->id . ' };',
                '    const recordingSettings = { storage_provider: "google_drive" };',
                '    window.roomUppy = new RoomUppy(roomData, recordingSettings);',
                '}',
                
                // Add event listeners to track upload progress
                'window.uploadEvents = [];',
                'window.addEventListener("recording-upload-progress", (e) => {',
                '    window.uploadEvents.push("progress: " + e.detail.progress);',
                '});',
                'window.addEventListener("recording-upload-success", (e) => {',
                '    window.uploadEvents.push("success: " + e.detail.provider);',
                '});',
                
                'window.roomUppy.uploadVideoBlob(window.testRecordingBlob);'
            ]);

            // Wait for upload completion
            $browser->pause(5000);

            // Check that appropriate events were fired
            $events = $browser->script('return window.uploadEvents || [];')[0];
            expect($events)->toContain('success: google_drive');
        });
    })->timeout(30);
});
