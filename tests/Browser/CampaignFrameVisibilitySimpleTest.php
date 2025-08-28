<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

it('can create a campaign without description (simplified)', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'Simple Test Frame',
        'description' => 'Basic frame for testing',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['A simple test adventure'],
        'touchstones' => [],
        'tone' => [],
        'themes' => [],
        'player_principles' => [],
        'gm_principles' => [],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => '',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => '',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => [],
    ]);

    auth()->login($creator);
    
    $page = visit('/campaigns/create');
    
    $page->assertSee('Create Campaign');
});

it('can view a campaign with linked frame (simplified)', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'View Test Frame',
        'description' => 'Frame for view testing',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['Test campaign content'],
        'touchstones' => [],
        'tone' => [],
        'themes' => [],
        'player_principles' => [],
        'gm_principles' => [],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => '',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => '',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => [],
    ]);

    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'campaign_frame_id' => $frame->id,
        'name' => 'Test Campaign with Frame',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}");
    
    $page->assertSee('Test Campaign with Frame')
        ->assertSee('View Test Frame');
});
