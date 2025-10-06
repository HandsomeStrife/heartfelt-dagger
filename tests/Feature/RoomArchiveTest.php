<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Actions\ArchiveRoomAction;
use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\User\Models\User;

describe('Room Archive Functionality', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['creator_id' => $this->user->id]);
        $this->room = Room::factory()->create([
            'creator_id' => $this->user->id,
            'campaign_id' => $this->campaign->id,
            'status' => RoomStatus::Active,
        ]);
    });

    test('room creator can archive a room', function () {
        $action = new ArchiveRoomAction();
        
        $action->execute($this->room, $this->user);
        
        $this->room->refresh();
        expect($this->room->status)->toBe(RoomStatus::Archived);
    });

    test('non-creator cannot archive a room', function () {
        $otherUser = User::factory()->create();
        $action = new ArchiveRoomAction();
        
        expect(fn () => $action->execute($this->room, $otherUser))
            ->toThrow(\Exception::class, 'Only the room creator can archive this room.');
    });

    test('cannot archive an already archived room', function () {
        $this->room->update(['status' => RoomStatus::Archived]);
        $action = new ArchiveRoomAction();
        
        expect(fn () => $action->execute($this->room, $this->user))
            ->toThrow(\Exception::class, 'Room is already archived.');
    });

    test('archived rooms are accessible via recordings route', function () {
        $this->room->update(['status' => RoomStatus::Archived]);
        
        $response = $this->actingAs($this->user)
            ->get(route('rooms.recordings', $this->room));
        
        $response->assertOk();
        $response->assertSee($this->room->name);
        $response->assertSee('Archived');
    });

    test('archived rooms are accessible via transcripts route', function () {
        $this->room->update(['status' => RoomStatus::Archived]);
        
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));
        
        $response->assertOk();
        $response->assertSee($this->room->name);
        $response->assertSee('Archived');
    });

    test('campaign members can access room recordings', function () {
        $member = User::factory()->create();
        $this->campaign->members()->create([
            'user_id' => $member->id,
            'joined_at' => now(),
        ]);
        
        $response = $this->actingAs($member)
            ->get(route('rooms.recordings', $this->room));
        
        $response->assertOk();
    });

    test('non-campaign members cannot access room recordings', function () {
        $nonMember = User::factory()->create();
        
        $response = $this->actingAs($nonMember)
            ->get(route('rooms.recordings', $this->room));
        
        $response->assertForbidden();
    });
});
