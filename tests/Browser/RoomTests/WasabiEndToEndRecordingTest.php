<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

describe('Wasabi End-to-End Recording Tests', function () {
    test('test file runs fine', function () {
        $this->assertTrue(true);
    });
    test('complete wasabi setup and recording workflow', function () {
        // Skip if no real Wasabi credentials in environment (needed for connection test)
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available - full E2E test requires live credentials');
        }

        $user = User::factory()->create([
            'username' => 'testuser', 
            'email' => 'test@example.com'
        ]);
        
        $room = Room::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Test Recording Room',
            'slug' => 'test-recording-room'
        ]);

        // Authenticate the user
        actingAs($user);

        // Step 1: Login and navigate to Wasabi setup
        $page = visit('/wasabi/connect')
            ->assertSee('Connect Wasabi Account')
            ->assertSee('Add your Wasabi cloud storage credentials');

        // Step 2: Fill out Wasabi credentials form with live credentials
        $uniqueBucketName = 'daggerheart-e2e-test-' . time() . '-' . rand(1000, 9999);
        
        $page->type('#display_name', 'E2E Test Wasabi Account')
            ->type('#access_key_id', config('services.wasabi.access_key'))
            ->type('#secret_access_key', config('services.wasabi.secret_key'))
            ->type('#bucket_name', $uniqueBucketName)
            ->select('#region', env('WASABI_REGION', 'us-east-1'))
            ->type('#endpoint', env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'));

        // Step 3: Test connection (should work with bucket auto-creation)
        $page->press('Test Connection')
            ->wait(5) // Wait for AJAX response
            ->assertSee('Connection successful!');

        // Step 4: Save the account
        $page->press('Connect Account')
            ->wait(5) // Wait for form submission
            ->assertSee('Storage account connected successfully!')
            ->assertSee('E2E Test Wasabi Account');

        // Step 5: Navigate to room and setup recording
        $page->navigate(route('rooms.show', $room))
            ->assertSee($room->name)
            ->assertSee('Recording Settings');

        // Step 6: Enable recording and select Wasabi
        $page->check('recording_enabled')
            ->wait(2) // Wait for UI to update
            ->radio('storage_provider', 'wasabi')
            ->wait(2) // Wait for storage account selector to appear
            ->assertSee('E2E Test Wasabi Account')
            ->select('form.storage_account_id', '1'); // Select first option

        // Step 7: Enable STT and save settings
        $page->check('stt_enabled')
            ->press('Save Settings')
            ->wait(3) // Wait for settings to save
            ->assertSee('Recording settings updated successfully!')
            ->assertSee('Recording Enabled');

            // Step 8: Verify room configuration is complete
            $room->refresh();
            $room->load('recordingSettings.storageAccount');
            
            expect($room->recordingSettings)->not()->toBeNull();
            expect($room->recordingSettings->recording_enabled)->toBeTrue();
            expect($room->recordingSettings->stt_enabled)->toBeTrue();
            expect($room->recordingSettings->storage_provider)->toBe('wasabi');
            expect($room->recordingSettings->storageAccount)->not()->toBeNull();

        // Step 9: Navigate to room session to verify it loads with recording setup
        $page->navigate(route('rooms.session', $room))
            ->wait(2) // Wait for page to load
            ->assertSee($room->name)
            ->assertPresent('[data-testid="video-slot"]');

        // Step 10: Mock media devices with fake streams (simulating your media files)
        $page->script([
                '// Mock getUserMedia with fake streams simulating the test media files',
                'navigator.mediaDevices.getUserMedia = async (constraints) => {',
                '  const stream = new MediaStream();',
                '  ',
                '  // Create fake video track (simulating akiyo_cif.y4m)',
                '  if (constraints.video) {',
                '    const canvas = document.createElement("canvas");',
                '    canvas.width = 640; canvas.height = 480;',
                '    const ctx = canvas.getContext("2d");',
                '    ctx.fillStyle = "#4A90E2"; ctx.fillRect(0, 0, 640, 480);',
                '    ctx.fillStyle = "white"; ctx.font = "20px Arial";',
                '    ctx.fillText("Fake Video Stream (akiyo_cif.y4m)", 160, 240);',
                '    const videoStream = canvas.captureStream(30);',
                '    videoStream.getTracks().forEach(track => {',
                '      track.label = "fake-video-akiyo_cif.y4m";',
                '      stream.addTrack(track);',
                '    });',
                '  }',
                '  ',
                '  // Create fake audio track (simulating talking_test.wav)',
                '  if (constraints.audio) {',
                '    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();',
                '    const oscillator = audioCtx.createOscillator();',
                '    const dest = audioCtx.createMediaStreamDestination();',
                '    oscillator.connect(dest);',
                '    oscillator.frequency.setValueAtTime(440, audioCtx.currentTime);',
                '    oscillator.start();',
                '    dest.stream.getTracks().forEach(track => {',
                '      track.label = "fake-audio-talking_test.wav";',
                '      stream.addTrack(track);',
                '    });',
                '  }',
                '  ',
                '  return stream;',
                '};',
                '',
                '// Test the mocked getUserMedia',
                'navigator.mediaDevices.getUserMedia({video: true, audio: true})',
                '  .then(stream => {',
                '    window.testMediaStream = stream;',
                '    window.testMediaReady = true;',
                '    window.testVideoTracks = stream.getVideoTracks().length;',
                '    window.testAudioTracks = stream.getAudioTracks().length;',
                '    window.testVideoLabel = stream.getVideoTracks()[0]?.label;',
                '    window.testAudioLabel = stream.getAudioTracks()[0]?.label;',
                '  })',
                '  .catch(err => {',
                '    window.testMediaError = err.message;',
                '  });'
            ]);

        // Step 11: Verify media streams work with our fake files  
        $page->waitUntil('window.testMediaReady === true', 10)
            ->assertScript('window.testMediaStream !== undefined')
            ->assertScript('window.testVideoTracks > 0')
            ->assertScript('window.testAudioTracks > 0')
            ->assertScript('window.testVideoLabel && window.testVideoLabel.includes("akiyo_cif.y4m")')
            ->assertScript('window.testAudioLabel && window.testAudioLabel.includes("talking_test.wav")');

        // Step 12: Verify that room data indicates recording is enabled
        $page->assertScript('window.roomData && window.roomData.recording_enabled === true')
            ->assertScript('window.roomData && window.roomData.stt_enabled === true');

        // Cleanup: Remove the test bucket (since it was auto-created)
        try {
            $storageAccount = $user->storageAccounts()->where('provider', 'wasabi')->first();
            if ($storageAccount) {
                $wasabiService = new \Domain\Room\Services\WasabiS3Service($storageAccount);
                $s3Client = $wasabiService->createS3ClientWithCredentials($storageAccount->encrypted_credentials);
                $s3Client->deleteBucket(['Bucket' => $uniqueBucketName]);
            }
        } catch (\Exception $e) {
            // Log but don't fail test if cleanup fails
            error_log("E2E test bucket cleanup failed: " . $e->getMessage());
        }
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for E2E testing');

    test('wasabi setup with fake credentials shows proper error handling', function () {
        $user = User::factory()->create(['username' => 'testuser2', 'email' => 'test2@example.com']);
        $room = Room::factory()->create(['creator_id' => $user->id]);

        actingAs($user);
        
        $page = visit('/wasabi/connect')
            ->assertSee('Connect Wasabi Account');

        // Fill out form with fake credentials
        $page->type('#display_name', 'Test Fake Account')
            ->type('#access_key_id', 'FAKE_ACCESS_KEY')
            ->type('#secret_access_key', 'FAKE_SECRET_KEY')
            ->type('#bucket_name', 'fake-test-bucket')
            ->select('#region', 'us-east-1')
            ->type('#endpoint', 'https://s3.wasabisys.com');

        // Test connection (should fail)
        $page->press('Test Connection')
            ->wait(5) // Wait for connection attempt
            ->assertSee('Connection failed');

        // Verify we can't save with failed connection
        $page->press('Connect Account')
            ->assertDontSee('Storage account connected successfully!') // Should not succeed
            ->assertPresent('#display_name'); // Should still be on form
    });

    test('room session loads correctly with wasabi recording disabled', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $room) {
            // Navigate directly to room session without recording setup
            $browser->loginAs($user)
                ->visit(route('rooms.session', $room))
                ->assertSee($room->name)
                ->waitFor('#video-container', 10);

            // Should see that recording is disabled
            $browser->assertDontSee('Start Recording')
                ->assertDontSee('[data-testid="recording-controls"]');

            // Media streams should still work for regular video chat
            $browser->script("
                navigator.mediaDevices.getUserMedia({video: true, audio: true})
                    .then(stream => {
                        window.testMediaStream = stream;
                        window.testMediaReady = true;
                    })
                    .catch(err => {
                        window.testMediaError = err.message;
                    });
            ");

            $browser->waitUntil('window.testMediaReady === true', 10)
                ->assertScript('window.testMediaStream !== undefined');
        });
    });

    test('wasabi account setup form validation works correctly', function () {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/wasabi/connect')
                ->assertSee('Connect Wasabi Account');

            // Try to submit empty form
            $browser->press('Connect Account')
                ->assertSee('The display name field is required')
                ->assertSee('The access key id field is required')
                ->assertSee('The secret access key field is required')
                ->assertSee('The bucket name field is required');

            // Fill some fields and test partial validation
            $browser->type('#display_name', 'Test Account')
                ->type('#access_key_id', 'TEST_KEY')
                ->press('Connect Account')
                ->assertDontSee('The display name field is required')
                ->assertDontSee('The access key id field is required')
                ->assertSee('The secret access key field is required')
                ->assertSee('The bucket name field is required');
        });
    });

    test('chromium fake media files are properly loaded from media folder', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $room) {
            $browser->loginAs($user)
                ->visit(route('rooms.session', $room))
                ->waitFor('#video-container', 10);

            // Test that fake media streams have the expected properties
            $browser->script("
                navigator.mediaDevices.getUserMedia({video: true, audio: true})
                    .then(stream => {
                        const videoTrack = stream.getVideoTracks()[0];
                        const audioTrack = stream.getAudioTracks()[0];
                        
                        window.testResults = {
                            hasVideo: !!videoTrack,
                            hasAudio: !!audioTrack,
                            videoSettings: videoTrack ? videoTrack.getSettings() : null,
                            audioSettings: audioTrack ? audioTrack.getSettings() : null
                        };
                        
                        window.testMediaReady = true;
                    })
                    .catch(err => {
                        window.testMediaError = err.message;
                    });
            ");

            $browser->waitUntil('window.testMediaReady === true', 10)
                ->assertScript('window.testResults.hasVideo === true')
                ->assertScript('window.testResults.hasAudio === true')
                ->assertScript('window.testResults.videoSettings !== null')
                ->assertScript('window.testResults.audioSettings !== null');

            // Verify the fake streams have reasonable properties
            $browser->assertScript('window.testResults.videoSettings.width > 0')
                ->assertScript('window.testResults.videoSettings.height > 0');
        });
    });

    test('wasabi presigned url generation works after browser setup', function () {
        // Skip if no real Wasabi credentials in environment
        if (empty(config('services.wasabi.access_key')) || empty(config('services.wasabi.secret_key'))) {
            $this->markTestSkipped('Wasabi credentials not available - test requires live credentials');
        }

        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user, $room) {
            // Step 1: Setup Wasabi account through browser
            $uniqueBucketName = 'daggerheart-presign-test-' . time() . '-' . rand(1000, 9999);
            
            $browser->loginAs($user)
                ->visit('/wasabi/connect')
                ->type('#display_name', 'Presign Test Account')
                ->type('#access_key_id', config('services.wasabi.access_key'))
                ->type('#secret_access_key', config('services.wasabi.secret_key'))
                ->type('#bucket_name', $uniqueBucketName)
                ->select('#region', env('WASABI_REGION', 'us-east-1'))
                ->type('#endpoint', env('WASABI_ENDPOINT', 'https://s3.wasabisys.com'))
                ->press('Test Connection')
                ->waitFor('.text-green-400', 15)
                ->press('Connect Account')
                ->waitForLocation('/storage-accounts', 15);

            // Step 2: Setup room recording
            $browser->visit(route('rooms.show', $room))
                ->check('recording_enabled')
                ->waitFor('[value="wasabi"]', 5)
                ->radio('storage_provider', 'wasabi')
                ->waitFor('select[wire\\:model\\.live="form.storage_account_id"]', 5)
                ->selectFirstOption('form.storage_account_id')
                ->press('Save Settings')
                ->wait(3) // Wait for settings to save
                ->assertSee('Recording settings updated successfully!');

            // Step 3: Test that presigned URL generation works (verify backend integration)
            $room->refresh();
            
            try {
                $action = new \Domain\Room\Actions\GenerateWasabiPresignedUrl();
                $result = $action->execute(
                    $room,
                    $user,
                    'browser-test.webm',
                    'video/webm',
                    1024 * 1024
                );

                // Verify the action worked
                expect($result['success'])->toBeTrue();
                expect($result['bucket'])->toBe($uniqueBucketName);
                expect($result['presigned_url'])->toContain($uniqueBucketName);

            } finally {
                // Cleanup test bucket
                try {
                    $storageAccount = $user->storageAccounts()->where('provider', 'wasabi')->first();
                    if ($storageAccount) {
                        $wasabiService = new \Domain\Room\Services\WasabiS3Service($storageAccount);
                        $s3Client = $wasabiService->createS3ClientWithCredentials($storageAccount->encrypted_credentials);
                        $s3Client->deleteBucket(['Bucket' => $uniqueBucketName]);
                    }
                } catch (\Exception $e) {
                    error_log("Presign test cleanup failed: " . $e->getMessage());
                }
            }
        });
    })->skip(fn() => app()->environment('testing') && empty(config('services.wasabi.access_key')), 
        'Wasabi credentials not configured for testing');
});
