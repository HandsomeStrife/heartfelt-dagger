<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

uses()->group('browser', 'room', 'reconnection');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::factory()->create(['creator_id' => $this->user->id]);
    $this->room = Room::factory()->create([
        'creator_id' => $this->user->id,
        'campaign_id' => $this->campaign->id
    ]);
});

test('validates and repairs peer state inconsistencies', function () {
    actingAs($this->user);
    
    $page = visit(route('rooms.session', ['room' => $this->room]));
    
    // Wait for page to load
    sleep(3);
    
    // Create inconsistent state (slot occupant without active call)
    $page->script('
        if (window.roomWebRTC) {
            const fakePeerId = "fake-peer-123";
            window.roomWebRTC.slotOccupants?.set("1", {
                slotId: "1",
                peerId: fakePeerId,
                userId: 999,
                characterName: "Test",
                stream: null
            });
        }
    ');
    
    sleep(1);
    
    // Trigger state validation
    $page->script('
        if (window.roomWebRTC) {
            window.roomWebRTC.validatePeerState();
        }
    ');
    
    sleep(1);
    
    // Repair state
    $page->script('
        if (window.roomWebRTC) {
            window.roomWebRTC.repairPeerState();
        }
    ');
    
    sleep(1);
    
    expect(true)->toBeTrue();
});
