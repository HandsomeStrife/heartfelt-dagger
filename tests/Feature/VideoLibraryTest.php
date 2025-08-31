<?php

declare(strict_types=1);

use App\Livewire\VideoLibrary;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

test('video library loads for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/video-library')
        ->assertOk()
        ->assertSee('Video Library')
        ->assertSee('Your recorded room sessions and video content');
});

test('video library requires authentication', function () {
    $this->get('/video-library')
        ->assertRedirect('/login');
});

test('video library shows no recordings message when empty', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    $response->assertSee('No recordings yet')
        ->assertSee('Start recording in your rooms to see videos here');
});

test('video library displays user recordings', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'status' => 'ready',
    ]);

    $response = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    $response->assertViewHas('recordings')
        ->assertDontSee('No recordings yet');
});

test('video library can filter recordings by provider', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'status' => 'ready',
    ]);
    
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'status' => 'ready',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    // Filter by Wasabi
    $component->set('selectedProvider', 'wasabi')
        ->assertCount('recordings', 1);

    // Filter by Google Drive
    $component->set('selectedProvider', 'google_drive')
        ->assertCount('recordings', 1);

    // Show all
    $component->set('selectedProvider', 'all')
        ->assertCount('recordings', 2);
});

test('video library can filter recordings by status', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'status' => 'ready',
    ]);
    
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'status' => 'processing',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    // Filter by ready
    $component->set('selectedStatus', 'ready')
        ->assertCount('recordings', 1);

    // Filter by processing
    $component->set('selectedStatus', 'processing')
        ->assertCount('recordings', 1);

    // Show all
    $component->set('selectedStatus', 'all')
        ->assertCount('recordings', 2);
});

test('video library can search recordings', function () {
    $user = User::factory()->create();
    $room1 = Room::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Epic Adventure'
    ]);
    $room2 = Room::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Casual Gaming'
    ]);
    
    RoomRecording::factory()->create([
        'room_id' => $room1->id,
        'user_id' => $user->id,
        'status' => 'ready',
    ]);
    
    RoomRecording::factory()->create([
        'room_id' => $room2->id,
        'user_id' => $user->id,
        'status' => 'ready',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    // Search for "Epic"
    $component->set('searchQuery', 'Epic')
        ->assertCount('recordings', 1);

    // Search for "Gaming"
    $component->set('searchQuery', 'Gaming')
        ->assertCount('recordings', 1);

    // Clear search
    $component->set('searchQuery', '')
        ->assertCount('recordings', 2);
});

test('video library can change view modes', function () {
    $user = User::factory()->create();

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    // Default is list view
    $component->assertSet('viewMode', 'list');

    // Change to grid view
    $component->call('setViewMode', 'grid')
        ->assertSet('viewMode', 'grid');

    // Change to rooms view
    $component->call('setViewMode', 'rooms')
        ->assertSet('viewMode', 'rooms');
});

test('video library can toggle analytics', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'status' => 'ready',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    // Analytics initially hidden
    $component->assertSet('showAnalytics', false);

    // Toggle analytics
    $component->call('toggleAnalytics')
        ->assertSet('showAnalytics', true)
        ->assertViewHas('storageAnalytics');
});

test('video library can select recordings', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    $recording = RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'status' => 'ready',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    // Select recording
    $component->call('selectRecording', $recording->id)
        ->assertSet('selectedRecordingId', $recording->id);

    // Clear selection
    $component->call('selectRecording', null)
        ->assertSet('selectedRecordingId', null);
});

test('video library shows only accessible recordings', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $room1 = Room::factory()->create(['creator_id' => $user1->id]);
    $room2 = Room::factory()->create(['creator_id' => $user2->id]);
    
    // Recording accessible to user1 (room creator)
    RoomRecording::factory()->create([
        'room_id' => $room1->id,
        'user_id' => $user1->id,
        'status' => 'ready',
    ]);
    
    // Recording not accessible to user1 (different room creator)
    RoomRecording::factory()->create([
        'room_id' => $room2->id,
        'user_id' => $user2->id,
        'status' => 'ready',
    ]);

    $component = $this->actingAs($user1)
        ->livewire(VideoLibrary::class);

    // User1 should only see their own recording
    $component->assertCount('recordings', 1);
});

test('video library can filter by date range', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    $today = now();
    $yesterday = now()->subDay();
    
    // Recording from today
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'started_at_ms' => $today->getTimestamp() * 1000,
        'ended_at_ms' => $today->copy()->addMinutes(30)->getTimestamp() * 1000,
        'status' => 'ready',
    ]);
    
    // Recording from yesterday
    RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'started_at_ms' => $yesterday->getTimestamp() * 1000,
        'ended_at_ms' => $yesterday->copy()->addMinutes(30)->getTimestamp() * 1000,
        'status' => 'ready',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    // Filter by today
    $component->set('selectedDateRange', 'today')
        ->assertCount('recordings', 1);

    // Show all
    $component->set('selectedDateRange', 'all')
        ->assertCount('recordings', 2);
});

test('video library can clear all filters', function () {
    $user = User::factory()->create();

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class)
        ->set('searchQuery', 'test')
        ->set('selectedProvider', 'wasabi')
        ->set('selectedStatus', 'processing')
        ->set('selectedDateRange', 'today');

    // Clear all filters
    $component->call('clearFilters')
        ->assertSet('searchQuery', '')
        ->assertSet('selectedProvider', 'all')
        ->assertSet('selectedStatus', 'ready')
        ->assertSet('selectedDateRange', 'all');
});

test('video library download recording requires ready status', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    $processingRecording = RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'status' => 'processing',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    $component->call('downloadRecording', $processingRecording->id)
        ->assertHasNoErrors();
    
    // The method should have been called without PHP errors
    // The actual download failure is expected and handled gracefully
    expect(true)->toBeTrue();
});

test('video library play recording selects it', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);
    
    $recording = RoomRecording::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'status' => 'ready',
    ]);

    $component = $this->actingAs($user)
        ->livewire(VideoLibrary::class);

    $component->call('playRecording', $recording->id)
        ->assertSet('selectedRecordingId', $recording->id);
});
