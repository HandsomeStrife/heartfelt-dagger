<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Fear and Countdown Integration', function () {
    it('can increase fear level directly through action', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $updateFearAction = new \Domain\Room\Actions\UpdateFearLevelAction;
        $result = $updateFearAction->increaseFear(null, $room, 1);

        expect($result->fear_level)->toBe(1);
        expect($room->fresh()->getFearLevel())->toBe(1);
    });

    it('can create countdown tracker through action', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $manageCountdownAction = new \Domain\Room\Actions\ManageCountdownTrackerAction;
        $tracker = $manageCountdownAction->createCountdownTracker(null, $room, 'Test Timer', 10);

        expect($tracker->name)->toBe('Test Timer');
        expect($tracker->value)->toBe(10);

        // Verify in database
        $roomTrackers = $room->fresh()->getCountdownTrackers();
        expect($roomTrackers)->not->toBeEmpty();
        expect(array_values($roomTrackers)[0]['name'])->toBe('Test Timer');
    });

    it('prioritizes campaign over room for fear tracking', function () {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
        $room = Room::factory()->forCampaign($campaign)->create(['creator_id' => $user->id]);

        // Set different fear levels
        $campaign->setFearLevel(5);
        $room->setFearLevel(3);
        $campaign->save();
        $room->save();

        $getGameStateAction = new \Domain\Room\Actions\GetGameStateAction;
        $gameState = $getGameStateAction->execute($room);

        // Should show campaign fear level, not room
        expect($gameState->fear_tracker->fear_level)->toBe(5);
        expect($gameState->source_type)->toBe('campaign');
    });

    it('handles countdown tracker modifications correctly', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $manageCountdownAction = new \Domain\Room\Actions\ManageCountdownTrackerAction;

        // Create initial tracker
        $tracker = $manageCountdownAction->createCountdownTracker(null, $room, 'Test Timer', 10);
        $trackerId = $tracker->id;

        // Increase countdown
        $updatedTracker = $manageCountdownAction->increaseCountdownTracker(null, $room, $trackerId, 1);
        expect($updatedTracker->value)->toBe(11);

        // Decrease countdown
        $updatedTracker = $manageCountdownAction->decreaseCountdownTracker(null, $room, $trackerId, 1);
        expect($updatedTracker->value)->toBe(10);

        // Delete countdown
        $deleted = $manageCountdownAction->deleteCountdownTracker(null, $room, $trackerId);
        expect($deleted)->toBeTrue();

        $roomTracker = $room->fresh()->getCountdownTracker($trackerId);
        expect($roomTracker)->toBeNull();
    });

    it('loads game state correctly for different room types', function () {
        $user = User::factory()->create();

        // Test standalone room
        $standaloneRoom = Room::factory()->create(['creator_id' => $user->id]);
        $standaloneRoom->setFearLevel(5);
        $standaloneRoom->save();

        $getGameStateAction = new \Domain\Room\Actions\GetGameStateAction;
        $gameState = $getGameStateAction->execute($standaloneRoom);

        expect($gameState->fear_tracker->fear_level)->toBe(5);
        expect($gameState->source_type)->toBe('room');

        // Test campaign room
        $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
        $campaign->setFearLevel(10);
        $campaign->save();

        $campaignRoom = Room::factory()->forCampaign($campaign)->create(['creator_id' => $user->id]);

        $gameState = $getGameStateAction->execute($campaignRoom);

        expect($gameState->fear_tracker->fear_level)->toBe(10);
        expect($gameState->source_type)->toBe('campaign');
    });
});
