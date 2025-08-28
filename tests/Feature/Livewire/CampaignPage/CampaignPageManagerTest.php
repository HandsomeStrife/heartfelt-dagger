<?php

declare(strict_types=1);

use App\Livewire\CampaignPage\CampaignPageManager;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs};
use function Pest\Livewire\livewire;


it('can render campaign page manager', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->assertSuccessful()
        ->assertSee('Campaign Pages')
        ->assertSee('Organize and manage your campaign lore');
});


it('loads pages on mount', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    $page1 = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
    $page2 = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

    actingAs($user);

    $component = livewire(CampaignPageManager::class, ['campaign' => $campaign]);
    
    expect($component->get('pages'))->toHaveCount(2);
});


it('shows_create button for campaign creators', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->assertSee('Create Page');
});


it('hides_create button for non creators', function ()
{
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $member->id]);

    actingAs($member);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->assertDontSee('Create Page');
});


it('can switch view modes', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->assertSet('view_mode', 'hierarchy')
        ->call('setViewMode', 'list')
        ->assertSet('view_mode', 'list');
});


it('can search pages', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    $dragonPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Dragon Lair',
        'content' => '<p>Ancient red dragon</p>',
    ]);
    $villagePage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Village',
        'content' => '<p>Peaceful village</p>',
    ]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->set('search_query', 'dragon')
        ->call('search')
        ->assertSet('view_mode', 'search');
});


it('can filter by categories', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    $npcPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs', 'Villains'],
    ]);
    $lorePage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Lore'],
    ]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->call('toggleCategory', 'NPCs')
        ->assertSet('selected_categories', ['NPCs']);
});


it('can clear search filters', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->set('search_query', 'test')
        ->set('selected_categories', ['NPCs'])
        ->set('view_mode', 'search')
        ->call('clearSearch')
        ->assertSet('search_query', '')
        ->assertSet('selected_categories', [])
        ->assertSet('view_mode', 'hierarchy');
});


it('can create page', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->call('createPage')
        ->assertSet('show_form', true)
        ->assertSet('editing_page', null);
});


it('can create child page', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $parentPage = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->call('createPage', $parentPage->id)
        ->assertSet('show_form', true)
        ->assertDispatched('set-parent');
});


it('can edit page', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $page = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->call('editPage', $page->id)
        ->assertSet('show_form', true)
        ->assertSet('editing_page.id', $page->id);
});


it('can delete page', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $page = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->call('deletePage', $page->id)
        ->assertDispatched('page-deleted');

    expect(CampaignPage::find($page->id))->toBeNull();
});


it('prevents_non authorized users from deleting', function ()
{
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    $page = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    actingAs($otherUser);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->call('deletePage', $page->id)
        ->assertHasErrors(['permission']);

    expect(CampaignPage::find($page->id))->not->toBeNull();
});


it('can reorder pages', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    $page1 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'display_order' => 1,
    ]);
    $page2 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'display_order' => 2,
    ]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->call('reorderPages', [$page2->id, $page1->id]) // Reverse order
        ->assertHasNoErrors();

    $page1->refresh();
    $page2->refresh();
    
    expect($page2->display_order)->toBe(1);
    expect($page1->display_order)->toBe(2);
});


it('refreshes pages on form events', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    $component = livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->set('show_form', true)
        ->call('refreshPages')
        ->assertSet('show_form', false);
});


it('closes form', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $page = CampaignPage::factory()->create(['campaign_id' => $campaign->id]);

    actingAs($user);

    livewire(CampaignPageManager::class, ['campaign' => $campaign])
        ->set('show_form', true)
        ->set('editing_page', $page)
        ->call('closeForm')
        ->assertSet('show_form', false)
        ->assertSet('editing_page', null);
});


it('loads campaign statistics', function ()
{
    $user = User::factory()->create();
    $player = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player->id]);
    
    $page1 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['NPCs'],
    ]);
    $page2 = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'category_tags' => ['Lore'],
    ]);

    actingAs($user);

    $component = livewire(CampaignPageManager::class, ['campaign' => $campaign]);
    
    expect($component->get('available_categories'))->toHaveCount(2);
    expect($component->get('campaign_members'))->toHaveCount(1);
});


it('respects_access permissions for page visibility', function ()
{
    $creator = User::factory()->create();
    $player = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player->id]);
    
    $gmPage = CampaignPage::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
    $playersPage = CampaignPage::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

    // Creator should see all pages
    actingAs($creator);
    $creatorComponent = livewire(CampaignPageManager::class, ['campaign' => $campaign]);
    expect($creatorComponent->get('pages'))->toHaveCount(2);

    // Player should only see accessible pages
    actingAs($player);
    $playerComponent = livewire(CampaignPageManager::class, ['campaign' => $campaign]);
    expect($playerComponent->get('pages'))->toHaveCount(1);
});
