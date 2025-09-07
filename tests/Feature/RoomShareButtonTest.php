<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\User\Models\User;

test('share button is shown for non-campaign rooms', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => null, // Not part of a campaign
    ]);

    $response = $this->actingAs($user)->get(route('rooms.show', $room));

    $response->assertOk();
    $response->assertSee('Share');
    $response->assertSee('roomInviteModal');
});

test('share button is hidden for campaign rooms', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id, // Part of a campaign
    ]);

    $response = $this->actingAs($user)->get(route('rooms.show', $room));

    $response->assertOk();
    $response->assertDontSee('Share');
    $response->assertDontSee('roomInviteModal');
});

test('join room button is always shown for room creator', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    $response = $this->actingAs($user)->get(route('rooms.show', $room));

    $response->assertOk();
    $response->assertSee('Join Room');
});

test('viewer link functionality is not affected by campaign status', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
    ]);

    // Viewer functionality should still work
    $response = $this->get(route('rooms.viewer', $room->viewer_code));
    $response->assertOk();
});
