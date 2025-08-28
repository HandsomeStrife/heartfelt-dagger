<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use Laravel\Dusk\Browser;

test('campaign creator can complete full page management workflow', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Test Campaign',
    ]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}")
            ->assertSee('Test Campaign')
            ->assertSee('Campaign Pages')
            ->click('@manage-pages-button')
            ->assertUrlIs("/campaigns/{$campaign->campaign_code}/pages")
            ->assertSee('Campaign Pages')
            ->assertSee('No pages found')
            ->assertSee('Create Your First Page');
    });
});

test('campaign creator can create a new page', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->click('@create-page-button')
            ->waitFor('@page-form-modal')
            ->assertSee('Create Campaign Page')
            ->type('@title-input', 'My First Page')
            ->type('@content-editor .tiptap-content', 'This is my first campaign page content.')
            ->select('@access-level-select', 'all_players')
            ->check('@published-checkbox')
            ->click('@save-button')
            ->waitUntilMissing('@page-form-modal')
            ->assertSee('My First Page')
            ->assertSee('This is my first campaign page content');
    });

    expect(CampaignPage::where('title', 'My First Page')->exists())->toBeTrue();
});

test('campaign creator can add category tags', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->click('@create-page-button')
            ->waitFor('@page-form-modal')
            ->type('@title-input', 'Tagged Page')
            ->type('@new-tag-input', 'NPCs')
            ->click('@add-tag-button')
            ->type('@new-tag-input', 'Villains')
            ->keys('@new-tag-input', '{enter}')
            ->assertSee('NPCs')
            ->assertSee('Villains')
            ->click('@save-button')
            ->waitUntilMissing('@page-form-modal');
    });

    $page = CampaignPage::where('title', 'Tagged Page')->first();
    expect($page->category_tags)->toContain('NPCs', 'Villains');
});

test('campaign creator can create hierarchical pages', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    browse(function (Browser $browser) use ($user, $campaign) {
        // Create parent page
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->click('@create-page-button')
            ->waitFor('@page-form-modal')
            ->type('@title-input', 'Chapter 1')
            ->click('@save-button')
            ->waitUntilMissing('@page-form-modal')
            ->assertSee('Chapter 1');

        // Create child page
        $browser->click('@add-child-page-button')
            ->waitFor('@page-form-modal')
            ->assertSee('Create Campaign Page')
            ->type('@title-input', 'Section 1.1')
            ->click('@save-button')
            ->waitUntilMissing('@page-form-modal')
            ->assertSee('Section 1.1');
    });

    $parentPage = CampaignPage::where('title', 'Chapter 1')->first();
    $childPage = CampaignPage::where('title', 'Section 1.1')->first();
    
    expect($childPage->parent_id)->toBe($parentPage->id);
});

test('campaign creator can edit existing pages', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Original Title',
        'content' => '<p>Original content</p>',
    ]);

    browse(function (Browser $browser) use ($user, $campaign, $page) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->assertSee('Original Title')
            ->click('@edit-page-button')
            ->waitFor('@page-form-modal')
            ->assertSee('Edit Campaign Page')
            ->assertInputValue('@title-input', 'Original Title')
            ->clear('@title-input')
            ->type('@title-input', 'Updated Title')
            ->click('@save-button')
            ->waitUntilMissing('@page-form-modal')
            ->assertSee('Updated Title')
            ->assertDontSee('Original Title');
    });

    $page->refresh();
    expect($page->title)->toBe('Updated Title');
});

test('campaign creator can delete pages', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Page to Delete',
    ]);

    browse(function (Browser $browser) use ($user, $campaign, $page) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->assertSee('Page to Delete')
            ->click('@delete-page-button')
            ->acceptDialog()
            ->waitUntilMissing('.page-card:contains("Page to Delete")')
            ->assertDontSee('Page to Delete');
    });

    expect(CampaignPage::find($page->id))->toBeNull();
});

test('campaign creator can search pages', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Lair',
        'content' => '<p>Ancient red dragon lives here</p>',
    ]);
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Village Market',
        'content' => '<p>Peaceful trading post</p>',
    ]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->assertSee('Dragon Lair')
            ->assertSee('Village Market')
            ->type('@search-input', 'dragon')
            ->click('@search-button')
            ->assertSee('Dragon Lair')
            ->assertDontSee('Village Market');
    });
});

test('campaign creator can filter by categories', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'NPC Page',
        'category_tags' => ['NPCs'],
    ]);
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Lore Page',
        'category_tags' => ['Lore'],
    ]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->assertSee('NPC Page')
            ->assertSee('Lore Page')
            ->click('@category-filter-npcs')
            ->assertSee('NPC Page')
            ->assertDontSee('Lore Page');
    });
});

test('campaign creator can switch view modes', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    $parentPage = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Parent Page',
    ]);
    CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $parentPage->id,
        'title' => 'Child Page',
    ]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->assertSelected('@view-mode-hierarchy', 'hierarchy')
            ->click('@view-mode-list')
            ->assertSelected('@view-mode-list', 'list')
            ->assertSee('Parent Page')
            ->assertSee('Child Page');
    });
});

test('players can only see accessible pages', function () {
    $creator = User::factory()->create();
    $player = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player->id]);
    
    $gmPage = CampaignPage::factory()->gmOnly()->create([
        'campaign_id' => $campaign->id,
        'title' => 'GM Secret Page',
    ]);
    $playersPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Player Visible Page',
    ]);

    browse(function (Browser $browser) use ($player, $campaign) {
        $browser->loginAs($player)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->assertSee('Player Visible Page')
            ->assertDontSee('GM Secret Page')
            ->assertDontSee('Create Page'); // No create button for players
    });
});

test('rich text editor works correctly', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->click('@create-page-button')
            ->waitFor('@page-form-modal')
            ->type('@title-input', 'Rich Text Test')
            ->click('@tiptap-bold-button')
            ->type('@content-editor .tiptap-content', 'Bold text')
            ->click('@tiptap-italic-button')
            ->type('@content-editor .tiptap-content', ' and italic text')
            ->click('@save-button')
            ->waitUntilMissing('@page-form-modal');
    });

    $page = CampaignPage::where('title', 'Rich Text Test')->first();
    expect($page->content)->toContain('<strong>Bold text</strong>');
    expect($page->content)->toContain('<em>and italic text</em>');
});

test('form validation works correctly', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    browse(function (Browser $browser) use ($user, $campaign) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->click('@create-page-button')
            ->waitFor('@page-form-modal')
            ->click('@save-button') // Try to save without title
            ->waitFor('.error-message')
            ->assertSee('required')
            ->type('@title-input', str_repeat('a', 201)) // Too long
            ->click('@save-button')
            ->waitFor('.error-message')
            ->assertSee('maximum');
    });
});

test('access level controls work correctly', function () {
    $user = User::factory()->create();
    $player = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player->id]);

    browse(function (Browser $browser) use ($user, $campaign, $player) {
        $browser->loginAs($user)
            ->visit("/campaigns/{$campaign->campaign_code}/pages")
            ->click('@create-page-button')
            ->waitFor('@page-form-modal')
            ->type('@title-input', 'Access Test Page')
            ->select('@access-level-select', 'specific_players')
            ->waitFor('@player-selection')
            ->check("@player-checkbox-{$player->id}")
            ->click('@save-button')
            ->waitUntilMissing('@page-form-modal');
    });

    $page = CampaignPage::where('title', 'Access Test Page')->first();
    expect($page->authorizedUsers)->toHaveCount(1);
    expect($page->authorizedUsers->first()->id)->toBe($player->id);
});
