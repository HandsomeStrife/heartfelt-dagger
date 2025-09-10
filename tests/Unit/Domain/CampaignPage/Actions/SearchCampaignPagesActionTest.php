<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Actions\SearchCampaignPagesAction;
use Domain\CampaignPage\Data\SearchCampaignPagesData;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;

it('searches pages by text content', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $page1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Lair',
        'content' => '<p>Ancient red dragon lives here with treasure.</p>',
    ]);

    $page2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Village Market',
        'content' => '<p>Local merchants sell dragon scales.</p>',
    ]);

    $page3 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Peaceful Forest',
        'content' => '<p>No dangerous creatures here.</p>',
    ]);

    $searchData = SearchCampaignPagesData::from([
        'query' => 'dragon',
        'sort_by' => 'relevance',
    ]);

    $action = new SearchCampaignPagesAction;

    // Debug: Check what we have before executing
    $totalPages = CampaignPage::where('campaign_id', $campaign->id)->count();
    $accessiblePages = CampaignPage::inCampaign($campaign)->accessibleBy($user)->count();
    $searchablePages = CampaignPage::inCampaign($campaign)->accessibleBy($user)->search('dragon')->count();

    $results = $action->execute($campaign, $searchData, $user);

    expect($results)->toHaveCount(2, "Expected 2 results but got {$results->count()}. Debug: totalPages={$totalPages}, accessiblePages={$accessiblePages}, searchablePages={$searchablePages}");
    expect($results->pluck('id')->toArray())->toContain($page1->id, $page2->id);
    expect($results->pluck('id')->toArray())->not->toContain($page3->id);
});

it('filters by category tags', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $page1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs', 'Villains'],
    ]);

    $page2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Locations', 'NPCs'],
    ]);

    $page3 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Lore'],
    ]);

    $searchData = SearchCampaignPagesData::from([
        'category_tags' => ['NPCs'],
    ]);

    $action = new SearchCampaignPagesAction;
    $results = $action->execute($campaign, $searchData, $user);

    expect($results)->toHaveCount(2);
    expect($results->pluck('id')->toArray())->toContain($page1->id, $page2->id);
    expect($results->pluck('id')->toArray())->not->toContain($page3->id);
});

it('filters by access level', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $gmPage = CampaignPage::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
    $playersPage = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
    $specificPage = CampaignPage::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);

    $searchData = SearchCampaignPagesData::from([
        'access_level' => PageAccessLevel::ALL_PLAYERS,
    ]);

    $action = new SearchCampaignPagesAction;
    $results = $action->execute($campaign, $searchData, $user);

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($playersPage->id);
});

it('filters by parent id', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $parent = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
    $child1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parent->id,
    ]);
    $child2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parent->id,
    ]);
    $rootPage = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

    $searchData = SearchCampaignPagesData::from([
        'parent_id' => $parent->id,
    ]);

    $action = new SearchCampaignPagesAction;
    $results = $action->execute($campaign, $searchData, $user);

    expect($results)->toHaveCount(2);
    expect($results->pluck('id')->toArray())->toContain($child1->id, $child2->id);
    expect($results->pluck('id')->toArray())->not->toContain($parent->id, $rootPage->id);
});

it('filters root pages only', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $rootPage1 = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
    $rootPage2 = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
    $childPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $rootPage1->id,
    ]);

    $searchData = SearchCampaignPagesData::from([
        'root_pages_only' => true,
    ]);

    $action = new SearchCampaignPagesAction;
    $results = $action->execute($campaign, $searchData, $user);

    expect($results)->toHaveCount(2);
    expect($results->pluck('id')->toArray())->toContain($rootPage1->id, $rootPage2->id);
    expect($results->pluck('id')->toArray())->not->toContain($childPage->id);
});

it('respects access permissions', function () {
    $campaignCreator = User::factory()->create();
    $player = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $campaignCreator->id]);

    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player->id]);

    $gmPage = CampaignPage::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
    $playersPage = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

    $searchData = SearchCampaignPagesData::from([]);

    $action = new SearchCampaignPagesAction;

    // Campaign creator should see all pages
    $creatorResults = $action->execute($campaign, $searchData, $campaignCreator);
    expect($creatorResults)->toHaveCount(2);

    // Player should only see player-accessible pages
    $playerResults = $action->execute($campaign, $searchData, $player);
    expect($playerResults)->toHaveCount(1);
    expect($playerResults->first()->id)->toBe($playersPage->id);
});

it('sorts by title', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $pageC = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Charlie Page',
    ]);
    $pageA = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Alpha Page',
    ]);
    $pageB = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Beta Page',
    ]);

    $searchData = SearchCampaignPagesData::from([
        'sort_by' => 'title',
        'sort_direction' => 'asc',
    ]);

    $action = new SearchCampaignPagesAction;
    $results = $action->execute($campaign, $searchData, $user);

    expect($results->first()->title)->toBe('Alpha Page');
    expect($results->last()->title)->toBe('Charlie Page');
});

it('limits results', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    // Create 5 pages
    for ($i = 1; $i <= 5; $i++) {
        CampaignPage::factory()->allPlayers()->create([
            'campaign_id' => $campaign->id,
            'title' => "Page $i",
        ]);
    }

    $searchData = SearchCampaignPagesData::from([
        'limit' => 3,
    ]);

    $action = new SearchCampaignPagesAction;
    $results = $action->execute($campaign, $searchData, $user);

    expect($results)->toHaveCount(3);
});

it('gets search suggestions', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $page1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Pyraxis the Dragon',
        'content' => '<p>Ancient red dragon</p>',
    ]);

    $page2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Lair',
        'content' => '<p>Where Pyraxis lives</p>',
    ]);

    $page3 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Village Market',
        'content' => '<p>No dragons here</p>',
    ]);

    $action = new SearchCampaignPagesAction;
    $suggestions = $action->getSuggestions($campaign, 'Pyr', $user, 2);

    expect($suggestions)->toHaveCount(2);
    expect($suggestions->pluck('title')->toArray())->toContain('Pyraxis the Dragon', 'Dragon Lair');
});

it('gets popular categories', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    // Create pages with different category combinations
    CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs', 'Villains'],
    ]);
    CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs', 'Locations'],
    ]);
    CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Lore'],
    ]);

    $action = new SearchCampaignPagesAction;
    $categories = $action->getPopularCategories($campaign, $user);

    expect($categories)->toHaveCount(4);

    // NPCs should be most popular (appears in 2 pages)
    $npcCategory = $categories->first();
    expect($npcCategory['tag'])->toBe('NPCs');
    expect($npcCategory['count'])->toBe(2);
});
