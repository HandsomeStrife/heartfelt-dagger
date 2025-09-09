<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hides fear and countdown trackers when GM is not joined', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Set some game state so there's something to potentially display
    $room->setFearLevel(5);
    $room->setCountdownTracker('test-timer', 'Test Timer', 10);
    $room->save();

    // Player visits room - should not see game state overlays
    $this->actingAs($player)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.video-slot')
        ->assertDontSee('Fear') // Should not see fear tracker
        ->assertDontSee('Test Timer'); // Should not see countdown tracker
});

it('shows fear and countdown trackers when GM joins a slot', function () {
    $gm = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Set some game state
    $room->setFearLevel(3);
    $room->setCountdownTracker('initiative', 'Initiative', 8);
    $room->save();

    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.video-slot')
        ->pause(1000) // Wait for initial state
        ->assertScript('return document.querySelector("[data-game-state-overlay]").classList.contains("hidden")', true) // Initially hidden
        ->click('.join-btn') // GM joins a slot
        ->waitFor('.game-state-overlay:not(.hidden)', 10) // Wait for overlay to appear
        ->assertVisible('[data-game-state-overlay]')
        ->assertSee('Fear')
        ->assertSee('3') // Fear level
        ->assertSee('Initiative')
        ->assertSee('8'); // Timer value
});

it('hides trackers when GM leaves the slot', function () {
    $gm = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.video-slot')
        ->click('.join-btn') // GM joins
        ->waitFor('[data-game-state-overlay]:not(.hidden)') // Wait for overlay to show
        ->assertVisible('[data-game-state-overlay]')
        ->click('.leave-btn') // GM leaves (if leave button exists)
        ->waitFor('[data-game-state-overlay].hidden', 5) // Wait for overlay to hide
        ->assertNotVisible('[data-game-state-overlay]');
});

it('shows trackers to all participants when GM is present', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // Set game state
    $room->setFearLevel(7);
    $room->save();

    // Start GM session and join
    $gmBrowser = $this->actingAs($gm)->browse(function ($browser) use ($room) {
        $browser->visit("/rooms/{$room->invite_code}/session")
            ->waitFor('.video-slot')
            ->click('.join-btn') // GM joins
            ->waitFor('[data-game-state-overlay]:not(.hidden)');
    });

    // Player joins and should see the trackers
    $playerBrowser = $this->actingAs($player)->browse(function ($browser) use ($room) {
        $browser->visit("/rooms/{$room->invite_code}/session")
            ->waitFor('.video-slot')
            ->waitFor('[data-game-state-overlay]:not(.hidden)', 10) // Should see overlay since GM is present
            ->assertVisible('[data-game-state-overlay]')
            ->assertSee('Fear')
            ->assertSee('7');
    });

    $gmBrowser->assertVisible('[data-game-state-overlay]');
    $playerBrowser->assertVisible('[data-game-state-overlay]');
});
