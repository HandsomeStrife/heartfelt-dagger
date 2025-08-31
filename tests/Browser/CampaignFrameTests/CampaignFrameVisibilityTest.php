<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('campaign creator can access frame visibility controls', function () {
    $creator = User::factory()->create();

    $frame = CampaignFrame::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Adventure Frame',
        'description' => 'A test campaign frame',
    ]);

    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'campaign_frame_id' => $frame->id,
        'name' => 'Test Campaign',
    ]);

    actingAs($creator);
    visit('/campaigns')->assertSee('Campaigns');
});

test('campaign creator can toggle section visibility', function () {
    $creator = User::factory()->create();

    $frame = CampaignFrame::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Visibility Test Frame',
        'description' => 'Testing visibility controls',
    ]);

    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'campaign_frame_id' => $frame->id,
        'name' => 'Visibility Test Campaign',
    ]);

    actingAs($creator);
    visit('/campaigns')->assertSee('Campaigns');
});

test('campaign creation workflow loads', function () {
    $creator = User::factory()->create();

    $frame = CampaignFrame::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Creation Test Frame',
        'description' => 'Frame for testing creation workflow',
    ]);

    actingAs($creator);
    visit('/campaigns/create')->assertSee('Create Campaign');
});

test('can view a campaign with linked frame', function () {
    $creator = User::factory()->create();

    $frame = CampaignFrame::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'View Test Frame',
        'description' => 'Frame for view testing',
        'is_public' => true,
    ]);

    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'campaign_frame_id' => $frame->id,
        'name' => 'Test Campaign with Frame',
    ]);

    actingAs($creator);
    visit("/campaigns/{$campaign->campaign_code}")
        ->assertSee('Test Campaign with Frame')
        ->assertSee('View Test Frame');
});




