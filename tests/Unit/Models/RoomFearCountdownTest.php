<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Room Fear Level Management', function () {
    it('can increase fear level', function () {
        $room = Room::factory()->create();

        $newLevel = $room->increaseFear(3);

        expect($newLevel)->toBe(3);
        expect($room->getFearLevel())->toBe(3);
    });

    it('can decrease fear level', function () {
        $room = Room::factory()->create();
        $room->setFearLevel(5);

        $newLevel = $room->decreaseFear(2);

        expect($newLevel)->toBe(3);
        expect($room->getFearLevel())->toBe(3);
    });

    it('cannot set fear level below 0', function () {
        $room = Room::factory()->create();

        $room->setFearLevel(-5);

        expect($room->getFearLevel())->toBe(0);
    });

    it('cannot set fear level above 255', function () {
        $room = Room::factory()->create();

        $room->setFearLevel(300);

        expect($room->getFearLevel())->toBe(255);
    });

    it('cannot decrease fear below 0', function () {
        $room = Room::factory()->create();
        $room->setFearLevel(2);

        $newLevel = $room->decreaseFear(5);

        expect($newLevel)->toBe(0);
        expect($room->getFearLevel())->toBe(0);
    });
});

describe('Room Countdown Tracker Management', function () {
    it('can add a countdown tracker', function () {
        $room = Room::factory()->create();

        $room->setCountdownTracker('test-id', 'Test Timer', 10);

        $tracker = $room->getCountdownTracker('test-id');
        expect($tracker)->toBeArray();
        expect($tracker['name'])->toBe('Test Timer');
        expect($tracker['value'])->toBe(10);
        expect($tracker['updated_at'])->toBeString();
    });

    it('can update an existing countdown tracker', function () {
        $room = Room::factory()->create();
        $room->setCountdownTracker('test-id', 'Test Timer', 10);

        $room->setCountdownTracker('test-id', 'Updated Timer', 5);

        $tracker = $room->getCountdownTracker('test-id');
        expect($tracker['name'])->toBe('Updated Timer');
        expect($tracker['value'])->toBe(5);
    });

    it('can remove a countdown tracker', function () {
        $room = Room::factory()->create();
        $room->setCountdownTracker('test-id', 'Test Timer', 10);

        $room->removeCountdownTracker('test-id');

        $tracker = $room->getCountdownTracker('test-id');
        expect($tracker)->toBeNull();
    });

    it('cannot set negative countdown values', function () {
        $room = Room::factory()->create();

        $room->setCountdownTracker('test-id', 'Test Timer', -5);

        $tracker = $room->getCountdownTracker('test-id');
        expect($tracker['value'])->toBe(0);
    });

    it('can retrieve all countdown trackers', function () {
        $room = Room::factory()->create();
        $room->setCountdownTracker('timer1', 'First Timer', 10);
        $room->setCountdownTracker('timer2', 'Second Timer', 20);

        $trackers = $room->getCountdownTrackers();

        expect($trackers)->toBeArray();
        expect($trackers)->toHaveCount(2);
        expect($trackers['timer1']['name'])->toBe('First Timer');
        expect($trackers['timer2']['name'])->toBe('Second Timer');
    });

    it('returns empty array when no countdown trackers exist', function () {
        $room = Room::factory()->create();

        $trackers = $room->getCountdownTrackers();

        expect($trackers)->toBeArray();
        expect($trackers)->toBeEmpty();
    });
});
