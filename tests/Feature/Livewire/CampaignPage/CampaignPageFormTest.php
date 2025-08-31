<?php

declare(strict_types=1);

use App\Livewire\CampaignPage\CampaignPageForm;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs};
use function Pest\Livewire\livewire;


it('can render create form', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->assertSuccessful()
        ->assertSee('Create Campaign Page')
        ->assertSee('Page Title')
        ->assertSee('Access Level');
});


it('can render edit form', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $user->id,
        'title' => 'Test Page',
        'content' => '<p>Test content</p>',
    ]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign, 'page' => $page])
        ->assertSuccessful()
        ->assertSee('Edit Campaign Page')
        ->assertSet('form.title', 'Test Page')
        ->assertSet('form.content', '<p>Test content</p>');
});


it('validates required fields', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->set('form.title', '')
        ->call('save')
        ->assertHasErrors(['form.title']);
});


it('validates title length', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->set('form.title', str_repeat('a', 201)) // Exceeds 200 character limit
        ->call('save')
        ->assertHasErrors(['form.title']);
});


it('creates page successfully', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->set('form.title', 'Test Page')
        ->set('form.content', '<p>Test content</p>')
        ->set('form.category_tags', ['Lore', 'NPCs'])
        ->set('form.access_level', 'all_players')
        ->set('form.is_published', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('page-saved');

    expect(CampaignPage::where('title', 'Test Page')->exists())->toBeTrue();
    
    $page = CampaignPage::where('title', 'Test Page')->first();
    expect($page->campaign_id)->toBe($campaign->id);
    expect($page->creator_id)->toBe($user->id);
    expect($page->content)->toBe('<p>Test content</p>');
    expect($page->category_tags)->toBe(['Lore', 'NPCs']);
    expect($page->access_level)->toBe(PageAccessLevel::ALL_PLAYERS);
});


it('updates page successfully', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $user->id,
        'title' => 'Original Title',
        'content' => '<p>Original content</p>',
    ]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign, 'page' => $page])
        ->set('form.title', 'Updated Title')
        ->set('form.content', '<p>Updated content</p>')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('page-saved');

    $page->refresh();
    expect($page->title)->toBe('Updated Title');
    expect($page->content)->toBe('<p>Updated content</p>');
});


it('can add category tags', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->call('addCategoryTag', 'NPCs')
        ->call('addCategoryTag', 'Villains')
        ->assertSet('form.category_tags', ['NPCs', 'Villains']);
});


it('can remove category tags', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->set('form.category_tags', ['NPCs', 'Villains', 'Lore'])
        ->call('removeCategoryTag', 1) // Remove 'Villains'
        ->assertSet('form.category_tags', ['NPCs', 'Lore']);
});


it('does_not add duplicate category tags', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->call('addCategoryTag', 'NPCs')
        ->call('addCategoryTag', 'NPCs') // Duplicate
        ->assertSet('form.category_tags', ['NPCs']);
});


it('handles specific player access', function ()
{
    $user = User::factory()->create();
    $player1 = User::factory()->create();
    $player2 = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player1->id]);
    CampaignMember::create(['campaign_id' => $campaign->id, 'user_id' => $player2->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->set('form.title', 'Secret Page')
        ->set('form.access_level', 'specific_players')
        ->set('form.authorized_user_ids', [$player1->id])
        ->call('save')
        ->assertHasNoErrors();

    $page = CampaignPage::where('title', 'Secret Page')->first();
    expect($page->authorizedUsers)->toHaveCount(1);
    expect($page->authorizedUsers->first()->id)->toBe($player1->id);
});


it('loads parent page options', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
    
    $rootPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'title' => 'Root Page',
    ]);
    $childPage = CampaignPage::factory()->allPlayers()->create([
        'campaign_id' => $campaign->id,
        'parent_id' => $rootPage->id,
        'title' => 'Child Page',
    ]);

    actingAs($user);

    $component = livewire(CampaignPageForm::class, ['campaign' => $campaign]);
    
    expect($component->get('parentPageOptions'))->toContain([
        'value' => null,
        'label' => 'No Parent (Root Level)'
    ]);
    expect($component->get('parentPageOptions'))->toContain([
        'value' => $rootPage->id,
        'label' => 'Root Page'
    ]);
    expect($component->get('parentPageOptions'))->toContain([
        'value' => $childPage->id,
        'label' => '— Child Page'
    ]);
});


it('prevents_non authorized users from editing', function ()
{
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $creator->id,
    ]);

    actingAs($otherUser);

    livewire(CampaignPageForm::class, ['campaign' => $campaign, 'page' => $page])
        ->assertStatus(403);
});


it('campaign_creator can edit any page', function ()
{
    $campaignCreator = User::factory()->create();
    $pageCreator = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $campaignCreator->id]);
    $page = CampaignPage::factory()->create([
        'campaign_id' => $campaign->id,
        'creator_id' => $pageCreator->id,
        'title' => 'Test Page',
    ]);

    actingAs($campaignCreator);

    livewire(CampaignPageForm::class, ['campaign' => $campaign, 'page' => $page])
        ->assertSuccessful()
        ->assertSet('form.title', 'Test Page');
});


it('can cancel form', function ()
{
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    actingAs($user);

    livewire(CampaignPageForm::class, ['campaign' => $campaign])
        ->set('form.title', 'Test Page')
        ->call('cancel')
        ->assertDispatched('form-cancelled');
});
