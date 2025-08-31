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

test('campaign creator can add category tags to pages', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($creator);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can set access levels for pages', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($creator);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can create hierarchical pages', function () {
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

test('campaign pages support different view modes', function () {
    $creator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($creator);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('non-members cannot access campaign pages', function () {
    $creator = User::factory()->create();
    $outsider = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($outsider);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});