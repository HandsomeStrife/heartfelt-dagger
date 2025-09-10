<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays fear tracker and countdown controls in GM sidebar', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->forCampaign($campaign)->create(['creator_id' => $user->id]);

    // Set initial game state
    $campaign->setFearLevel(3);
    $campaign->setCountdownTracker('test-timer', 'Test Timer', 10);
    $campaign->save();

    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->click('[data-testid="sidebar-dropdown"]')
        ->click('button[x-show="!sidebarVisible"]') // Open Game State tab
        ->waitFor('[x-show="activeTab === \'gamestate\'"]')
        ->within('[x-show="activeTab === \'gamestate\'"]', function ($browser) {
            $browser->assertSee('Game State Management')
                ->assertSee('Fear Level')
                ->assertSee('3') // Current fear level
                ->assertSee('Countdown Trackers')
                ->assertSee('Test Timer')
                ->assertSee('10'); // Timer value
        });
});

it('allows GM to increase fear level', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->click('[data-testid="sidebar-dropdown"]')
        ->within('[x-show="activeTab === \'gamestate\'"]', function ($browser) {
            $browser->click('button[wire:click="increaseFear"]')
                ->waitFor('[data-fear-display="level"]')
                ->assertSeeIn('[data-fear-display="level"]', '1');
        });

    // Verify in database
    expect($room->fresh()->getFearLevel())->toBe(1);
});

it('allows GM to create countdown tracker', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->click('[data-testid="sidebar-dropdown"]')
        ->within('[x-show="activeTab === \'gamestate\'"]', function ($browser) {
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
    expect($trackers['Initiative Timer'])->toBeDefined();
});

it('displays fear and countdown in GM video slot overlay', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->forCampaign($campaign)->create(['creator_id' => $user->id]);

    // Set game state
    $campaign->setFearLevel(5);
    $campaign->setCountdownTracker('turn-timer', 'Turn Timer', 8);
    $campaign->save();

    $this->actingAs($user)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.video-slot')
        ->with('.game-state-overlay', function ($browser) {
            $browser->assertSee('Fear')
                ->assertSee('5')
                ->within('[data-countdown-display="container"]', function ($browser) {
                    $browser->assertSee('Turn Timer')
                        ->assertSee('8');
                });
        });
});

it('shows different game state for campaign vs standalone room', function () {
    $user = User::factory()->create();

    // Campaign room
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $campaignRoom = Room::factory()->forCampaign($campaign)->create(['creator_id' => $user->id]);
    $campaign->setFearLevel(10);
    $campaign->save();

    // Standalone room
    $standaloneRoom = Room::factory()->create(['creator_id' => $user->id]);
    $standaloneRoom->setFearLevel(5);
    $standaloneRoom->save();

    // Test campaign room shows campaign fear level
    $this->actingAs($user)
        ->visit("/rooms/{$campaignRoom->invite_code}/session")
        ->waitFor('[data-fear-display="level"]')
        ->assertSeeIn('[data-fear-display="level"]', '10');

    // Test standalone room shows room fear level
    $this->actingAs($user)
        ->visit("/rooms/{$standaloneRoom->invite_code}/session")
        ->waitFor('[data-fear-display="level"]')
        ->assertSeeIn('[data-fear-display="level"]', '5');
});

it('synchronizes fear level changes across participants', function () {
    $gm = User::factory()->create();
    $player = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $gm->id]);

    // GM increases fear level
    $this->actingAs($gm)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('.room-sidebar')
        ->within('[x-show="activeTab === \'gamestate\'"]', function ($browser) {
            $browser->click('button[wire:click="increaseFear"]')
                ->waitFor('[data-fear-display="level"]');
        });

    // Player should see the updated fear level
    $this->actingAs($player)
        ->visit("/rooms/{$room->invite_code}/session")
        ->waitFor('[data-fear-display="indicator"]')
        ->assertSeeIn('[data-fear-display="indicator"]', '1');
});
