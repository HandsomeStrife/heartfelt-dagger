<?php

declare(strict_types=1);

use Domain\Campaign\Data\GameStateData as CampaignGameStateData;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Data\GameStateData as RoomGameStateData;
use Domain\Room\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign GameStateData', function () {
    it('can be created from campaign with fear and countdown data', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(5);
        $campaign->setCountdownTracker('timer1', 'First Timer', 10);
        $campaign->setCountdownTracker('timer2', 'Second Timer', 20);
        $campaign->save();

        $gameState = CampaignGameStateData::fromCampaign($campaign);

        expect($gameState->fear_tracker->fear_level)->toBe(5);
        expect($gameState->countdown_trackers)->toHaveCount(2);
        expect($gameState->source_type)->toBe('campaign');
        expect($gameState->source_id)->toBe($campaign->id);

        $timer1 = $gameState->getCountdownTracker('timer1');
        expect($timer1)->not->toBeNull();
        expect($timer1->name)->toBe('First Timer');
        expect($timer1->value)->toBe(10);
    });

    it('can get specific countdown tracker', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-timer', 'Test Timer', 15);
        $campaign->save();

        $gameState = CampaignGameStateData::fromCampaign($campaign);

        expect($gameState->hasCountdownTracker('test-timer'))->toBeTrue();
        expect($gameState->hasCountdownTracker('nonexistent'))->toBeFalse();

        $timer = $gameState->getCountdownTracker('test-timer');
        expect($timer)->not->toBeNull();
        expect($timer->name)->toBe('Test Timer');
    });

    it('can create default empty state', function () {
        $gameState = CampaignGameStateData::default('campaign', 123);

        expect($gameState->fear_tracker->fear_level)->toBe(0);
        expect($gameState->countdown_trackers)->toBeEmpty();
        expect($gameState->source_type)->toBe('campaign');
        expect($gameState->source_id)->toBe(123);
    });

    it('can count countdown trackers', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('timer1', 'First Timer', 10);
        $campaign->setCountdownTracker('timer2', 'Second Timer', 20);
        $campaign->setCountdownTracker('timer3', 'Third Timer', 30);
        $campaign->save();

        $gameState = CampaignGameStateData::fromCampaign($campaign);

        expect($gameState->getCountdownTrackersCount())->toBe(3);
    });
});

describe('Room GameStateData', function () {
    it('can be created from room with fear and countdown data', function () {
        $room = Room::factory()->create();
        $room->setFearLevel(8);
        $room->setCountdownTracker('room-timer', 'Room Timer', 5);
        $room->save();

        $gameState = RoomGameStateData::fromRoom($room);

        expect($gameState->fear_tracker->fear_level)->toBe(8);
        expect($gameState->countdown_trackers)->toHaveCount(1);
        expect($gameState->source_type)->toBe('room');
        expect($gameState->source_id)->toBe($room->id);

        $timer = $gameState->getCountdownTracker('room-timer');
        expect($timer)->not->toBeNull();
        expect($timer->name)->toBe('Room Timer');
        expect($timer->value)->toBe(5);
    });

    it('handles empty room state correctly', function () {
        $room = Room::factory()->create();

        $gameState = RoomGameStateData::fromRoom($room);

        expect($gameState->fear_tracker->fear_level)->toBe(0);
        expect($gameState->countdown_trackers)->toBeEmpty();
        expect($gameState->getCountdownTrackersCount())->toBe(0);
    });
});
