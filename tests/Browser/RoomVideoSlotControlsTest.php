<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

describe('Room Video Slot Controls Error Handling', function () {
    beforeEach(function () {
        $this->gm = User::factory()->create();
        $this->room = Room::factory()->create([
            'creator_id' => $this->gm->id,
            'guest_count' => 2,
        ]);

        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->gm->id,
        ]);
    });

    test('hovering over video slots does not produce console errors', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->mouseover('.video-slot')
            ->pause(100)
            ->assertNoConsoleErrors();
    });

    test('video slot controls are present but hidden initially', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.video-controls.hidden')
            ->assertPresent('.refresh-connection-btn');
    });

    test('event handlers work with non-Element event targets', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->script("
                // Simulate event with text node as target (edge case)
                const slot = document.querySelector('.video-slot');
                const textNode = document.createTextNode('test');
                slot.appendChild(textNode);
                
                const event = new MouseEvent('mouseenter', {
                    bubbles: true,
                    cancelable: true,
                    view: window
                });
                
                Object.defineProperty(event, 'target', {
                    value: textNode,
                    enumerable: true
                });
                
                document.dispatchEvent(event);
                
                return 'success';
            ")
            ->pause(100)
            ->assertNoConsoleErrors();
    });

    test('refresh button is present in video controls', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.refresh-connection-btn')
            ->assertSeeIn('.refresh-connection-btn', 'Refresh');
    });

    test('kick button is present for room creator', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.kick-participant-btn');
    });

    test('kick button is not present for non-creator', function () {
        $player = User::factory()->create();
        
        RoomParticipant::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $player->id,
        ]);

        actingAs($player);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertMissing('.kick-participant-btn');
    });

    test('video controls become visible on hover', function () {
        actingAs($this->gm);

        visit("/rooms/{$this->room->invite_code}/session")
            ->waitFor('.video-slot')
            ->assertPresent('.video-controls.hidden')
            ->mouseover('.video-slot')
            ->pause(300) // Wait for CSS transition
            ->assertPresent('.video-controls:not(.hidden)');
    })->skip('Requires slot to be occupied for controls to show');
});

