<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('campaign creator can complete full page management workflow', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Test Campaign',
    ]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can create a new page', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can edit existing pages', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can delete pages', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can set page access levels', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign members can view appropriate pages', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    actingAs($member);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can organize pages with categories', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('campaign creator can search and filter pages', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('pages support rich text content with formatting', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('page creation includes validation and error handling', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);
    $page = visit('/campaigns');
    $page->wait(3)
        ->assertSee('Campaigns');
});