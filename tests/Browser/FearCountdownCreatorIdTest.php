<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('passes room creator_id to JavaScript correctly', function () {
    $gm = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->assertScript('return window.roomData.creator_id', $gm->id)
        ->assertScript('return window.currentUserId', $gm->id)
        ->assertScript('return window.roomData.creator_id === window.currentUserId', true);
});

it('correctly identifies GM status in fear countdown manager', function () {
    $gm = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->pause(2000) // Wait for initialization
        ->script('return debugFearCountdown()')
        ->assertScript('return debugFearCountdown().isGm', true)
        ->assertScript('return debugFearCountdown().roomCreatorId', $gm->id)
        ->assertScript('return debugFearCountdown().currentUserId', $gm->id);
});

it('shows fear trackers when GM joins after creator_id fix', function () {
    $gm = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Set some fear level
    $room->setFearLevel(5);
    $room->save();

    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->pause(1000) // Wait for initialization
        ->assertScript('return window.roomData.creator_id', $gm->id) // Verify creator_id is passed
        ->assertScript('return document.querySelector("[data-game-state-overlay]").classList.contains("hidden")', true) // Initially hidden
        ->click('.join-btn') // GM joins a slot
        ->pause(2000) // Wait for join process and detection
        ->assertScript('return debugFearCountdown().gmJoined', true) // Should detect GM joined
        ->waitFor('[data-game-state-overlay]:not(.hidden)', 10) // Wait for overlay to appear
        ->assertVisible('[data-game-state-overlay]')
        ->assertSee('Fear')
        ->assertSee('5'); // Fear level
});
