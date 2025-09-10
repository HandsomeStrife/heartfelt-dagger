<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Campaign Fear Level Management', function () {
    it('can increase fear level', function () {
        $campaign = Campaign::factory()->create();

        $newLevel = $campaign->increaseFear(3);

        expect($newLevel)->toBe(3);
        expect($campaign->getFearLevel())->toBe(3);
    });

    it('can decrease fear level', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(5);

        $newLevel = $campaign->decreaseFear(2);

        expect($newLevel)->toBe(3);
        expect($campaign->getFearLevel())->toBe(3);
    });

    it('cannot set fear level below 0', function () {
        $campaign = Campaign::factory()->create();

        $campaign->setFearLevel(-5);

        expect($campaign->getFearLevel())->toBe(0);
    });

    it('cannot set fear level above 255', function () {
        $campaign = Campaign::factory()->create();

        $campaign->setFearLevel(300);

        expect($campaign->getFearLevel())->toBe(255);
    });

    it('cannot decrease fear below 0', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setFearLevel(2);

        $newLevel = $campaign->decreaseFear(5);

        expect($newLevel)->toBe(0);
        expect($campaign->getFearLevel())->toBe(0);
    });
});

describe('Campaign Countdown Tracker Management', function () {
    it('can add a countdown tracker', function () {
        $campaign = Campaign::factory()->create();

        $campaign->setCountdownTracker('test-id', 'Test Timer', 10);

        $tracker = $campaign->getCountdownTracker('test-id');
        expect($tracker)->toBeArray();
        expect($tracker['name'])->toBe('Test Timer');
        expect($tracker['value'])->toBe(10);
        expect($tracker['updated_at'])->toBeString();
    });

    it('can update an existing countdown tracker', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-id', 'Test Timer', 10);

        $campaign->setCountdownTracker('test-id', 'Updated Timer', 5);

        $tracker = $campaign->getCountdownTracker('test-id');
        expect($tracker['name'])->toBe('Updated Timer');
        expect($tracker['value'])->toBe(5);
    });

    it('can remove a countdown tracker', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('test-id', 'Test Timer', 10);

        $campaign->removeCountdownTracker('test-id');

        $tracker = $campaign->getCountdownTracker('test-id');
        expect($tracker)->toBeNull();
    });

    it('cannot set negative countdown values', function () {
        $campaign = Campaign::factory()->create();

        $campaign->setCountdownTracker('test-id', 'Test Timer', -5);

        $tracker = $campaign->getCountdownTracker('test-id');
        expect($tracker['value'])->toBe(0);
    });

    it('can retrieve all countdown trackers', function () {
        $campaign = Campaign::factory()->create();
        $campaign->setCountdownTracker('timer1', 'First Timer', 10);
        $campaign->setCountdownTracker('timer2', 'Second Timer', 20);

        $trackers = $campaign->getCountdownTrackers();

        expect($trackers)->toBeArray();
        expect($trackers)->toHaveCount(2);
        expect($trackers['timer1']['name'])->toBe('First Timer');
        expect($trackers['timer2']['name'])->toBe('Second Timer');
    });

    it('returns empty array when no countdown trackers exist', function () {
        $campaign = Campaign::factory()->create();

        $trackers = $campaign->getCountdownTrackers();

        expect($trackers)->toBeArray();
        expect($trackers)->toBeEmpty();
    });
});
