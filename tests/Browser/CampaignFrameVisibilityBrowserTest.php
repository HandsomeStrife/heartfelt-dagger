<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\CampaignFrame\Models\CampaignFrameVisibility;
use Domain\User\Models\User;

test('campaign creator can access frame visibility controls', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'Test Adventure Frame',
        'description' => 'A test campaign frame',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['An exciting adventure awaits brave heroes!'],
        'touchstones' => ['Lord of the Rings', 'The Hobbit'],
        'tone' => ['heroic', 'epic'],
        'themes' => ['friendship', 'courage'],
        'player_principles' => ['Work together', 'Be brave'],
        'gm_principles' => ['Challenge the players', 'Reward creativity'],
        'community_guidance' => [],
        'ancestry_guidance' => [],
        'class_guidance' => [],
        'background_overview' => 'A world of magic and adventure',
        'setting_guidance' => [],
        'setting_distinctions' => [],
        'inciting_incident' => 'A dragon threatens the village',
        'special_mechanics' => [],
        'campaign_mechanics' => [],
        'session_zero_questions' => ['What motivates your character?'],
    ]);

    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'campaign_frame_id' => $frame->id,
        'name' => 'Test Campaign',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}");
    
    $page->assertSee('Test Campaign')
        ->assertSee('Test Adventure Frame')
        ->assertSee('Campaign Setting Guide')
        ->assertSee('Manage Player Visibility')
        ->click('Manage Player Visibility')
        ->waitForText('Campaign Frame Visibility')
        ->assertSee('Control which sections players can see')
        ->assertSee('Campaign Pitch')
        ->assertSee('Touchstones')
        ->assertSee('Tone')
        ->assertSee('Themes')
        ->assertSee('Player Principles')
        ->assertSee('GM Principles');
});

test('campaign creator can toggle section visibility', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'Visibility Test Frame',
        'description' => 'Testing visibility controls',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['Secret mission awaits!'],
        'touchstones' => [],
        'tone' => [],
        'themes' => [],
        'player_principles' => [],
        'gm_principles' => ['Keep secrets from players'],
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
        'name' => 'Visibility Test Campaign',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}");
    
    $page->click('Manage Player Visibility')
        ->waitForText('Campaign Frame Visibility')
        // GM Principles should be OFF by default (not visible to players)
        ->assertSee('GM only') 
        // Toggle GM Principles to be visible to players
        ->click('@toggle-gm_principles')
        ->wait(1) // Wait for toggle state change
        ->click('Save Settings')
        ->waitForText('Visibility settings saved successfully!');

    // Verify the setting was saved in the database
    $visibility = CampaignFrameVisibility::where([
        'campaign_id' => $campaign->id,
        'section_name' => 'gm_principles',
    ])->first();
    
    expect($visibility)->not->toBeNull();
    expect($visibility->is_visible_to_players)->toBe(true);
});

test('campaign creation workflow with optional description', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    
    $frame = CampaignFrame::create([
        'name' => 'Creation Test Frame',
        'description' => 'Frame for testing creation workflow',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $creator->id,
        'pitch' => ['A simple adventure'],
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
        ->type('[name="name"]', 'My New Campaign')
        // Note: NOT filling in description (should be optional)
        ->select('[name="campaign_frame_id"]', (string) $frame->id)
        ->press('Create Campaign')
        ->waitForLocation('/campaigns/*')
        ->assertSee('Campaign created successfully!')
        ->assertSee('My New Campaign')
        ->assertSee('Creation Test Frame')
        ->assertSee('A simple adventure'); // Should see frame content

    // Verify campaign was created without description
    $campaign = Campaign::where('name', 'My New Campaign')->first();
    expect($campaign)->not->toBeNull();
    expect($campaign->description)->toBeNull();
    expect($campaign->campaign_frame_id)->toBe($frame->id);
});
