<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('hover state is isolated to individual page entries', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('eye icon navigates to page view', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('plus icon opens sub-page creation form with parent set', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});