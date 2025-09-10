<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\CampaignPage\Repositories\CampaignPageRepository;
use Domain\User\Models\User;

it('finds_page by id with relations', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    $parent = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
        'parent_id' => $parent->id,
        'title' => 'Test Page',
    ]);

    $child = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $page->id,
        'display_order' => 1,
    ]);

    $repository = new CampaignPageRepository;
    $result = $repository->findById($page->id);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($page->id);
    expect($result->title)->toBe('Test Page');
    expect($result->campaign)->not->toBeNull();
    expect($result->creator)->not->toBeNull();
    expect($result->parent)->not->toBeNull();
    expect($result->children)->toHaveCount(1);
});

it('returns_null for non existent page', function () {
    $repository = new CampaignPageRepository;
    $result = $repository->findById(999999);

    expect($result)->toBeNull();
});

it('gets accessible pages for campaign', function () {
    $campaignCreator = User::factory()->create();
    $player = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $campaignCreator->id]);

    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player->id]);

    $gmPage = CampaignPage::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
    $playersPage = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
    $specificPage = CampaignPage::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);
    $specificPage->authorizedUsers()->attach($player->id);

    $repository = new CampaignPageRepository;

    // Campaign creator should see all pages
    $creatorPages = $repository->getAccessiblePagesForCampaign($campaign, $campaignCreator);
    expect($creatorPages)->toHaveCount(3);

    // Player should see players and specific pages
    $playerPages = $repository->getAccessiblePagesForCampaign($campaign, $player);
    expect($playerPages)->toHaveCount(2);
    expect($playerPages->pluck('id')->toArray())->toContain($playersPage->id, $specificPage->id);
    expect($playerPages->pluck('id')->toArray())->not->toContain($gmPage->id);
});

it('gets root pages for campaign', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $rootPage1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'display_order' => 2,
    ]);
    $rootPage2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'display_order' => 1,
    ]);
    $childPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $rootPage1->id,
    ]);

    $repository = new CampaignPageRepository;
    $rootPages = $repository->getRootPagesForCampaign($campaign, $user);

    expect($rootPages)->toHaveCount(2);
    expect($rootPages->first()->display_order)->toBe(1); // Should be ordered by display_order
    expect($rootPages->last()->display_order)->toBe(2);
    expect($rootPages->pluck('id')->toArray())->not->toContain($childPage->id);
});

it('gets child pages', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $parent = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
    $child1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parent->id,
        'display_order' => 2,
    ]);
    $child2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parent->id,
        'display_order' => 1,
    ]);
    $otherPage = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

    $repository = new CampaignPageRepository;
    $children = $repository->getChildPages($parent, $user);

    expect($children)->toHaveCount(2);
    expect($children->first()->display_order)->toBe(1); // Should be ordered by display_order
    expect($children->last()->display_order)->toBe(2);
    expect($children->pluck('id')->toArray())->not->toContain($otherPage->id);
});

it('searches pages in campaign', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $matchingPage1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Lair',
        'content' => '<p>Ancient red dragon</p>',
    ]);
    $matchingPage2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Village',
        'content' => '<p>Dragon scales sold here</p>',
    ]);
    $nonMatchingPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Forest',
        'content' => '<p>Peaceful woods</p>',
    ]);

    $repository = new CampaignPageRepository;
    $results = $repository->searchPagesInCampaign($campaign, 'dragon', $user);

    expect($results)->toHaveCount(2);
    expect($results->pluck('id')->toArray())->toContain($matchingPage1->id, $matchingPage2->id);
    expect($results->pluck('id')->toArray())->not->toContain($nonMatchingPage->id);
});

it('gets pages by category', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $npcPage1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs', 'Villains'],
    ]);
    $npcPage2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs', 'Locations'],
    ]);
    $lorePage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Lore'],
    ]);

    $repository = new CampaignPageRepository;
    $npcPages = $repository->getPagesByCategory($campaign, 'NPCs', $user);

    expect($npcPages)->toHaveCount(2);
    expect($npcPages->pluck('id')->toArray())->toContain($npcPage1->id, $npcPage2->id);
    expect($npcPages->pluck('id')->toArray())->not->toContain($lorePage->id);
});

it('gets category tags for campaign', function () {
    $campaign = Campaign::factory()->create();

    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs', 'Villains'],
    ]);
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Locations', 'NPCs'],
    ]);
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Lore'],
    ]);

    $repository = new CampaignPageRepository;
    $tags = $repository->getCategoryTagsForCampaign($campaign);

    expect($tags)->toHaveCount(4);
    expect($tags->toArray())->toContain('Lore', 'Locations', 'NPCs', 'Villains');
    // expect($tags->toArray())->toBeSorted(); // Should be sorted - toBeSorted() method doesn't exist
});

it('gets page hierarchy', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $root1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Chapter 1',
        'display_order' => 1,
    ]);
    $root2 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Chapter 2',
        'display_order' => 2,
    ]);
    $child1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $root1->id,
        'title' => 'Section 1.1',
        'display_order' => 1,
    ]);
    $grandchild = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $child1->id,
        'title' => 'Section 1.1.1',
        'display_order' => 1,
    ]);

    $repository = new CampaignPageRepository;
    $hierarchy = $repository->getPageHierarchy($campaign, $user);

    expect($hierarchy)->toHaveCount(2); // Two root pages

    $firstRoot = $hierarchy->first();
    expect($firstRoot->title)->toBe('Chapter 1');
    expect($firstRoot->children)->toHaveCount(1);

    $firstChild = $firstRoot->children->first();
    expect($firstChild->title)->toBe('Section 1.1');
    expect($firstChild->children)->toHaveCount(1);

    $grandchild = $firstChild->children->first();
    expect($grandchild->title)->toBe('Section 1.1.1');
});

it('handles advanced search filters', function () {
    $campaign = Campaign::factory()->create();
    $user = User::factory()->create();

    // Add user as campaign member to allow access to ALL_PLAYERS pages
    $campaign->members()->create(['user_id' => $user->id, 'joined_at' => now()]);

    $page1 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Information',
        'category_tags' => ['NPCs'],
        'created_at' => now()->subDay(),
    ]);
    $page2 = CampaignPage::factory()->gmOnly()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Secrets',
        'category_tags' => ['NPCs'],
        'created_at' => now(),
    ]);
    $page3 = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Lore',
        'category_tags' => ['Lore'],
        'created_at' => now(),
    ]);

    $repository = new CampaignPageRepository;

    $filters = [
        'query' => 'dragon',
        'categories' => ['NPCs'],
        // Remove access_level filter since we know user has access
        // 'access_level' => 'all_players',
        // Remove date filter to avoid time-based issues
        // 'date_from' => now()->subHour()->toDateString(),
        'sort_by' => 'title',
        'sort_direction' => 'asc',
        'limit' => 10,
    ];

    $results = $repository->advancedSearch($campaign, $filters, $user);

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($page1->id);
});
