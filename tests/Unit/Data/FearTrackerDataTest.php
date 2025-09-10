<?php

declare(strict_types=1);

use Domain\Campaign\Data\FearTrackerData as CampaignFearTrackerData;
use Domain\Campaign\Models\Campaign;
use Domain\Room\Data\FearTrackerData as RoomFearTrackerData;
use Domain\Room\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign FearTrackerData', function () {
    it('can be created with basic properties', function () {
        $data = new CampaignFearTrackerData(fear_level: 5);

        expect($data->fear_level)->toBe(5);
        expect($data->max_fear_level)->toBe(255);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeTrue();
    });

    it('enforces minimum fear level of 0', function () {
        $data = new CampaignFearTrackerData(fear_level: -5);

        expect($data->fear_level)->toBe(0);
        expect($data->can_decrease)->toBeFalse();
    });

    it('enforces maximum fear level', function () {
        $data = new CampaignFearTrackerData(fear_level: 300);

        expect($data->fear_level)->toBe(255);
        expect($data->can_increase)->toBeFalse();
    });

    it('can be created from campaign model', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(10);

        $data = CampaignFearTrackerData::fromCampaign($campaign);

        expect($data->fear_level)->toBe(10);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeTrue();
    });

    it('can create default instance', function () {
        $data = CampaignFearTrackerData::default();

        expect($data->fear_level)->toBe(0);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeFalse();
    });
});

describe('Room FearTrackerData', function () {
    it('can be created with basic properties', function () {
        $data = new RoomFearTrackerData(fear_level: 5);

        expect($data->fear_level)->toBe(5);
        expect($data->max_fear_level)->toBe(255);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeTrue();
    });

    it('can be created from room model', function () {
        $room = Room::factory()->create();
        $room->setFearLevel(15);

        $data = RoomFearTrackerData::fromRoom($room);

        expect($data->fear_level)->toBe(15);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeTrue();
    });

    it('can create default instance', function () {
        $data = RoomFearTrackerData::default();

        expect($data->fear_level)->toBe(0);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeFalse();
    });
});
