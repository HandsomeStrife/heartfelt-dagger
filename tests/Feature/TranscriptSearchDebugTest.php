<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;

test('transcript search functionality debug', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    // Create specific transcripts
    $transcript1 = RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Alice',
        'character_class' => 'Warrior',
        'text' => 'Hello everyone, ready for adventure?',
        'started_at_ms' => 1000000,
        'ended_at_ms' => 1002000,
        'language' => 'en-US',
        'provider' => 'browser',
    ]);

    $transcript2 = RoomTranscript::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Bob',
        'character_class' => 'Wizard',
        'text' => 'I cast magic missile at the darkness!',
        'started_at_ms' => 1005000,
        'ended_at_ms' => 1007000,
        'language' => 'en-US',
        'provider' => 'browser',
    ]);

    // Test the search functionality
    $response = $this->actingAs($user)
        ->get(route('rooms.transcripts', $room) . '?search=magic');

    $response->assertOk();
    
    // Debug: Check the view data
    $filteredTranscripts = $response->viewData('transcripts');
    $allTranscripts = $response->viewData('allTranscripts');
    
    // Verify filtering worked in controller
    expect($allTranscripts->count())->toBe(2);
    expect($filteredTranscripts->count())->toBe(1);
    expect($filteredTranscripts->first()->text)->toContain('magic missile');
    
    // Check if the search term appears in response
    $content = $response->getContent();
    expect($content)->toContain('magic missile');
    expect($content)->not->toContain('Hello everyone');
});
