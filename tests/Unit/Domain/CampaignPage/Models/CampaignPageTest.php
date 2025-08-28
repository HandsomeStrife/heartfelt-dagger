<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;

it('belongs to a campaign', function () {
    $campaign = Campaign::factory()->create();
    $page = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    expect($page->campaign)->toBeInstanceOf(Campaign::class);
    expect($page->campaign->id)->toBe($campaign->id);
});

it('belongs to a creator', function () {
    $user = User::factory()->create();
    $page = CampaignPage::factory()->create(['creator_id' => $user->id]);

    expect($page->creator)->toBeInstanceOf(User::class);
    expect($page->creator->id)->toBe($user->id);
});

it('can have a parent page', function () {
    $campaign = Campaign::factory()->create();
    $parentPage = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);
    $childPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parentPage->id
    ]);

    expect($childPage->parent)->toBeInstanceOf(CampaignPage::class);
    expect($childPage->parent->id)->toBe($parentPage->id);
});

it('can have child pages', function () {
    $campaign = Campaign::factory()->create();
    $parentPage = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);
    $childPage1 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parentPage->id,
        'display_order' => 1
    ]);
    $childPage2 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parentPage->id,
        'display_order' => 2
    ]);

    expect($parentPage->children)->toHaveCount(2);
    expect($parentPage->children->first()->id)->toBe($childPage1->id);
    expect($parentPage->children->last()->id)->toBe($childPage2->id);
});

it('can have authorized users', function () {
    $page = CampaignPage::factory()->specificPlayers()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $page->authorizedUsers()->attach([$user1->id, $user2->id]);

    expect($page->authorizedUsers)->toHaveCount(2);
    expect($page->authorizedUsers->pluck('id')->toArray())->toContain($user1->id, $user2->id);
});

it('allows creator to always view page', function () {
    $creator = User::factory()->create();
    $page = CampaignPage::factory()->gmOnly()->create(['creator_id' => $creator->id]);

    expect($page->canBeViewedBy($creator))->toBeTrue();
});

it('allows campaign creator to always view page', function () {
    $campaignCreator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $campaignCreator->id]);
    $pageCreator = User::factory()->create();
    $page = CampaignPage::factory()->gmOnly()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $pageCreator->id
    ]);

    expect($page->canBeViewedBy($campaignCreator))->toBeTrue();
});

it('restricts gm only pages to gm', function () {
    $campaign = Campaign::factory()->create();
    $player = User::factory()->create();
    $page = CampaignPage::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);

    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $player->id
    ]);

    expect($page->canBeViewedBy($player))->toBeFalse();
});

it('allows all players pages to be viewed by campaign members', function () {
    $campaign = Campaign::factory()->create();
    $player = User::factory()->create();
    $page = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $player->id
    ]);

    expect($page->canBeViewedBy($player))->toBeTrue();
});

it('restricts specific players pages to authorized users only', function () {
    $campaign = Campaign::factory()->create();
    $authorizedPlayer = User::factory()->create();
    $unauthorizedPlayer = User::factory()->create();
    $page = CampaignPage::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);

    // Add both as campaign members
    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $authorizedPlayer->id
    ]);
    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $unauthorizedPlayer->id
    ]);

    // Only authorize one player for the page
    $page->authorizedUsers()->attach($authorizedPlayer->id);

    expect($page->canBeViewedBy($authorizedPlayer))->toBeTrue();
    expect($page->canBeViewedBy($unauthorizedPlayer))->toBeFalse();
});

it('restricts unpublished pages to creator only', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $page = CampaignPage::factory()->draft()->create(['creator_id' => $creator->id]);

    expect($page->canBeViewedBy($creator))->toBeTrue();
    expect($page->canBeViewedBy($otherUser))->toBeFalse();
});

it('can calculate depth level', function () {
    $campaign = Campaign::factory()->create();
    
    $rootPage = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);
    $childPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $rootPage->id
    ]);
    $grandchildPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $childPage->id
    ]);

    expect($rootPage->getDepthLevel())->toBe(0);
    expect($childPage->getDepthLevel())->toBe(1);
    expect($grandchildPage->getDepthLevel())->toBe(2);
});

it('can get ancestors', function () {
    $campaign = Campaign::factory()->create();
    
    $rootPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Root Page'
    ]);
    $childPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $rootPage->id,
        'title' => 'Child Page'
    ]);
    $grandchildPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $childPage->id,
        'title' => 'Grandchild Page'
    ]);

    $ancestors = $grandchildPage->ancestors();
    
    expect($ancestors)->toHaveCount(2);
    expect($ancestors[0]->id)->toBe($rootPage->id);
    expect($ancestors[1]->id)->toBe($childPage->id);
});

it('can detect descendant relationships', function () {
    $campaign = Campaign::factory()->create();
    
    $rootPage = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);
    $childPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $rootPage->id
    ]);
    $grandchildPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $childPage->id
    ]);
    $siblingPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $rootPage->id
    ]);

    expect($grandchildPage->isDescendantOf($rootPage))->toBeTrue();
    expect($grandchildPage->isDescendantOf($childPage))->toBeTrue();
    expect($grandchildPage->isDescendantOf($siblingPage))->toBeFalse();
    expect($childPage->isDescendantOf($grandchildPage))->toBeFalse();
});

it('scopes accessible pages correctly', function () {
    $campaignCreator = User::factory()->create();
    $pageCreator = User::factory()->create();
    $authorizedPlayer = User::factory()->create();
    $unauthorizedPlayer = User::factory()->create();
    
    $campaign = Campaign::factory()->create(['creator_id' => $campaignCreator->id]);
    
    // Add players to campaign
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $authorizedPlayer->id]);
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $unauthorizedPlayer->id]);

    $gmPage = CampaignPage::factory()->gmOnly()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $pageCreator->id
    ]);
    
    $allPlayersPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $pageCreator->id
    ]);
    
    $specificPage = CampaignPage::factory()->specificPlayers()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $pageCreator->id
    ]);
    $specificPage->authorizedUsers()->attach($authorizedPlayer->id);

    // Campaign creator should see all pages
    $accessibleToCreator = CampaignPage::accessibleBy($campaignCreator)->pluck('id')->toArray();
    expect($accessibleToCreator)->toContain($gmPage->id, $allPlayersPage->id, $specificPage->id);

    // Authorized player should see all players and specific pages
    $accessibleToAuthorized = CampaignPage::accessibleBy($authorizedPlayer)->pluck('id')->toArray();
    expect($accessibleToAuthorized)->toContain($allPlayersPage->id, $specificPage->id);
    expect($accessibleToAuthorized)->not->toContain($gmPage->id);

    // Unauthorized player should only see all players page
    $accessibleToUnauthorized = CampaignPage::accessibleBy($unauthorizedPlayer)->pluck('id')->toArray();
    expect($accessibleToUnauthorized)->toContain($allPlayersPage->id);
    expect($accessibleToUnauthorized)->not->toContain($gmPage->id, $specificPage->id);
});
