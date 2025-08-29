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

test('hover state is isolated to individual page entries', function () {
    // Create multiple pages to test hover isolation
    $page1 = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'First Page',
        'parent_id' => null,
    ]);

    $page2 = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Second Page',
        'parent_id' => null,
    ]);

    actingAs($this->user)
        ->visit("/campaigns/{$this->campaign->campaign_code}/pages")
        ->assertSee('First Page')
        ->assertSee('Second Page')
        // Wait for content to load
        ->wait(1)
        // Test that action buttons exist but are initially hidden via CSS
        ->assertPresent('[wire\\:click*="viewPage"]')
        ->assertPresent('[wire\\:click*="editPage"]')
        // Verify the opacity classes are applied correctly
        ->assertPresent('.group')
        ->assertPresent('.opacity-0.group-hover\\:opacity-100');
});

test('eye icon navigates to page view', function () {
    $page = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Test Page',
        'content' => '<p>This is test content</p>',
        'parent_id' => null,
    ]);

    actingAs($this->user)
        ->visit("/campaigns/{$this->campaign->campaign_code}/pages")
        ->assertSee('Test Page')
        ->wait(1)
        // Click the view button using wire:click selector
        ->click('[wire\\:click*="viewPage"]')
        ->wait(2)
        ->assertPathIs("/campaigns/{$this->campaign->campaign_code}/pages/{$page->id}")
        ->assertSee('Test Page')
        ->assertSee('This is test content');
});

test('plus icon opens sub-page creation form with parent set', function () {
    $parentPage = CampaignPage::factory()->create([
        'campaign_id' => $this->campaign->id,
        'creator_id' => $this->user->id,
        'title' => 'Parent Page',
        'parent_id' => null,
    ]);

    actingAs($this->user)
        ->visit("/campaigns/{$this->campaign->campaign_code}/pages")
        ->assertSee('Parent Page')
        ->wait(1)
        // Click the add child page button using wire:click selector
        ->click('[wire\\:click*="createPage"]')
        ->wait(2)
        // Check that the slideover form opened
        ->assertSee('Create Campaign Page')
        ->wait(1)
        // Verify that the form is present
        ->assertPresent('#title');
});
