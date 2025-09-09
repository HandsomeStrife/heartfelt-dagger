<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Repositories\GameStateRepository as CampaignGameStateRepository;
use Domain\Room\Models\Room;
use Domain\Room\Repositories\GameStateRepository as RoomGameStateRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign GameStateRepository', function () {
    it('gets game state from campaign when room belongs to campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(10);
        $campaign->setCountdownTracker('timer1', 'Campaign Timer', 15);
        $campaign->save();
        
        $room = Room::factory()->forCampaign($campaign)->create();
        $room->setFearLevel(5); // Should be ignored
        $room->save();
        
        $repository = new CampaignGameStateRepository();
        $gameState = $repository->getGameStateForRoom($room);
        
        expect($gameState->fear_tracker->fear_level)->toBe(10); // From campaign
        expect($gameState->countdown_trackers)->toHaveCount(1);
        expect($gameState->source_type)->toBe('campaign');
    });

    it('gets game state from room when no campaign', function () {
        $room = Room::factory()->create();
        $room->setFearLevel(8);
        $room->setCountdownTracker('timer1', 'Room Timer', 12);
        $room->save();
        
        $repository = new CampaignGameStateRepository();
        $gameState = $repository->getGameStateForRoom($room);
        
        expect($gameState->fear_tracker->fear_level)->toBe(8);
        expect($gameState->countdown_trackers)->toHaveCount(1);
        expect($gameState->source_type)->toBe('room');
    });

    it('gets fear tracker for room prioritizing campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(15);
        $campaign->save();
        
        $room = Room::factory()->forCampaign($campaign)->create();
        $room->setFearLevel(3);
        $room->save();
        
        $repository = new CampaignGameStateRepository();
        $fearTracker = $repository->getFearTrackerForRoom($room);
        
        expect($fearTracker->fear_level)->toBe(15); // From campaign
    });

    it('gets countdown trackers for room prioritizing campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('timer1', 'Campaign Timer', 10);
        $campaign->setCountdownTracker('timer2', 'Another Timer', 20);
        $campaign->save();
        
        $room = Room::factory()->forCampaign($campaign)->create();
        $room->setCountdownTracker('timer3', 'Room Timer', 5);
        $room->save();
        
        $repository = new CampaignGameStateRepository();
        $trackers = $repository->getCountdownTrackersForRoom($room);
        
        expect($trackers)->toHaveCount(2); // Only campaign trackers
        expect($trackers->pluck('name')->toArray())->toContain('Campaign Timer', 'Another Timer');
        expect($trackers->pluck('name')->toArray())->not->toContain('Room Timer');
    });

    it('finds specific countdown tracker for room', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-timer', 'Test Timer', 25);
        $campaign->save();
        
        $room = Room::factory()->forCampaign($campaign)->create();
        
        $repository = new CampaignGameStateRepository();
        $tracker = $repository->findCountdownTrackerForRoom($room, 'test-timer');
        
        expect($tracker)->not->toBeNull();
        expect($tracker->name)->toBe('Test Timer');
        expect($tracker->value)->toBe(25);
    });

    it('returns null for non-existent countdown tracker', function () {
        $room = Room::factory()->create();
        
        $repository = new CampaignGameStateRepository();
        $tracker = $repository->findCountdownTrackerForRoom($room, 'non-existent');
        
        expect($tracker)->toBeNull();
    });

    it('checks if room has active countdown trackers', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('timer1', 'Timer', 5);
        $campaign->save();
        
        $room = Room::factory()->forCampaign($campaign)->create();
        
        $repository = new CampaignGameStateRepository();
        
        expect($repository->hasActiveCountdownTrackers($room))->toBeTrue();
    });

    it('returns false when no active countdown trackers', function () {
        $room = Room::factory()->create();
        
        $repository = new CampaignGameStateRepository();
        
        expect($repository->hasActiveCountdownTrackers($room))->toBeFalse();
    });

    it('determines correct game state source', function () {
        $campaign = Campaign::factory()->create();
        $room = Room::factory()->forCampaign($campaign)->create();
        
        $repository = new CampaignGameStateRepository();
        $source = $repository->getGameStateSource($room);
        
        expect($source['type'])->toBe('campaign');
        expect($source['id'])->toBe($campaign->id);
        expect($source['model'])->toBeInstanceOf(Campaign::class);
    });

    it('gets game state specifically for campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(18);
        $campaign->save();
        
        $repository = new CampaignGameStateRepository();
        $gameState = $repository->getGameStateForCampaign($campaign);
        
        expect($gameState->fear_tracker->fear_level)->toBe(18);
        expect($gameState->source_type)->toBe('campaign');
    });

    it('gets countdown trackers specifically for campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('timer1', 'Timer 1', 5);
        $campaign->setCountdownTracker('timer2', 'Timer 2', 10);
        $campaign->save();
        
        $repository = new CampaignGameStateRepository();
        $trackers = $repository->getCountdownTrackersForCampaign($campaign);
        
        expect($trackers)->toHaveCount(2);
        expect($trackers->pluck('name')->toArray())->toContain('Timer 1', 'Timer 2');
    });
});

describe('Room GameStateRepository', function () {
    it('has same functionality as campaign version', function () {
        $room = Room::factory()->create();
        $room->setFearLevel(12);
        $room->save();
        
        $repository = new RoomGameStateRepository();
        $gameState = $repository->getGameStateForRoom($room);
        
        expect($gameState->fear_tracker->fear_level)->toBe(12);
        expect($gameState->source_type)->toBe('room');
    });
});
