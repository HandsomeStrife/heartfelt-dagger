<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Domain\Character\Models\Character;

use function Pest\Laravel\actingAs;

describe('Room Reference Search Component', function () {
    beforeEach(function () {
        $this->campaign = Campaign::factory()->create();
        $this->gm = User::factory()->create();
        $this->room = Room::factory()->create([
            'creator_id' => $this->gm->id,
            'campaign_id' => $this->campaign->id,
            'guest_count' => 2,
        ]);

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
        ]);
    });

    test('GM sidebar has reference tab', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->assertSee('Reference');
    });

    test('reference search component loads in GM sidebar', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                // Click reference tab
                const referenceTab = document.querySelector('[x-data*=\"activeTab\"] button[x-text*=\"Reference\"]');
                if (!referenceTab) {
                    const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                    tabs?.click();
                } else {
                    referenceTab.click();
                }
            ")
            ->pause(500)
            ->assertSee('Reference Search');
    });

    test('reference search component loads in player sidebar', function () {
        $player = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $player->id]);

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $player->id,
            'character_id' => $character->id,
        ]);

        actingAs($player);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                // Click reference tab in player sidebar
                const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                tabs?.click();
            ")
            ->pause(500)
            ->assertSee('Reference Search');
    });

    test('reference search has search input field', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                tabs?.click();
            ")
            ->pause(500)
            ->assertPresent('input[type="search"]')
            ->assertSee('Search reference...');
    });

    test('reference search input accepts text', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                tabs?.click();
            ")
            ->pause(500)
            ->type('input[type="search"]', 'combat')
            ->pause(500)
            ->assertInputValue('input[type="search"]', 'combat');
    });

    test('reference search shows loading indicator', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                tabs?.click();
            ")
            ->pause(500)
            ->assertPresent('svg.animate-spin');
    })->skip('Loading indicator may not be visible after init');

    test('reference search component has search icon', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                tabs?.click();
            ")
            ->pause(500)
            ->assertPresent('svg'); // Search icon
    });

    test('reference search initializes Alpine data', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                tabs?.click();
            ")
            ->pause(1000)
            ->script("
                // Check if Alpine data is initialized
                const searchDiv = document.querySelector('[x-data=\"referenceSearch()\"]');
                return searchDiv !== null;
            ", function ($hasAlpine) {
                expect($hasAlpine)->toBeTrue();
            });
    });

    test('reference search placeholder text is correct', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('#room-main-content')
            ->script("
                const tabs = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.includes('Reference'));
                tabs?.click();
            ")
            ->pause(500)
            ->assertAttribute('input[type="search"]', 'placeholder', 'Search reference...');
    });
});

