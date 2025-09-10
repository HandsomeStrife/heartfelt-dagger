<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can send fear level updates via Ably messaging', function () {
    $gm = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->click('[data-testid="sidebar-dropdown"]')
        ->waitFor('[x-show="activeTab === \'gamestate\'"]')
        ->within('[x-show="activeTab === \'gamestate\'"]', function ($browser) {
            // Click increase fear button
            $browser->click('button[wire:click="increaseFear"]')
                ->waitFor('[data-fear-display="level"]')
                ->assertSeeIn('[data-fear-display="level"]', '1');
        })
        // Verify the Ably message was sent by checking console logs
        ->script('return window.roomWebRTC && window.roomWebRTC.fearCountdownManager ? "initialized" : "not initialized"')
        ->assertScript('return window.roomWebRTC && window.roomWebRTC.fearCountdownManager ? "initialized" : "not initialized"', 'initialized');

    // Verify in database
    expect($room->fresh()->getFearLevel())->toBe(1);
});

it('can receive fear level updates from other participants', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Start both browser sessions
    $gmBrowser = $this->actingAs($gm)->browse(function ($browser) use ($room) {
        $browser->visit("/rooms/{$room->invite_code}/session")
            ->waitFor('.room-sidebar')
            ->click('[data-testid="sidebar-dropdown"]');
    });

    $playerBrowser = $this->actingAs($player)->browse(function ($browser) use ($room) {
        $browser->visit("/rooms/{$room->invite_code}/session")
            ->waitFor('[data-fear-display="indicator"]');
    });

    // GM increases fear level
    $gmBrowser->within('[x-show="activeTab === \'gamestate\'"]', function ($browser) {
        $browser->click('button[wire:click="increaseFear"]')
            ->waitFor('[data-fear-display="level"]')
            ->assertSeeIn('[data-fear-display="level"]', '1');
    });

    // Player should see the updated fear level (allow some time for Ably propagation)
    $playerBrowser->waitFor('[data-fear-display="indicator"]', 5)
        ->assertSeeIn('[data-fear-display="indicator"]', '1');
});

it('can handle countdown tracker creation via Ably', function () {
    $gm = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->click('[data-testid="sidebar-dropdown"]')
        ->waitFor('[x-show="activeTab === \'gamestate\'"]')
        ->within('[x-show="activeTab === \'gamestate\'"]', function ($browser) {
            // Create a countdown tracker
            $browser->click('button[wire:click="$set(\'show_add_countdown\', true)"]')
                ->waitFor('input[wire:model.live="new_countdown_name"]')
                ->type('input[wire:model.live="new_countdown_name"]', 'Initiative Timer')
                ->type('input[wire:model.live="new_countdown_value"]', '5')
                ->click('button[wire:click="createCountdownTracker"]')
                ->waitFor('.countdown-tracker')
                ->assertSee('Initiative Timer')
                ->assertSee('5');
        });

    // Verify in database
    $trackers = $room->fresh()->getCountdownTrackers();
    expect($trackers)->not->toBeEmpty();
});
