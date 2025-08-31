<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;
use Domain\User\Models\UserStorageAccount;
use Laravel\Dusk\Browser;

test('complete room recording setup flow with local storage', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Step 1: Verify initial state
    $page->assertSee('Recording Disabled')
        ->assertSee('Recording Settings')
        ->assertDontSee('Storage Provider');

    // Step 2: Enable recording
    $page->check('recording_enabled')
        ->waitFor('[wire\\:model\\.live="form.storage_provider"]')
        ->assertSee('Storage Provider')
        ->assertSee('Local Server')
        ->assertSee('Wasabi Cloud')
        ->assertSee('Google Drive');

    // Step 3: Select local storage
    $page->radio('storage_provider', 'local')
        ->waitFor('.border-amber-500') // Wait for selection to be visible
        ->assertSee('Local Server');

    // Step 4: Enable STT as well
    $page->check('stt_enabled');

    // Step 5: Save settings
    $page->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording Enabled'); // Status should update

    // Step 6: Verify settings persist after page reload
    $page->refresh()
        ->assertSee('Recording Enabled')
        ->assertChecked('recording_enabled')
        ->assertChecked('stt_enabled')
        ->assertSee('Current: Local Server Storage');

    // Step 7: Verify database state
    $room->refresh();
    $room->load('recordingSettings');
    expect($room->recordingSettings)->not()->toBeNull();
    expect($room->recordingSettings->recording_enabled)->toBeTrue();
    expect($room->recordingSettings->stt_enabled)->toBeTrue();
    expect($room->recordingSettings->storage_provider)->toBe('local');
});

test('complete room recording setup flow with wasabi storage', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // Step 1: Visit room page
    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Step 2: Enable recording and select Wasabi (should show no accounts)
    $page->check('recording_enabled')
        ->waitFor('[value="wasabi"]')
        ->radio('storage_provider', 'wasabi')
        ->waitFor('button:contains("Connect Wasabi Account")')
        ->assertSee('No Wasabi accounts connected')
        ->assertSee('Connect Wasabi Account');

    // Step 3: Click connect button (should go to Wasabi setup)
    $page->click('button:contains("Connect Wasabi Account")')
        ->waitForLocation('/wasabi/connect')
        ->assertSee('Connect Wasabi Account')
        ->assertSee('Add your Wasabi cloud storage credentials');

    // Step 4: Fill out Wasabi form
    $page->type('#display_name', 'My DaggerHeart Recordings')
        ->type('#access_key_id', 'AKIAIOSFODNN7EXAMPLE')
        ->type('#secret_access_key', 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY')
        ->type('#bucket_name', 'daggerheart-recordings')
        ->select('#region', 'us-west-1');

    // Step 5: Try to save (will likely fail due to invalid credentials, but form should work)
    $page->press('Connect Account');

    // Due to invalid test credentials, this will fail, but we can verify the form submission works
    // In a real scenario with valid credentials, this would redirect back to the room
    $page->waitFor('.text-red-400', 10); // Wait for error or success

    // For testing purposes, manually create the storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
            'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'bucket_name' => 'daggerheart-recordings',
            'region' => 'us-west-1',
            'endpoint' => 'https://s3.us-west-1.wasabisys.com',
        ],
        'display_name' => 'My DaggerHeart Recordings',
        'is_active' => true,
    ]);

    // Step 6: Return to room and complete setup
    $page->visit(route('rooms.show', $room))
        ->check('recording_enabled')
        ->waitFor('[value="wasabi"]')
        ->radio('storage_provider', 'wasabi')
        ->waitFor('select[wire\\:model\\.live="form.storage_account_id"]')
        ->select('form.storage_account_id', $storageAccount->id)
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!');

    // Step 7: Verify final state
    $page->assertSee('Recording Enabled')
        ->assertSee('Current: Wasabi Cloud Storage')
        ->assertSee('(My DaggerHeart Recordings)');
});

test('room recording settings integration with participant flow', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    // Step 1: Creator sets up recording
    actingAs($creator);
    $creatorPage = visit(route('rooms.show', $room));
    
    $creatorPage->check('recording_enabled')
        ->waitFor('[value="local"]')
        ->radio('storage_provider', 'local')
        ->check('stt_enabled')
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording Enabled');

    // Step 2: Participant views room (should not see settings)
    actingAs($participant);
    $participantPage = visit(route('rooms.show', $room));
    
    $participantPage->assertDontSee('Recording Settings')
        ->assertDontSee('Configure video recording and storage for this room');

    // Step 3: Participant can still see room info
    $participantPage->assertSee($room->name)
        ->assertSee($room->description)
        ->assertSee('Join Room'); // Should be able to join
});

test('room recording settings validation prevents invalid configurations', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Test 1: Enable recording without selecting storage provider
    $page->check('recording_enabled')
        ->waitFor('[value="local"]')
        // Don't select any provider
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Please select a storage provider when recording is enabled')
        ->assertSee('Please select a storage provider when recording is enabled');

    // Test 2: Select Wasabi without account
    $page->radio('storage_provider', 'wasabi')
        ->waitFor('button:contains("Connect Wasabi Account")')
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Please select a Wasabi storage account or create one')
        ->assertSee('Please select a Wasabi storage account or create one');

    // Test 3: Disable recording (should allow save)
    $page->uncheck('recording_enabled')
        ->waitUntilMissing('[wire\\:model\\.live="form.storage_provider"]')
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!');
});

test('room recording settings update existing configuration', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // Create initial settings
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'local',
        'storage_account_id' => null,
    ]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Should load existing settings
    $page->assertSee('Recording Enabled')
        ->assertChecked('recording_enabled')
        ->assertNotChecked('stt_enabled')
        ->assertSee('Current: Local Server Storage');

    // Update settings
    $page->check('stt_enabled')
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!');

    // Verify update
    $room->refresh();
    $room->load('recordingSettings');
    expect($room->recordingSettings->recording_enabled)->toBeTrue();
    expect($room->recordingSettings->stt_enabled)->toBeTrue();
    expect($room->recordingSettings->storage_provider)->toBe('local');
});

test('multiple storage accounts selection flow', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // Create multiple Wasabi accounts
    $account1 = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'KEY1',
            'secret_access_key' => 'SECRET1',
            'bucket_name' => 'bucket1',
        ],
        'display_name' => 'Primary Wasabi Account',
        'is_active' => true,
    ]);

    $account2 = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'KEY2',
            'secret_access_key' => 'SECRET2',
            'bucket_name' => 'bucket2',
        ],
        'display_name' => 'Secondary Wasabi Account',
        'is_active' => true,
    ]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording and select Wasabi
    $page->check('recording_enabled')
        ->waitFor('[value="wasabi"]')
        ->radio('storage_provider', 'wasabi')
        ->waitFor('select[wire\\:model\\.live="form.storage_account_id"]');

    // Should see both accounts in dropdown
    $page->assertSeeIn('select[wire\\:model\\.live="form.storage_account_id"]', 'Primary Wasabi Account')
        ->assertSeeIn('select[wire\\:model\\.live="form.storage_account_id"]', 'Secondary Wasabi Account');

    // Select second account
    $page->select('form.storage_account_id', $account2->id)
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!')
        ->assertSee('(Secondary Wasabi Account)');

    // Verify correct account was saved
    $room->refresh();
    $room->load('recordingSettings');
    expect($room->recordingSettings->storage_account_id)->toBe($account2->id);
});

test('room recording ui responsive behavior', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Test mobile viewport
    $page->resize(375, 667); // iPhone size

    $page->assertVisible('input#recording_enabled')
        ->assertVisible('button:contains("Save Settings")');

    // Test tablet viewport  
    $page->resize(768, 1024); // iPad size

    $page->assertVisible('input#recording_enabled')
        ->assertVisible('button:contains("Save Settings")');

    // Test desktop viewport
    $page->resize(1920, 1080);

    $page->assertVisible('input#recording_enabled')
        ->assertVisible('button:contains("Save Settings")');
});

test('room recording settings accessibility features', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Check for proper labels
    $page->assertSee('Enable Video Recording')
        ->assertSee('Enable Speech-to-Text')
        ->assertSee('Storage Provider');

    // Check for proper form structure
    $page->assertSourceHas('for="recording_enabled"')
        ->assertSourceHas('for="stt_enabled"')
        ->assertAttribute('#recording_enabled', 'type', 'checkbox')
        ->assertAttribute('#stt_enabled', 'type', 'checkbox');

    // Check for ARIA or similar accessibility attributes if present
    // (These would depend on your specific implementation)
});

test('room recording error handling and recovery', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Test error recovery flow
    $page->check('recording_enabled')
        ->waitFor('[value="local"]')
        ->press('Save Settings') // Don't select provider - should error
        ->wait(2) // Wait for content to load
            ->assertSee('Please select a storage provider when recording is enabled')
        ->assertSee('Please select a storage provider when recording is enabled');

    // Fix the error
    $page->radio('storage_provider', 'local')
        ->press('Save Settings')
        ->wait(2) // Wait for content to load
            ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!');

    // Error should be cleared
    $page->assertDontSee('Please select a storage provider when recording is enabled');
});
