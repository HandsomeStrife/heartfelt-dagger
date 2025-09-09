<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('adds fear_level column to campaigns table', function () {
    expect(Schema::hasColumn('campaigns', 'fear_level'))->toBeTrue();
});

it('adds countdown_trackers column to campaigns table', function () {
    expect(Schema::hasColumn('campaigns', 'countdown_trackers'))->toBeTrue();
});

it('adds fear_level column to rooms table', function () {
    expect(Schema::hasColumn('rooms', 'fear_level'))->toBeTrue();
});

it('adds countdown_trackers column to rooms table', function () {
    expect(Schema::hasColumn('rooms', 'countdown_trackers'))->toBeTrue();
});

it('sets default fear_level to 0 for campaigns', function () {
    $campaign = \Domain\Campaign\Models\Campaign::factory()->create();
    
    expect($campaign->getFearLevel())->toBe(0);
});

it('sets default fear_level to 0 for rooms', function () {
    $room = \Domain\Room\Models\Room::factory()->create();
    
    expect($room->getFearLevel())->toBe(0);
});

it('allows null countdown_trackers for campaigns', function () {
    $campaign = \Domain\Campaign\Models\Campaign::factory()->create();
    
    expect($campaign->countdown_trackers)->toBeNull();
});

it('allows null countdown_trackers for rooms', function () {
    $room = \Domain\Room\Models\Room::factory()->create();
    
    expect($room->countdown_trackers)->toBeNull();
});
