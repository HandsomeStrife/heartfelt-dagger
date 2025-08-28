<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;

test('campaign creator can access campaign pages management', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Campaign for Pages',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}");
    
    $page->assertSee('Test Campaign for Pages')
        ->assertSee('Campaign Pages')
        ->assertSee('Manage your campaign lore and information')
        ->click('a[href*="/pages"]')
        ->assertUrlIs("/campaigns/{$campaign->campaign_code}/pages")
        ->assertSee('Campaign Pages')
        ->assertSee('No pages found')
        ->assertSee('Create Your First Page');
});

test('campaign creator can create a new campaign page', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Create Your First Page')
        ->click('Create Your First Page')
        ->waitForText('Create Campaign Page')
        ->type('@title-input', 'World Overview')
        ->type('@content-input', 'This is the world where our adventure takes place.')
        ->select('@access-level-select', 'all_players')
        ->click('Save Page')
        ->waitForText('Page created successfully!')
        ->assertSee('World Overview')
        ->assertSee('This is the world where our adventure takes place');

    // Verify the page was created in the database
    $campaignPage = CampaignPage::where('title', 'World Overview')->first();
    expect($campaignPage)->not->toBeNull();
    expect($campaignPage->campaign_id)->toBe($campaign->id);
    expect($campaignPage->access_level->value)->toBe('all_players');
});

test('campaign creator can search through pages', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    // Create some test pages
    CampaignPage::create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
        'title' => 'Dragon Lore',
        'content' => 'Ancient dragons ruled the skies centuries ago.',
        'access_level' => 'all_players',
    ]);

    CampaignPage::create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
        'title' => 'Treasure Maps',
        'content' => 'Hidden treasures lie beneath the old castle.',
        'access_level' => 'gm_only',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Dragon Lore')
        ->assertSee('Treasure Maps')
        ->type('@search-input', 'dragon')
        ->wait(1) // Wait for live search
        ->assertSee('Dragon Lore') // Should still see dragon page
        ->assertDontSee('Treasure Maps') // Should not see treasure page
        ->click('Clear Search')
        ->wait(1) // Wait for search to clear
        ->assertSee('Treasure Maps') // Should see all pages again
        ->assertSee('Dragon Lore');
});

test('campaign players only see accessible pages', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $player = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    
    // Add player to campaign
    $campaign->members()->create(['user_id' => $player->id]);

    // Create GM-only page
    CampaignPage::create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
        'title' => 'Secret GM Notes',
        'content' => 'The BBEG is actually the king in disguise.',
        'access_level' => 'gm_only',
    ]);

    // Create player-visible page
    CampaignPage::create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
        'title' => 'Public Information',
        'content' => 'The kingdom needs heroes to save it.',
        'access_level' => 'all_players',
    ]);

    // Test GM view (should see both pages)
    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Secret GM Notes')
        ->assertSee('Public Information');

    // Test player view (should only see public page)
    auth()->login($player);
    
    $playerPage = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $playerPage->assertSee('Public Information')
        ->assertDontSee('Secret GM Notes')
        ->assertDontSee('Create Your First Page'); // Players can't create pages
});
