<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('campaign pages has minimal compact navigation bar', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Navigation Test Campaign',
    ]);

    actingAs($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Navigation Test Campaign') // Small campaign name
        ->assertSee('Overview') // Tab navigation
        ->assertSee('Pages') // Current active tab
        ->assertDontSee('Campaign Pages & Lore'); // Old large subtitle removed
});

test('campaign pages navigation includes frame tab when campaign has frame', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'Test Frame',
        'description' => 'A test frame',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['Test content'],
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
        'name' => 'Frame Navigation Test',
    ]);

    actingAs($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Frame Navigation Test')
        ->assertSee('Overview')
        ->assertSee('Pages')
        ->assertSee('Frame'); // Frame tab should be present
});

test('compact navigation back button works', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Back Button Test',
    ]);

    actingAs($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Back Button Test')
        ->click('Overview') // Click back to overview
        ->wait(2)
        ->assertPathIs("/campaigns/{$campaign->campaign_code}")
        ->assertSee('Back Button Test'); // Should be on campaign overview page
});
