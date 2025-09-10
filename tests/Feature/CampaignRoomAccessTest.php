<?php

declare(strict_types=1);

use Domain\Room\Models\Room;

use function Pest\Laravel\get;

test('debug campaign room access for anonymous user', function () {
    $campaign = \Domain\Campaign\Models\Campaign::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'campaign_id' => $campaign->id,
    ]);

    $response = get("/rooms/join/{$room->invite_code}");

    // Let's see what happens
    if ($response->status() === 302) {
        echo "\nRedirect URL: ".$response->headers->get('Location');
        echo "\nSession errors: ".json_encode(session()->get('errors'));
        echo "\nSession flash: ".json_encode(session()->all());
    }

    // Just make a passing assertion for now
    expect(true)->toBeTrue();
});
