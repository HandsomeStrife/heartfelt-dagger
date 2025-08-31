<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Actions\CreateCampaignPageAction;
use Domain\CampaignPage\Data\CreateCampaignPageData;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;

it('creates a campaign page successfully', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    
    $data = CreateCampaignPageData::from([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'title' => 'Test Campaign Page',
        'content' => '<p>This is a test page content.</p>',
        'category_tags' => ['Lore', 'NPCs'],
        'access_level' => PageAccessLevel::ALL_PLAYERS,
        'display_order' => 1,
        'is_published' => true,
        'authorized_user_ids' => [],
    ]);

    $action = new CreateCampaignPageAction();
    $page = $action->execute($data, $creator);

    expect($page)->toBeInstanceOf(CampaignPage::class);
    expect($page->campaign_id)->toBe($campaign->id);
    expect($page->creator_id)->toBe($creator->id);
    expect($page->title)->toBe('Test Campaign Page');
    expect($page->content)->toBe('<p>This is a test page content.</p>');
    expect($page->category_tags)->toBe(['Lore', 'NPCs']);
    expect($page->access_level)->toBe(PageAccessLevel::ALL_PLAYERS);
    expect($page->display_order)->toBe(1);
    expect($page->is_published)->toBeTrue();
});

it('creates a child page with correct parent', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    
    $parentPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id
    ]);
    
    $data = CreateCampaignPageData::from([
        'campaign_id' => $campaign->id,
        'parent_id' => $parentPage->id,
        'title' => 'Child Page',
        'content' => '<p>Child content</p>',
        'category_tags' => [],
        'access_level' => PageAccessLevel::GM_ONLY,
        'display_order' => 0,
        'is_published' => true,
        'authorized_user_ids' => [],
    ]);

    $action = new CreateCampaignPageAction();
    $page = $action->execute($data, $creator);

    expect($page->parent_id)->toBe($parentPage->id);
    expect($page->parent->id)->toBe($parentPage->id);
});

it('sets display order automatically when zero', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    
    // Create existing pages with different display orders
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'display_order' => 1
    ]);
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'display_order' => 3
    ]);
    
    $data = CreateCampaignPageData::from([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'title' => 'Auto Order Page',
        'content' => null,
        'category_tags' => [],
        'access_level' => PageAccessLevel::GM_ONLY,
        'display_order' => 0, // Should auto-increment
        'is_published' => true,
        'authorized_user_ids' => [],
    ]);

    $action = new CreateCampaignPageAction();
    $page = $action->execute($data, $creator);

    expect($page->display_order)->toBe(4); // Max (3) + 1
});

it('attaches authorized users for specific players access', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $data = CreateCampaignPageData::from([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'title' => 'Specific Access Page',
        'content' => null,
        'category_tags' => [],
        'access_level' => PageAccessLevel::SPECIFIC_PLAYERS,
        'display_order' => 1,
        'is_published' => true,
        'authorized_user_ids' => [$user1->id, $user2->id],
    ]);

    $action = new CreateCampaignPageAction();
    $page = $action->execute($data, $creator);

    expect($page->authorizedUsers)->toHaveCount(2);
    expect($page->authorizedUsers->pluck('id')->toArray())->toContain($user1->id, $user2->id);
});

it('does not attach users for non specific access levels', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    $user1 = User::factory()->create();
    
    $data = CreateCampaignPageData::from([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'title' => 'All Players Page',
        'content' => null,
        'category_tags' => [],
        'access_level' => PageAccessLevel::ALL_PLAYERS,
        'display_order' => 1,
        'is_published' => true,
        'authorized_user_ids' => [$user1->id], // Should be ignored
    ]);

    $action = new CreateCampaignPageAction();
    $page = $action->execute($data, $creator);

    expect($page->authorizedUsers)->toHaveCount(0);
});

it('handles null content and empty categories', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    
    $data = CreateCampaignPageData::from([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'title' => 'Minimal Page',
        'content' => null,
        'category_tags' => [],
        'access_level' => PageAccessLevel::GM_ONLY,
        'display_order' => 1,
        'is_published' => true,
        'authorized_user_ids' => [],
    ]);

    $action = new CreateCampaignPageAction();
    $page = $action->execute($data, $creator);

    expect($page->content)->toBeNull();
    expect($page->category_tags)->toBe([]);
});

it('creates page in database transaction', function () {
    $campaign = Campaign::factory()->create();
    $creator = User::factory()->create();
    
    $data = CreateCampaignPageData::from([
        'campaign_id' => $campaign->id,
        'parent_id' => null,
        'title' => 'Transaction Test',
        'content' => null,
        'category_tags' => [],
        'access_level' => PageAccessLevel::SPECIFIC_PLAYERS,
        'display_order' => 1,
        'is_published' => true,
        'authorized_user_ids' => [999999], // Invalid user ID to force error
    ]);

    $action = new CreateCampaignPageAction();
    
    // This should fail and rollback the transaction
    expect(fn() => $action->execute($data, $creator))
        ->toThrow(Exception::class);
    
    // Verify no page was created
    expect(CampaignPage::where('title', 'Transaction Test')->exists())->toBeFalse();
});
