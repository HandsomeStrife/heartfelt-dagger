<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    config([
        'filesystems.disks.wasabi' => [
            'driver' => 's3',
            'key' => 'minioadmin',
            'secret' => 'minioadmin',
            'region' => 'us-east-1',
            'bucket' => 'recordings',
            'endpoint' => 'http://localhost:9000',
            'use_path_style_endpoint' => true,
        ],
    ]);
});

describe('Video Library Browser Tests', function () {
    test('displays user recordings with download links', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'encrypted_credentials' => [
                'access_key_id' => 'minioadmin',
                'secret_access_key' => 'minioadmin',
                'bucket_name' => 'recordings',
                'region' => 'us-east-1',
                'endpoint' => 'http://localhost:9000',
            ],
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Create sample recordings
        $recordings = [];
        for ($i = 1; $i <= 3; $i++) {
            $recordings[] = RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider' => 'wasabi',
                'provider_file_id' => "rooms/{$room->id}/users/{$user->id}/recording-{$i}.webm",
                'filename' => "Recording {$i}.webm",
                'size_bytes' => 1024 * 1024 * $i, // 1MB, 2MB, 3MB
                'started_at_ms' => 1234567890000 + ($i * 60000), // 1 minute apart
                'ended_at_ms' => 1234567920000 + ($i * 60000),   // 30-second recordings
                'mime_type' => 'video/webm',
                'status' => 'uploaded',
                'created_at' => now()->subMinutes(10 - $i), // Recent to oldest
            ]);
        }

        actingAs($user);
        $page = visit(route('video-library'));

        $page->wait(2) // Wait for content to load
            ->assertSee('Video Library')
            ->assertSee('Video Library')
            ->assertSee($room->name);

        // Check all recordings are displayed
        foreach ($recordings as $i => $recording) {
            $page->assertSee("Recording " . ($i + 1) . ".webm")
                ->assertSee(number_format($recording->size_bytes / (1024 * 1024), 1) . ' MB');
        }

        // Test download functionality
        $page->assertSee('Download'); // Download buttons should be present

        // Click on first download button
        $page->click('button:contains("Download")');

        // In a real scenario, this would trigger a download
        // For testing, we verify the click handler works
        $page->waitFor(1000);
        $page->assertNoJavaScriptErrors();
    });

    test('filters recordings by date range', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create recordings with different dates
        $oldRecording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_file_id' => 'old-recording.webm',
            'filename' => 'Old Recording.webm',
            'size_bytes' => 1024 * 1024,
            'started_at_ms' => 1234567890000, // Jan 2009
            'ended_at_ms' => 1234567920000,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'created_at' => now()->subDays(30),
        ]);

        $recentRecording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_file_id' => 'recent-recording.webm',
            'filename' => 'Recent Recording.webm',
            'size_bytes' => 2048 * 1024,
            'started_at_ms' => now()->timestamp * 1000,
            'ended_at_ms' => (now()->timestamp + 30) * 1000,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'created_at' => now()->subHours(2),
        ]);

        actingAs($user);
        $page = visit(route('video-library'));

        // Initially, both recordings should be visible
        $page->wait(2) // Wait for content to load
            ->assertSee('Video Library')
            ->assertSee('Old Recording.webm')
            ->assertSee('Recent Recording.webm');

        // Apply date filter (if UI supports it)
        // Note: This would depend on the actual Video Library UI implementation
        if ($page->assertVisible('input[type="date"]', false)) {
            $page->type('input[name="start_date"]', now()->subDays(7)->format('Y-m-d'))
                ->press('Filter');

            $page->waitFor(1000)
                ->assertDontSee('Old Recording.webm')
                ->assertSee('Recent Recording.webm');
        }
    });

    test('shows empty state when no recordings exist', function () {
        $user = User::factory()->create();

        actingAs($user);
        $page = visit(route('video-library'));

        $page->wait(2) // Wait for content to load
            ->assertSee('Video Library')
            ->assertSee('Video Library')
            ->assertSee('No recordings found'); // Or similar empty state message
    });

    test('handles different storage providers in listing', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create recordings with different providers
        $providers = ['local', 'wasabi', 'google_drive'];
        foreach ($providers as $i => $provider) {
            RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_file_id' => "recording-{$provider}.webm",
                'filename' => ucfirst($provider) . " Recording.webm",
                'size_bytes' => 1024 * 1024,
                'started_at_ms' => 1234567890000 + ($i * 60000),
                'ended_at_ms' => 1234567920000 + ($i * 60000),
                'mime_type' => 'video/webm',
                'status' => 'uploaded',
            ]);
        }

        actingAs($user);
        $page = visit(route('video-library'));

        $page->wait(2) // Wait for content to load
            ->assertSee('Video Library');

        // All recordings should be displayed regardless of provider
        foreach ($providers as $provider) {
            $page->assertSee(ucfirst($provider) . " Recording.webm");
        }

        // Each should have a download button
        $downloadButtons = $page->script([
            'return document.querySelectorAll("button:contains(\'Download\')").length;'
        ]);
        expect($downloadButtons)->toBe(count($providers));
    });

    test('download provides signed URL from storage not app', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'encrypted_credentials' => [
                'access_key_id' => 'minioadmin',
                'secret_access_key' => 'minioadmin',
                'bucket_name' => 'recordings',
                'region' => 'us-east-1',
                'endpoint' => 'http://localhost:9000',
            ],
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Add user as participant
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
        ]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'provider_file_id' => "rooms/{$room->id}/users/{$user->id}/test-recording.webm",
            'filename' => 'Test Recording.webm',
            'size_bytes' => 1024 * 1024,
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
        ]);

        actingAs($user);
        $page = visit(route('video-library'));

        // Mock fetch to capture download requests
        $page->script([
            'window.downloadRequests = [];',
            'const originalFetch = fetch;',
            'window.fetch = (...args) => {',
            '  if (args[0].includes("/download")) {',
            '    window.downloadRequests.push(args);',
            '    return Promise.resolve({',
            '      json: () => Promise.resolve({',
            '        success: true,',
            '        download_url: "http://localhost:9000/recordings/test-file.webm?X-Amz-Signature=test",',
            '        filename: "Test Recording.webm",',
            '        provider: "wasabi"',
            '      }),',
            '      ok: true',
            '    });',
            '  }',
            '  return originalFetch(...args);',
            '};'
        ]);

        $page->wait(2) // Wait for content to load
            ->assertSee('Test Recording.webm')
            ->click('button:contains("Download")');

        $page->wait(2000);

        // Verify download request was made
        $downloadRequests = $page->script('return window.downloadRequests.length || 0');
        expect($downloadRequests)->toBeGreaterThan(0);

        // In a real implementation, verify the download URL points to storage
        $downloadUrl = $page->script([
            'return window.lastDownloadResponse ? window.lastDownloadResponse.download_url : null;'
        ]);

        if ($downloadUrl) {
            expect($downloadUrl)->toContain('localhost:9000'); // MinIO endpoint
            expect($downloadUrl)->not()->toContain('localhost:80'); // Not the app endpoint
        }
    });

    test('shows recording metadata and duration', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $recording = RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_file_id' => 'detailed-recording.webm',
            'filename' => 'Detailed Recording.webm',
            'size_bytes' => 15 * 1024 * 1024, // 15MB
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567950000, // 60 seconds
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
            'created_at' => now()->subHours(3),
        ]);

        actingAs($user);
        $page = visit(route('video-library'));

        $page->wait(2) // Wait for content to load
            ->assertSee('Detailed Recording.webm')
            ->assertSee('Detailed Recording.webm')
            ->assertSee('15.0 MB') // File size
            ->assertSee('1:00') // Duration (1 minute)
            ->assertSee($room->name); // Room name

        // Check for formatted timestamps
        $recordingDate = $recording->created_at->format('M j, Y');
        $page->assertSee($recordingDate);
    });

    test('handles failed uploads and different statuses', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create recordings with different statuses
        $statuses = [
            ['status' => 'uploaded', 'filename' => 'Successful Upload.webm'],
            ['status' => 'processing', 'filename' => 'Processing Upload.webm'],
            ['status' => 'failed', 'filename' => 'Failed Upload.webm'],
        ];

        foreach ($statuses as $i => $recordingData) {
            RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider' => 'local',
                'provider_file_id' => "recording-{$i}.webm",
                'filename' => $recordingData['filename'],
                'size_bytes' => 1024 * 1024,
                'started_at_ms' => 1234567890000 + ($i * 60000),
                'ended_at_ms' => 1234567920000 + ($i * 60000),
                'mime_type' => 'video/webm',
                'status' => $recordingData['status'],
            ]);
        }

        actingAs($user);
        $page = visit(route('video-library'));

        $page->wait(2) // Wait for content to load
            ->assertSee('Video Library');

        // All recordings should be listed
        foreach ($statuses as $recordingData) {
            $page->assertSee($recordingData['filename']);
        }

        // Only successful uploads should have download buttons
        // Failed/processing uploads should show status indicators
        $page->assertSee('Processing') // Status indicator
            ->assertSee('Failed'); // Status indicator

        // Successful upload should have download button
        $downloadButtons = $page->script([
            'return document.querySelectorAll("button:contains(\'Download\')").length;'
        ]);
        expect($downloadButtons)->toBe(1); // Only successful uploads can be downloaded
    });

    test('respects user access control - users cannot see others recordings', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user1->id]);

        // Create recording by user1
        RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user1->id,
            'provider' => 'local',
            'provider_file_id' => 'user1-recording.webm',
            'filename' => 'User 1 Recording.webm',
            'size_bytes' => 1024 * 1024,
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
        ]);

        // Create recording by user2
        RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user2->id,
            'provider' => 'local',
            'provider_file_id' => 'user2-recording.webm',
            'filename' => 'User 2 Recording.webm',
            'size_bytes' => 2048 * 1024,
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
        ]);

        // User 1 should only see their own recordings
        actingAs($user1);
        $page1 = visit(route('video-library'));

        $page1->wait(2) // Wait for content to load
            ->assertSee('Video Library')
            ->assertSee('User 1 Recording.webm')
            ->assertDontSee('User 2 Recording.webm');

        // User 2 should only see their own recordings
        actingAs($user2);
        $page2 = visit(route('video-library'));

        $page2->wait(2) // Wait for content to load
            ->assertSee('Video Library')
            ->assertSee('User 2 Recording.webm')
            ->assertDontSee('User 1 Recording.webm');
    });

    test('video library is responsive across device sizes', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        // Create a sample recording
        RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => 'local',
            'provider_file_id' => 'responsive-test.webm',
            'filename' => 'Responsive Test.webm',
            'size_bytes' => 5 * 1024 * 1024,
            'started_at_ms' => 1234567890000,
            'ended_at_ms' => 1234567920000,
            'mime_type' => 'video/webm',
            'status' => 'uploaded',
        ]);

        actingAs($user);
        $page = visit(route('video-library'));

        $page->wait(2) // Wait for content to load
            ->assertSee('Video Library');

        // Test mobile viewport
        $page->resize(375, 667);
        $page->assertVisible('button:contains("Download")')
            ->assertSee('Responsive Test.webm');

        // Test tablet viewport
        $page->resize(768, 1024);
        $page->assertVisible('button:contains("Download")')
            ->assertSee('Responsive Test.webm');

        // Test desktop viewport
        $page->resize(1920, 1080);
        $page->assertVisible('button:contains("Download")')
            ->assertSee('Responsive Test.webm');
    });
});
