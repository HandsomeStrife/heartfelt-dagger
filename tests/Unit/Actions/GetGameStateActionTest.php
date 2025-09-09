<?php

declare(strict_types=1);

use Domain\Campaign\Actions\GetGameStateAction as CampaignGetGameStateAction;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Actions\GetGameStateAction as RoomGetGameStateAction;
use Domain\Room\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign GetGameStateAction', function () {
    it('gets game state from campaign when room belongs to campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(5);
        $campaign->setCountdownTracker('timer1', 'Campaign Timer', 10);
        $campaign->save();
        
        $room = Room::factory()->forCampaign($campaign)->create();
        $room->setFearLevel(3); // This should be ignored
        $room->save();
        
        $action = new CampaignGetGameStateAction();
        $gameState = $action->execute($room);
        
        expect($gameState->fear_tracker->fear_level)->toBe(5); // From campaign, not room
        expect($gameState->countdown_trackers)->toHaveCount(1);
        expect($gameState->source_type)->toBe('campaign');
        expect($gameState->source_id)->toBe($campaign->id);
    });

    it('gets game state from room when room has no campaign', function () {
        $room = Room::factory()->create(); // No campaign
        $room->setFearLevel(8);
        $room->setCountdownTracker('timer1', 'Room Timer', 15);
        $room->save();
        
        $action = new CampaignGetGameStateAction();
        $gameState = $action->execute($room);
        
        expect($gameState->fear_tracker->fear_level)->toBe(8);
        expect($gameState->countdown_trackers)->toHaveCount(1);
        expect($gameState->source_type)->toBe('room');
        expect($gameState->source_id)->toBe($room->id);
    });

    it('can get game state specifically for campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(12);
        $campaign->save();
        
        $action = new CampaignGetGameStateAction();
        $gameState = $action->executeForCampaign($campaign);
        
        expect($gameState->fear_tracker->fear_level)->toBe(12);
        expect($gameState->source_type)->toBe('campaign');
        expect($gameState->source_id)->toBe($campaign->id);
    });

    it('can determine correct game state source', function () {
        $campaign = Campaign::factory()->create();
        $room = Room::factory()->forCampaign($campaign)->create();
        
        $action = new CampaignGetGameStateAction();
        $source = $action->getGameStateSource($room);
        
        expect($source['type'])->toBe('campaign');
        expect($source['id'])->toBe($campaign->id);
        expect($source['model'])->toBeInstanceOf(Campaign::class);
        expect($source['model']->id)->toBe($campaign->id);
    });

    it('determines room as source when no campaign', function () {
        $room = Room::factory()->create();
        
        $action = new CampaignGetGameStateAction();
        $source = $action->getGameStateSource($room);
        
        expect($source['type'])->toBe('room');
        expect($source['id'])->toBe($room->id);
        expect($source['model'])->toBe($room);
    });
});

describe('Room GetGameStateAction', function () {
    it('has same functionality as campaign version', function () {
        $room = Room::factory()->create();
        $room->setFearLevel(6);
        $room->save();
        
        $action = new RoomGetGameStateAction();
        $gameState = $action->execute($room);
        
        expect($gameState->fear_tracker->fear_level)->toBe(6);
        expect($gameState->source_type)->toBe('room');
    });

    it('can get game state specifically for room regardless of campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(20);
        $campaign->save();
        
        $room = Room::factory()->forCampaign($campaign)->create();
        $room->setFearLevel(10);
        $room->save();
        
        $action = new RoomGetGameStateAction();
        $gameState = $action->executeForRoom($room);
        
        expect($gameState->fear_tracker->fear_level)->toBe(10); // Room's own data
        expect($gameState->source_type)->toBe('room');
        expect($gameState->source_id)->toBe($room->id);
    });
});
