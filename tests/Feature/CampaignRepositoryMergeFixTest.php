<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Repositories\CampaignRepository;
use Domain\Room\Models\Room;
use Domain\Room\Repositories\RoomRepository;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaignRepository = new CampaignRepository();
    $this->roomRepository = new RoomRepository();
});

test('getRecentByUser returns campaigns without getKey error', function () {
    // Create a campaign created by the user
    $createdCampaign = Campaign::factory()->create([
        'creator_id' => $this->user->id,
        'created_at' => now()->subDay(),
    ]);

    // Create another campaign and add the user as a member
    $joinedCampaign = Campaign::factory()->create([
        'created_at' => now()->subHours(2),
    ]);
    $joinedCampaign->members()->create([
        'user_id' => $this->user->id,
        'joined_at' => now(),
    ]);

    // This should not throw a getKey() error
    $result = $this->campaignRepository->getRecentByUser($this->user, 3);

    expect($result)->toHaveCount(2);
    expect($result->first()->id)->toBe($joinedCampaign->id); // Most recent
    expect($result->last()->id)->toBe($createdCampaign->id);
});

test('getAllUserCampaigns returns campaigns without getKey error', function () {
    // Create a campaign created by the user
    $createdCampaign = Campaign::factory()->create([
        'creator_id' => $this->user->id,
        'created_at' => now()->subDay(),
    ]);

    // Create another campaign and add the user as a member
    $joinedCampaign = Campaign::factory()->create([
        'created_at' => now()->subHours(2),
    ]);
    $joinedCampaign->members()->create([
        'user_id' => $this->user->id,
        'joined_at' => now(),
    ]);

    // This should not throw a getKey() error
    $result = $this->campaignRepository->getAllUserCampaigns($this->user);

    expect($result)->toHaveCount(2);
    expect($result->first()->id)->toBe($joinedCampaign->id); // Most recent
    expect($result->last()->id)->toBe($createdCampaign->id);
});

test('room repository getRecentByUser returns rooms without getKey error', function () {
    // Create a room created by the user
    $createdRoom = Room::factory()->create([
        'creator_id' => $this->user->id,
        'created_at' => now()->subDay(),
    ]);

    // Create another room and add the user as a participant
    $joinedRoom = Room::factory()->create([
        'created_at' => now()->subHours(2),
    ]);
    $joinedRoom->participants()->create([
        'user_id' => $this->user->id,
        'joined_at' => now(),
    ]);

    // This should not throw a getKey() error
    $result = $this->roomRepository->getRecentByUser($this->user, 3);

    expect($result)->toHaveCount(2);
    expect($result->first()->id)->toBe($joinedRoom->id); // Most recent
    expect($result->last()->id)->toBe($createdRoom->id);
});

test('dashboard route works without getKey error', function () {
    // Create a campaign created by the user
    Campaign::factory()->create([
        'creator_id' => $this->user->id,
    ]);

    // This should not throw an error
    actingAs($this->user)
        ->get('/dashboard')
        ->assertStatus(200);
});
