<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('campaign frames page loads correctly', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaign-frames');
    
    $page->assertSee('Campaign Frames');
});

test('campaign creation form loads with campaign frame options', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaigns/create');
    
    $page->assertSee('Create Campaign')->assertSee('Campaign Name');
});

test('campaign with frame shows frame information', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Test Frame',
        'description' => 'A test frame',
    ]);

    $campaign = Campaign::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Test Campaign',
        'campaign_frame_id' => $frame->id,
    ]);
    
    actingAs($user);
    $page = visit("/campaigns/{$campaign->campaign_code}");
    
    $page->assertSee('Test Campaign');
});

test('campaign frame list displays correctly', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Test Frame',
        'is_public' => true,
    ]);
    
    actingAs($user);
    $page = visit("/campaign-frames");
    
    $page->assertSee('Campaign Frames');
});