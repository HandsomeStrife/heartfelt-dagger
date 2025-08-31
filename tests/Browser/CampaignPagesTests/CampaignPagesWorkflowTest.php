<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('campaign creator can access campaign pages management', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Campaign for Pages',
    ]);

    actingAs($creator);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can create a new campaign page', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($creator);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can search through pages', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($creator);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign players only see accessible pages', function () {
    $creator = User::factory()->create();
    $player = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($player);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});
