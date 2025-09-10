<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

uses()->group('browser');

it('displays modern campaign show page design', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    visit("/campaigns/{$campaign->campaign_code}")
        ->assertSee($campaign->name)
        ->assertSee($campaign->description)
        ->assertSee('Campaign Pages')
        ->assertSee('Campaign Rooms')
        ->assertSee('Campaign Members');
});

it('shows campaign creator correctly', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($creator);

    visit("/campaigns/{$campaign->campaign_code}")
        ->assertSee($creator->username); // Creator's username should be visible in campaign info
});
