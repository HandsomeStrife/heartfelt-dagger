<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->campaign = Campaign::factory()->create([
        'creator_id' => $this->user->id,
    ]);
});

test('page entries display in compact single-line format', function () {
    // Create test pages
    $parentPage = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Parent Page',
        'parent_id' => null,
    ]);

    $childPage = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Child Page',
        'parent_id' => $parentPage->id,
    ]);

    actingAs($this->user)
        ->visit("/campaigns/{$this->campaign->campaign_code}/pages")
        ->assertSee('Parent Page')
        ->assertSee('Child Page')
        // Verify compact styling is applied
        ->assertPresent('[class*="px-4 py-3"]') // Compact padding
        ->assertPresent('[class*="text-sm"]') // Smaller text size
        // Verify single-line layout
        ->assertPresent('[class*="flex items-center justify-between"]')
        ->assertPresent('[class*="flex-1 min-w-0"]'); // Proper flex layout
});

test('total pages count includes all pages including children', function () {
    // Create hierarchy: 1 parent + 2 children + 1 grandchild = 4 total
    $parentPage = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Parent Page',
        'parent_id' => null,
    ]);

    $childPage1 = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Child Page 1',
        'parent_id' => $parentPage->id,
    ]);

    $childPage2 = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Child Page 2',
        'parent_id' => $parentPage->id,
    ]);

    $grandchildPage = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Grandchild Page',
        'parent_id' => $childPage1->id,
    ]);

    actingAs($this->user)
        ->visit("/campaigns/{$this->campaign->campaign_code}/pages")
        ->assertSee('Total Pages')
        ->assertSee('4'); // Should show 4 total pages
});

test('list view also displays pages in compact format', function () {
    // Create test pages
    CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Test Page',
        'parent_id' => null,
    ]);

    actingAs($this->user)
        ->visit("/campaigns/{$this->campaign->campaign_code}/pages")
        ->assertSee('Test Page')
        ->assertSee('List') // View toggle button exists
        ->click('[wire\\:click="setViewMode(\'list\')"]')
        ->wait(1)
        ->assertSee('Test Page')
        // Verify compact styling is applied in list view too
        ->assertPresent('[class*="px-4 py-3"]')
        ->assertPresent('[class*="text-sm"]');
});

test('hierarchy view shows proper indentation for child pages', function () {
    // Create parent and child
    $parentPage = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Parent Page',
        'parent_id' => null,
    ]);

    $childPage = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Child Page',
        'parent_id' => $parentPage->id,
    ]);

    actingAs($this->user)
        ->visit("/campaigns/{$this->campaign->campaign_code}/pages")
        ->assertSee('Parent Page')
        ->assertSee('Child Page')
        // Verify indentation styling
        ->assertPresent('[class*="border-l-2 border-slate-600"]') // Child indentation
        ->assertPresent('[class*="pl-4 ml-2"]'); // Child padding/margin
});
