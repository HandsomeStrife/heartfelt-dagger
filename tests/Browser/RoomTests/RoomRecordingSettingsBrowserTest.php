<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Laravel\Dusk\Browser;
use function Pest\Laravel\actingAs;

test('room creator can access recording settings on room page', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    $page->assertSee('Recording Settings')
        ->assertSee('Configure video recording and storage for this room')
        ->assertSee('Enable Video Recording')
        ->assertSee('Recording Disabled'); // Default status badge
});

test('non-creator cannot see recording settings', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    actingAs($otherUser);
    $page = visit(route('rooms.show', $room));

    $page->assertDontSee('Recording Settings')
        ->assertDontSee('Configure video recording and storage for this room');
});

test('can enable video recording and see storage options', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    // Ensure no existing recording settings
    \Domain\Room\Models\RoomRecordingSettings::where('room_id', $room->id)->delete();

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Initially recording should be disabled
    $page->assertSee('Recording Disabled');

    // Enable recording
    $page->check('recording_enabled')
        ->wait(500) // Wait for Livewire to update
        ->assertSee('Storage Provider')
        ->assertSee('Local Server')
        ->assertSee('Wasabi Cloud')
        ->assertSee('Google Drive');
});

test('can enable stt when recording is enabled', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording first
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('#stt_enabled')
        ->assertSee('Enable Speech-to-Text')
        ->check('stt_enabled');
});

test('can select local storage and save settings', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording and select local storage
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('[value="local"]')
        ->radio('storage_provider', 'local')
        ->wait(500) // Wait for UI update
        ->assertVisible('.border-amber-500') // Wait for selected state
        ->press('Save Settings')
        ->wait(500) // Wait for response
        ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!');

    // Verify settings were saved
    $room->refresh();
    $room->load('recordingSettings');
    expect($room->recordingSettings)->not()->toBeNull();
    expect($room->recordingSettings->recording_enabled)->toBeTrue();
    expect($room->recordingSettings->storage_provider)->toBe('local');
});

test('can select wasabi storage with existing account', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create a Wasabi storage account
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
        'display_name' => 'My Wasabi Account',
        'is_active' => true,
    ]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording and select Wasabi
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('[value="wasabi"]')
        ->radio('storage_provider', 'wasabi')
        ->wait(500) // Wait for UI update
        ->assertVisible('select[wire\\:model\\.live="form.storage_account_id"]')
        ->assertSee('Wasabi Account')
        ->select('form.storage_account_id', $storageAccount->id)
        ->press('Save Settings')
        ->wait(500) // Wait for response
        ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!');

    // Verify settings were saved
    $room->refresh();
    $room->load('recordingSettings');
    expect($room->recordingSettings->recording_enabled)->toBeTrue();
    expect($room->recordingSettings->storage_provider)->toBe('wasabi');
    expect($room->recordingSettings->storage_account_id)->toBe($storageAccount->id);
});

test('shows connect button when no wasabi accounts exist', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording and select Wasabi
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('[value="wasabi"]')
        ->radio('storage_provider', 'wasabi')
        ->wait(500) // Wait for UI update
        ->assertVisible('button')
        ->assertSee('No Wasabi accounts connected')
        ->assertSee('Connect Wasabi Account');
});

test('shows connect button when no google drive accounts exist', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording and select Google Drive
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('[value="google_drive"]')
        ->radio('storage_provider', 'google_drive')
        ->wait(500) // Wait for UI update
        ->assertVisible('button')
        ->assertSee('No Google Drive accounts connected')
        ->assertSee('Connect Google Drive');
});

test('can disable recording and clear settings', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create initial settings
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => true,
        'storage_provider' => 'local',
        'storage_account_id' => null,
    ]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Should show as enabled initially
    $page->assertSee('Recording Enabled');

    // Disable recording
    $page->uncheck('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertDontSee('Storage Provider')
        ->press('Save Settings')
        ->wait(500) // Wait for response
        ->assertSee('Recording settings updated successfully!')
        ->assertSee('Recording settings updated successfully!');

    // Verify settings were updated
    $room->refresh();
    $room->load('recordingSettings');
    expect($room->recordingSettings->recording_enabled)->toBeFalse();
    expect($room->recordingSettings->stt_enabled)->toBeFalse();
    expect($room->recordingSettings->storage_provider)->toBeNull();
});

test('shows validation error for invalid configuration', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording but don't select storage provider
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('[value="local"]')
        // Don't select any storage provider
        ->press('Save Settings')
        ->wait(500) // Wait for response
        ->assertSee('Please select a storage provider when recording is enabled')
        ->assertSee('Please select a storage provider when recording is enabled');
});

test('auto-selects single available storage account', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create exactly one Wasabi storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'Auto-Selected Account',
        'is_active' => true,
    ]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording and select Wasabi
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('[value="wasabi"]')
        ->radio('storage_provider', 'wasabi')
        ->wait(500) // Wait for UI update
        ->assertVisible('select[wire\\:model\\.live="form.storage_account_id"]');

    // Should auto-select the single account
    $selectedValue = $page->value('select[wire\\:model\\.live="form.storage_account_id"]');
    expect($selectedValue)->toBe((string)$storageAccount->id);
});

test('displays current settings status in footer', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    // Create settings with Wasabi
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'My Test Account',
        'is_active' => true,
    ]);
    
    RoomRecordingSettings::create([
        'room_id' => $room->id,
        'recording_enabled' => true,
        'stt_enabled' => false,
        'storage_provider' => 'wasabi',
        'storage_account_id' => $storageAccount->id,
    ]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    $page->assertSee('Current: Wasabi Cloud Storage')
        ->assertSee('(My Test Account)');
});

test('shows loading state during save', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit(route('rooms.show', $room));

    // Enable recording and select local storage
    $page->check('recording_enabled')
        ->wait(500) // Wait for UI update
        ->assertVisible('[value="local"]')
        ->radio('storage_provider', 'local')
        ->press('Save Settings');

    // Should briefly show loading state
    $page->assertSee('Saving...');
});
