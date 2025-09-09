<?php

declare(strict_types=1);

use Domain\Campaign\Actions\ManageCountdownTrackerAction as CampaignManageCountdownTrackerAction;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Actions\ManageCountdownTrackerAction as RoomManageCountdownTrackerAction;
use Domain\Room\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign ManageCountdownTrackerAction', function () {
    it('can create a countdown tracker for campaign', function () {
        $campaign = Campaign::factory()->create();
        $action = new CampaignManageCountdownTrackerAction();
        
        $result = $action->createCountdownTracker($campaign, null, 'Test Timer', 10);
        
        expect($result->name)->toBe('Test Timer');
        expect($result->value)->toBe(10);
        expect($result->id)->toBeString();
        
        $campaignTracker = $campaign->fresh()->getCountdownTracker($result->id);
        expect($campaignTracker)->not->toBeNull();
        expect($campaignTracker['name'])->toBe('Test Timer');
        expect($campaignTracker['value'])->toBe(10);
    });

    it('can update an existing countdown tracker', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-id', 'Original Timer', 5);
        $campaign->save();
        
        $action = new CampaignManageCountdownTrackerAction();
        $result = $action->updateCountdownTracker($campaign, null, 'test-id', 'Updated Timer', 15);
        
        expect($result->name)->toBe('Updated Timer');
        expect($result->value)->toBe(15);
        
        $campaignTracker = $campaign->fresh()->getCountdownTracker('test-id');
        expect($campaignTracker['name'])->toBe('Updated Timer');
        expect($campaignTracker['value'])->toBe(15);
    });

    it('can increase countdown tracker value', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-id', 'Test Timer', 5);
        $campaign->save();
        
        $action = new CampaignManageCountdownTrackerAction();
        $result = $action->increaseCountdownTracker($campaign, null, 'test-id', 3);
        
        expect($result->value)->toBe(8);
        
        $campaignTracker = $campaign->fresh()->getCountdownTracker('test-id');
        expect($campaignTracker['value'])->toBe(8);
    });

    it('can decrease countdown tracker value', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-id', 'Test Timer', 10);
        $campaign->save();
        
        $action = new CampaignManageCountdownTrackerAction();
        $result = $action->decreaseCountdownTracker($campaign, null, 'test-id', 3);
        
        expect($result->value)->toBe(7);
        
        $campaignTracker = $campaign->fresh()->getCountdownTracker('test-id');
        expect($campaignTracker['value'])->toBe(7);
    });

    it('cannot decrease countdown tracker below zero', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-id', 'Test Timer', 2);
        $campaign->save();
        
        $action = new CampaignManageCountdownTrackerAction();
        $result = $action->decreaseCountdownTracker($campaign, null, 'test-id', 5);
        
        expect($result->value)->toBe(0);
        
        $campaignTracker = $campaign->fresh()->getCountdownTracker('test-id');
        expect($campaignTracker['value'])->toBe(0);
    });

    it('can delete a countdown tracker', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-id', 'Test Timer', 5);
        $campaign->save();
        
        $action = new CampaignManageCountdownTrackerAction();
        $result = $action->deleteCountdownTracker($campaign, null, 'test-id');
        
        expect($result)->toBeTrue();
        
        $campaignTracker = $campaign->fresh()->getCountdownTracker('test-id');
        expect($campaignTracker)->toBeNull();
    });

    it('returns false when deleting non-existent tracker', function () {
        $campaign = Campaign::factory()->create();
        $action = new CampaignManageCountdownTrackerAction();
        
        $result = $action->deleteCountdownTracker($campaign, null, 'non-existent');
        
        expect($result)->toBeFalse();
    });

    it('can get all countdown trackers', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('timer1', 'First Timer', 10);
        $campaign->setCountdownTracker('timer2', 'Second Timer', 20);
        $campaign->save();
        
        $action = new CampaignManageCountdownTrackerAction();
        $trackers = $action->getCountdownTrackers($campaign, null);
        
        expect($trackers)->toHaveCount(2);
        expect($trackers->pluck('name')->toArray())->toContain('First Timer', 'Second Timer');
        expect($trackers->pluck('value')->toArray())->toContain(10, 20);
    });

    it('throws exception when updating non-existent tracker', function () {
        $campaign = Campaign::factory()->create();
        $action = new CampaignManageCountdownTrackerAction();
        
        expect(fn() => $action->updateCountdownTracker($campaign, null, 'non-existent', 'New Name', 5))
            ->toThrow(\InvalidArgumentException::class, "Countdown tracker with ID 'non-existent' not found");
    });

    it('throws exception when increasing non-existent tracker', function () {
        $campaign = Campaign::factory()->create();
        $action = new CampaignManageCountdownTrackerAction();
        
        expect(fn() => $action->increaseCountdownTracker($campaign, null, 'non-existent'))
            ->toThrow(\InvalidArgumentException::class, "Countdown tracker with ID 'non-existent' not found");
    });

    it('works with standalone rooms', function () {
        $room = Room::factory()->create(); // No campaign
        $action = new CampaignManageCountdownTrackerAction();
        
        $result = $action->createCountdownTracker(null, $room, 'Room Timer', 15);
        
        expect($result->name)->toBe('Room Timer');
        expect($result->value)->toBe(15);
        
        $roomTracker = $room->fresh()->getCountdownTracker($result->id);
        expect($roomTracker)->not->toBeNull();
        expect($roomTracker['name'])->toBe('Room Timer');
    });

    it('throws exception when room has campaign but campaign not provided', function () {
        $campaign = Campaign::factory()->create();
        $room = Room::factory()->forCampaign($campaign)->create();
        $action = new CampaignManageCountdownTrackerAction();
        
        expect(fn() => $action->createCountdownTracker(null, $room, 'Timer', 5))
            ->toThrow(\InvalidArgumentException::class, 'Room has an associated campaign, use campaign for countdown tracking');
    });
});

describe('Room ManageCountdownTrackerAction', function () {
    it('has same functionality as campaign version', function () {
        $room = Room::factory()->create();
        $action = new RoomManageCountdownTrackerAction();
        
        $result = $action->createCountdownTracker(null, $room, 'Room Timer', 8);
        
        expect($result->name)->toBe('Room Timer');
        expect($result->value)->toBe(8);
    });
});
