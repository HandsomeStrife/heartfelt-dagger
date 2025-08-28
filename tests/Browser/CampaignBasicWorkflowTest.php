<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

test('campaign creation works without description', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);

    auth()->login($creator);
    
    $page = visit('/campaigns/create');
    
    $page->assertSee('Create Campaign')
        ->type('name', 'Test Campaign No Description')
        // Not filling description field
        ->press('Create Campaign')
        ->wait(2)
        ->assertPathBeginsWith('/campaigns/')
        ->assertSee('Campaign created successfully!')
        ->assertSee('Test Campaign No Description');

    // Verify campaign was created without description
    $campaign = Campaign::where('name', 'Test Campaign No Description')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->description)->toBeNull();
});

test('campaign creation works with frame selection', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'Test Frame',
        'description' => 'A test frame',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['Test adventure'],
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
    
    $page->assertSee('Create Campaign')
        ->type('name', 'Campaign With Frame')
        ->select('campaign_frame_id', (string) $frame->id)
        ->press('Create Campaign')
        ->wait(2)
        ->assertPathBeginsWith('/campaigns/')
        ->assertSee('Campaign created successfully!')
        ->assertSee('Campaign With Frame')
        ->assertSee('Test Frame');

    // Verify campaign was created with frame
    $campaign = Campaign::where('name', 'Campaign With Frame')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->campaign_frame_id)->toBe($frame->id);
});

test('campaign shows frame information when linked', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'Epic Quest Frame',
        'description' => 'An epic fantasy adventure',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['Heroes must save the world from ancient evil'],
        'touchstones' => ['Lord of the Rings'],
        'tone' => ['heroic'],
        'themes' => ['good vs evil'],
        'player_principles' => ['Work together'],
        'gm_principles' => ['Challenge players fairly'],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => 'A world in peril',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => 'Ancient evil awakens',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => ['What drives your hero?'],
    ]);

    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'campaign_frame_id' => $frame->id,
        'name' => 'Epic Quest Campaign',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}");
    
    $page->assertSee('Epic Quest Campaign')
        ->assertSee('Epic Quest Frame')
        ->assertSee('Heroes must save the world from ancient evil');
});

test('user can view campaigns list', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    
    // Create a campaign for the user
    Campaign::factory()->create([
        'creator_id' => $user->id,
        'name' => 'My Test Campaign',
    ]);

    auth()->login($user);
    
    $page = visit('/campaigns');
    
    $page->assertSee('Campaigns')
        ->assertSee('My Test Campaign');
});
