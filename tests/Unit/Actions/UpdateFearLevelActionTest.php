<?php

declare(strict_types=1);

use Domain\Campaign\Actions\UpdateFearLevelAction as CampaignUpdateFearLevelAction;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Actions\UpdateFearLevelAction as RoomUpdateFearLevelAction;
use Domain\Room\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign UpdateFearLevelAction', function () {
    it('can update fear level for campaign', function () {
        $campaign = Campaign::factory()->create();
        $action = new CampaignUpdateFearLevelAction();
        
        $result = $action->execute($campaign, null, 5);
        
        expect($result->fear_level)->toBe(5);
        expect($campaign->fresh()->getFearLevel())->toBe(5);
    });

    it('can increase fear level for campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(3);
        $campaign->save();
        
        $action = new CampaignUpdateFearLevelAction();
        $result = $action->increaseFear($campaign, null, 2);
        
        expect($result->fear_level)->toBe(5);
        expect($campaign->fresh()->getFearLevel())->toBe(5);
    });

    it('can decrease fear level for campaign', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(8);
        $campaign->save();
        
        $action = new CampaignUpdateFearLevelAction();
        $result = $action->decreaseFear($campaign, null, 3);
        
        expect($result->fear_level)->toBe(5);
        expect($campaign->fresh()->getFearLevel())->toBe(5);
    });

    it('can update fear level for standalone room', function () {
        $room = Room::factory()->create(); // No campaign
        $action = new CampaignUpdateFearLevelAction();
        
        $result = $action->execute(null, $room, 7);
        
        expect($result->fear_level)->toBe(7);
        expect($room->fresh()->getFearLevel())->toBe(7);
    });

    it('throws exception when neither campaign nor room provided', function () {
        $action = new CampaignUpdateFearLevelAction();
        
        expect(fn() => $action->execute(null, null, 5))
            ->toThrow(\InvalidArgumentException::class, 'Either campaign or room must be provided');
    });

    it('throws exception when room has campaign but campaign not provided', function () {
        $campaign = Campaign::factory()->create();
        $room = Room::factory()->forCampaign($campaign)->create();
        $action = new CampaignUpdateFearLevelAction();
        
        expect(fn() => $action->execute(null, $room, 5))
            ->toThrow(\InvalidArgumentException::class, 'Room has an associated campaign, use campaign for fear tracking');
    });

    it('prefers campaign over room when both provided', function () {
        $campaign = Campaign::factory()->create();
        $room = Room::factory()->forCampaign($campaign)->create();
        $action = new CampaignUpdateFearLevelAction();
        
        $result = $action->execute($campaign, $room, 10);
        
        expect($result->fear_level)->toBe(10);
        expect($campaign->fresh()->getFearLevel())->toBe(10);
        expect($room->fresh()->getFearLevel())->toBe(0); // Should not be updated
    });
});

describe('Room UpdateFearLevelAction', function () {
    it('has same functionality as campaign version', function () {
        $room = Room::factory()->create();
        $action = new RoomUpdateFearLevelAction();
        
        $result = $action->execute(null, $room, 6);
        
        expect($result->fear_level)->toBe(6);
        expect($room->fresh()->getFearLevel())->toBe(6);
    });
});
