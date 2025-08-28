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

    $page = visit('/');
    
    auth()->login($creator);
    
    $page
        ->visit("/campaigns/{$campaign->campaign_code}")
        ->assertSee('Test Campaign for Pages')
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

    $page = visit('/');
    
    auth()->login($creator);
    
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->assertSee('Create Your First Page')
        ->click('button[wire\\:click="createPage"]')
        ->waitForText('Create Campaign Page')
        ->type('input[wire\\:model="form.title"]', 'World Overview')
        ->type('textarea[wire\\:model="form.content"]', 'This is the world where our adventure takes place.')
        ->select('select[wire\\:model="form.access_level"]', 'all_players')
        ->click('button[wire\\:click="save"]')
        ->waitForText('Page created successfully!')
        ->assertSee('World Overview')
        ->assertSee('This is the world where our adventure takes place');

    // Verify the page was created in the database
    $campaignPage = CampaignPage::where('title', 'World Overview')->first();
    expect($campaignPage)->not->toBeNull();
    expect($campaignPage->campaign_id)->toBe($campaign->id);
    expect($campaignPage->access_level->value)->toBe('all_players');
});

test('campaign creator can add category tags to pages', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $page = visit('/');
    
    auth()->login($creator);
    
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->click('button[wire\\:click="createPage"]')
        ->waitForText('Create Campaign Page')
        ->type('input[wire\\:model="form.title"]', 'Important NPCs')
        ->type('input[wire\\:model="newTag"]', 'Characters')
        ->click('button[wire\\:click="addTag"]')
        ->waitForText('Characters')
        ->type('input[wire\\:model="newTag"]', 'Allies')
        ->press('{enter}') // Test enter key functionality
        ->waitForText('Allies')
        ->click('button[wire\\:click="save"]')
        ->waitForText('Page created successfully!')
        ->assertSee('Characters')
        ->assertSee('Allies');

    $campaignPage = CampaignPage::where('title', 'Important NPCs')->first();
    expect($campaignPage->category_tags)->toContain('Characters', 'Allies');
});

test('campaign creator can set access levels for pages', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $player = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    
    // Add player to campaign
    $campaign->members()->create(['user_id' => $player->id]);

    $page = visit('/');
    
    auth()->login($creator);
    
    // Create GM-only page
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->click('button[wire\\:click="createPage"]')
        ->waitForText('Create Campaign Page')
        ->type('input[wire\\:model="form.title"]', 'Secret GM Notes')
        ->type('textarea[wire\\:model="form.content"]', 'The BBEG is actually the king in disguise.')
        ->select('select[wire\\:model="form.access_level"]', 'gm_only')
        ->click('button[wire\\:click="save"]')
        ->waitForText('Page created successfully!')
        ->assertSee('Secret GM Notes');

    // Create player-visible page
    $page
        ->click('button[wire\\:click="createPage"]')
        ->waitForText('Create Campaign Page')
        ->type('input[wire\\:model="form.title"]', 'Public Information')
        ->type('textarea[wire\\:model="form.content"]', 'The kingdom needs heroes to save it.')
        ->select('select[wire\\:model="form.access_level"]', 'all_players')
        ->click('button[wire\\:click="save"]')
        ->waitForText('Page created successfully!')
        ->assertSee('Public Information');

    // Test GM view (should see both pages)
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->assertSee('Secret GM Notes')
        ->assertSee('Public Information');

    // Test player view (should only see public page)
    auth()->login($player);
    
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->assertSee('Public Information')
        ->assertDontSee('Secret GM Notes')
        ->assertDontSee('Create Your First Page'); // Players can't create pages
});

test('campaign creator can create hierarchical pages', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $page = visit('/');
    
    auth()->login($creator);
    
    // Create parent page
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->click('button[wire\\:click="createPage"]')
        ->waitForText('Create Campaign Page')
        ->type('input[wire\\:model="form.title"]', 'Chapter 1')
        ->click('button[wire\\:click="save"]')
        ->waitForText('Page created successfully!')
        ->assertSee('Chapter 1');

    $parentPage = CampaignPage::where('title', 'Chapter 1')->first();

    // Create child page
    $page
        ->click('button[wire\\:click="createPage"]')
        ->waitForText('Create Campaign Page')
        ->type('input[wire\\:model="form.title"]', 'Section 1.1')
        ->select('select[wire\\:model="form.parent_id"]', (string) $parentPage->id)
        ->click('button[wire\\:click="save"]')
        ->waitForText('Page created successfully!')
        ->assertSee('Section 1.1');

    // Verify parent-child relationship
    $childPage = CampaignPage::where('title', 'Section 1.1')->first();
    expect($childPage->parent_id)->toBe($parentPage->id);
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

    $page = visit('/');
    
    auth()->login($creator);
    
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->assertSee('Dragon Lore')
        ->assertSee('Treasure Maps')
        ->type('input[wire\\:model.live="search_query"]', 'dragon')
        ->waitForText('Dragon Lore') // Should still see dragon page
        ->assertDontSee('Treasure Maps') // Should not see treasure page
        ->click('button[wire\\:click="clearSearch"]')
        ->waitForText('Treasure Maps') // Should see all pages again
        ->assertSee('Dragon Lore');
});

test('campaign pages support different view modes', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    // Create a parent page and child page for hierarchy testing
    $parentPage = CampaignPage::create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
        'title' => 'World Building',
        'content' => 'The world of our campaign.',
        'access_level' => 'all_players',
    ]);

    CampaignPage::create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
        'parent_id' => $parentPage->id,
        'title' => 'Geography',
        'content' => 'Mountains and valleys shape the land.',
        'access_level' => 'all_players',
    ]);

    $page = visit('/');
    
    auth()->login($creator);
    
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->assertSee('World Building')
        ->assertSee('Geography')
        // Test switching to list view
        ->click('button[wire\\:click="setViewMode(\'list\')"]')
        ->waitForText('List View')
        ->assertSee('World Building')
        ->assertSee('Geography')
        // Test switching back to hierarchy view
        ->click('button[wire\\:click="setViewMode(\'hierarchy\')"]')
        ->waitForText('Hierarchy View')
        ->assertSee('World Building')
        ->assertSee('Geography');
});

test('non-members cannot access campaign pages', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $outsider = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $page = visit('/');
    
    auth()->login($outsider);
    
    $page
        ->visit("/campaigns/{$campaign->campaign_code}/pages")
        ->assertStatus(403); // Should be forbidden
});
