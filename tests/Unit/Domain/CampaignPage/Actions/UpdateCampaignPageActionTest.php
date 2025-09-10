<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Actions\UpdateCampaignPageAction;
use Domain\CampaignPage\Data\UpdateCampaignPageData;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;

it('updates a campaign page successfully', function () {
    $campaign = Campaign::factory()->create();
    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Original Title',
        'content' => '<p>Original content</p>',
        'category_tags' => ['Old Tag'],
        'access_level' => PageAccessLevel::GM_ONLY,
        'is_published' => false,
    ]);

    $data = UpdateCampaignPageData::from([
        'parent_id' => null,
        'title' => 'Updated Title',
        'content' => '<p>Updated content</p>',
        'category_tags' => ['New Tag', 'Another Tag'],
        'access_level' => PageAccessLevel::ALL_PLAYERS,
        'display_order' => 5,
        'is_published' => true,
        'authorized_user_ids' => [],
    ]);

    $action = new UpdateCampaignPageAction;
    $updatedPage = $action->execute($page, $data);

    expect($updatedPage->title)->toBe('Updated Title');
    expect($updatedPage->content)->toBe('<p>Updated content</p>');
    expect($updatedPage->category_tags)->toBe(['New Tag', 'Another Tag']);
    expect($updatedPage->access_level)->toBe(PageAccessLevel::ALL_PLAYERS);
    expect($updatedPage->display_order)->toBe(5);
    expect($updatedPage->is_published)->toBeTrue();
});

it('updates parent page relationship', function () {
    $campaign = Campaign::factory()->create();
    $originalParent = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);
    $newParent = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $originalParent->id,
    ]);

    $data = UpdateCampaignPageData::from([
        'parent_id' => $newParent->id,
        'title' => $page->title,
        'content' => $page->content,
        'category_tags' => $page->category_tags ?? [],
        'access_level' => $page->access_level,
        'display_order' => $page->display_order,
        'is_published' => $page->is_published,
        'authorized_user_ids' => [],
    ]);

    $action = new UpdateCampaignPageAction;
    $updatedPage = $action->execute($page, $data);

    expect($updatedPage->parent_id)->toBe($newParent->id);
    expect($updatedPage->parent->id)->toBe($newParent->id);
});

it('can remove parent to make page root level', function () {
    $campaign = Campaign::factory()->create();
    $parent = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parent->id,
    ]);

    $data = UpdateCampaignPageData::from([
        'parent_id' => null,
        'title' => $page->title,
        'content' => $page->content,
        'category_tags' => $page->category_tags ?? [],
        'access_level' => $page->access_level,
        'display_order' => $page->display_order,
        'is_published' => $page->is_published,
        'authorized_user_ids' => [],
    ]);

    $action = new UpdateCampaignPageAction;
    $updatedPage = $action->execute($page, $data);

    expect($updatedPage->parent_id)->toBeNull();
    expect($updatedPage->parent)->toBeNull();
});

it('prevents circular references with self as parent', function () {
    $campaign = Campaign::factory()->create();
    $page = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    $data = UpdateCampaignPageData::from([
        'parent_id' => $page->id, // Trying to set self as parent
        'title' => $page->title,
        'content' => $page->content,
        'category_tags' => $page->category_tags ?? [],
        'access_level' => $page->access_level,
        'display_order' => $page->display_order,
        'is_published' => $page->is_published,
        'authorized_user_ids' => [],
    ]);

    $action = new UpdateCampaignPageAction;

    expect(fn () => $action->execute($page, $data))
        ->toThrow(InvalidArgumentException::class, 'Cannot set parent: would create circular reference');
});

it('prevents circular references with descendant as parent', function () {
    $campaign = Campaign::factory()->create();

    $grandparent = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);
    $parent = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $grandparent->id,
    ]);
    $child = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parent->id,
    ]);

    // Try to make child the parent of grandparent (would create circular reference)
    $data = UpdateCampaignPageData::from([
        'parent_id' => $child->id,
        'title' => $grandparent->title,
        'content' => $grandparent->content,
        'category_tags' => $grandparent->category_tags ?? [],
        'access_level' => $grandparent->access_level,
        'display_order' => $grandparent->display_order,
        'is_published' => $grandparent->is_published,
        'authorized_user_ids' => [],
    ]);

    $action = new UpdateCampaignPageAction;

    expect(fn () => $action->execute($grandparent, $data))
        ->toThrow(InvalidArgumentException::class, 'Cannot set parent: would create circular reference');
});

it('syncs authorized users for specific players', function () {
    $campaign = Campaign::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $page = CampaignPage::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);
    $page->authorizedUsers()->attach([$user1->id, $user2->id]);

    $data = UpdateCampaignPageData::from([
        'parent_id' => null,
        'title' => $page->title,
        'content' => $page->content,
        'category_tags' => $page->category_tags ?? [],
        'access_level' => PageAccessLevel::SPECIFIC_PLAYERS,
        'display_order' => $page->display_order,
        'is_published' => $page->is_published,
        'authorized_user_ids' => [$user2->id, $user3->id], // Replace user1 with user3
    ]);

    $action = new UpdateCampaignPageAction;
    $updatedPage = $action->execute($page, $data);

    expect($updatedPage->authorizedUsers)->toHaveCount(2);
    expect($updatedPage->authorizedUsers->pluck('id')->toArray())->toContain($user2->id, $user3->id);
    expect($updatedPage->authorizedUsers->pluck('id')->toArray())->not->toContain($user1->id);
});

it('clears authorized users when changing from specific players', function () {
    $campaign = Campaign::factory()->create();
    $user1 = User::factory()->create();

    $page = CampaignPage::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);
    $page->authorizedUsers()->attach($user1->id);

    $data = UpdateCampaignPageData::from([
        'parent_id' => null,
        'title' => $page->title,
        'content' => $page->content,
        'category_tags' => $page->category_tags ?? [],
        'access_level' => PageAccessLevel::ALL_PLAYERS, // Change from specific to all players
        'display_order' => $page->display_order,
        'is_published' => $page->is_published,
        'authorized_user_ids' => [$user1->id], // Should be ignored
    ]);

    $action = new UpdateCampaignPageAction;
    $updatedPage = $action->execute($page, $data);

    expect($updatedPage->authorizedUsers)->toHaveCount(0);
});

it('updates page in database transaction', function () {
    $campaign = Campaign::factory()->create();
    $page = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    $data = UpdateCampaignPageData::from([
        'parent_id' => null,
        'title' => 'Updated Title',
        'content' => $page->content,
        'category_tags' => $page->category_tags ?? [],
        'access_level' => PageAccessLevel::SPECIFIC_PLAYERS,
        'display_order' => $page->display_order,
        'is_published' => $page->is_published,
        'authorized_user_ids' => [999999], // Invalid user ID to force error
    ]);

    $action = new UpdateCampaignPageAction;

    // This should fail and rollback the transaction
    expect(fn () => $action->execute($page, $data))
        ->toThrow(Exception::class);

    // Verify page wasn't updated
    $page->refresh();
    expect($page->title)->not->toBe('Updated Title');
});
