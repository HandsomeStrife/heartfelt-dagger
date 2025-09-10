<?php

declare(strict_types=1);

use Carbon\Carbon;
use Domain\Campaign\Data\CountdownTrackerData as CampaignCountdownTrackerData;
use Domain\Room\Data\CountdownTrackerData as RoomCountdownTrackerData;

describe('Campaign CountdownTrackerData', function () {
    it('can be created with basic properties', function () {
        $now = Carbon::now();
        $data = new CampaignCountdownTrackerData(
            id: 'test-id',
            name: 'Test Timer',
            value: 10,
            updated_at: $now
        );

        expect($data->id)->toBe('test-id');
        expect($data->name)->toBe('Test Timer');
        expect($data->value)->toBe(10);
        expect($data->updated_at)->toEqual($now);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeTrue();
    });

    it('enforces minimum value of 0', function () {
        $data = new CampaignCountdownTrackerData(
            id: 'test-id',
            name: 'Test Timer',
            value: -5,
            updated_at: Carbon::now()
        );

        expect($data->value)->toBe(0);
        expect($data->can_decrease)->toBeFalse();
    });

    it('can be created from array data', function () {
        $arrayData = [
            'name' => 'Test Timer',
            'value' => 15,
            'updated_at' => '2024-09-08T10:00:00Z',
        ];

        $data = CampaignCountdownTrackerData::fromArray('test-id', $arrayData);

        expect($data->id)->toBe('test-id');
        expect($data->name)->toBe('Test Timer');
        expect($data->value)->toBe(15);
        expect($data->updated_at)->toBeInstanceOf(Carbon::class);
    });

    it('can create new instance', function () {
        $data = CampaignCountdownTrackerData::create('new-id', 'New Timer', 20);

        expect($data->id)->toBe('new-id');
        expect($data->name)->toBe('New Timer');
        expect($data->value)->toBe(20);
        expect($data->updated_at)->toBeInstanceOf(Carbon::class);
    });

    it('can convert to storage array', function () {
        $now = Carbon::now();
        $data = new CampaignCountdownTrackerData(
            id: 'test-id',
            name: 'Test Timer',
            value: 10,
            updated_at: $now
        );

        $storageArray = $data->toStorageArray();

        expect($storageArray)->toBeArray();
        expect($storageArray['name'])->toBe('Test Timer');
        expect($storageArray['value'])->toBe(10);
        expect($storageArray['updated_at'])->toBe($now->toISOString());
    });
});

describe('Room CountdownTrackerData', function () {
    it('has same functionality as campaign version', function () {
        $data = RoomCountdownTrackerData::create('room-timer', 'Room Timer', 5);

        expect($data->id)->toBe('room-timer');
        expect($data->name)->toBe('Room Timer');
        expect($data->value)->toBe(5);
        expect($data->can_increase)->toBeTrue();
        expect($data->can_decrease)->toBeTrue();
    });
});
