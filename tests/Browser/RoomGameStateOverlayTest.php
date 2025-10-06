<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Domain\Character\Models\Character;

use function Pest\Laravel\actingAs;

describe('Room Game State Overlay Visibility', function () {
    beforeEach(function () {
        // Create campaign with game state
        $this->campaign = Campaign::factory()->create([
            'fear_level' => 3,
        ]);

        // Create countdown tracker
        $this->campaign->countdownTrackers()->create([
            'name' => 'Test Timer',
            'value' => 5,
        ]);

        // Create GM user
        $this->gm = User::factory()->create();

        // Create room with campaign
        $this->room = Room::factory()->create([
            'creator_id' => $this->gm->id,
            'campaign_id' => $this->campaign->id,
            'guest_count' => 2,
        ]);

        // Create GM participant
        $this->gmParticipant = RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
        ]);
    });

    test('game state overlays are present in DOM for campaign rooms', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('[data-game-state-overlay]')
            ->assertPresent('[data-game-state-overlay]')
            ->assertPresent('[data-fear-display="indicator"]')
            ->assertPresent('[data-countdown-display="container"]');
    });

    test('fear tracker displays correct initial value', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('[data-fear-display="indicator"]')
            ->assertSeeIn('[data-fear-display="indicator"]', '3');
    });

    test('game state overlays are hidden by default when GM not joined', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('[data-game-state-overlay]')
            ->assertPresent('[data-game-state-overlay].hidden');
    });

    test('MutationObserver detects and caches new overlays', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('[data-game-state-overlay]')
            ->pause(600) // Wait for delayed refresh (500ms + buffer)
            ->script('return window.roomWebRTC?.fearCountdownManager?.gameStateOverlays?.length || 0', function ($count) {
                expect($count)->toBeGreaterThan(0);
            });
    });

    test('overlays become visible when GM joins slot', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.slot-gm-join')
            ->click('.slot-gm-join')
            ->pause(1000) // Wait for join process
            ->waitFor('[data-game-state-overlay]:not(.hidden)', 5)
            ->assertPresent('[data-game-state-overlay]:not(.hidden)');
    })->skip('Requires consent dialog handling');

    test('overlays remain visible for viewers when GM is in room', function () {
        // Create viewer user
        $viewer = User::factory()->create();

        // Create viewer participant
        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $viewer->id,
        ]);

        actingAs($viewer);

        // Set GM as joined in slot
        $this->room->refresh();

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('[data-game-state-overlay]')
            ->pause(1000);

        // If GM is present, overlays should be visible on GM's slot
        // This test validates the viewer can see game state
    })->skip('Requires full WebRTC setup with GM joined');

    test('countdown trackers are rendered in overlay', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('[data-countdown-display="container"]')
            ->assertPresent('[data-countdown-display="container"]');
    });

    test('fear tracker icon is rendered correctly', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('[data-game-state-overlay]')
            ->assertPresent('[data-game-state-overlay] svg')
            ->assertPresent('[data-fear-display="indicator"]');
    });
});

