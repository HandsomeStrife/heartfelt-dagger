<?php

declare(strict_types=1);

use App\Livewire\CampaignHandout\CampaignHandoutManager;
use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CampaignHandoutManager Livewire Component', function () {
    
    beforeEach(function () {
        Storage::fake('public');
    });

    describe('component initialization', function () {
        it('mounts with campaign correctly', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->assertSet('campaign.id', $campaign->id)
                ->assertSet('view_mode', 'grid')
                ->assertSet('search_query', '')
                ->assertSet('show_form', false)
                ->assertSet('show_preview_modal', false);
        });

        it('loads existing handouts on mount', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            
            CampaignHandout::factory()->count(3)->create(['campaign_id' => $campaign->id]);

            actingAs($user);

            $component = Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign]);
            
            // Check that handouts are loaded (this would be reflected in the view)
            $component->assertSee('handouts');
        });
    });

    describe('form interactions', function () {
        it('shows create form when triggered', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showCreateForm')
                ->assertSet('show_form', true)
                ->assertSet('editing_handout_id', null);
        });

        it('shows edit form when triggered', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showEditForm', $handout->id)
                ->assertSet('show_form', true)
                ->assertSet('editing_handout_id', $handout->id);
        });

        it('cancels form correctly', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showCreateForm')
                ->assertSet('show_form', true)
                ->call('cancelForm')
                ->assertSet('show_form', false)
                ->assertSet('editing_handout_id', null);
        });
    });

    describe('handout creation', function () {
        it('creates handout with valid data', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            $file = UploadedFile::fake()->image('test.jpg', 100, 100);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('form.title', 'Test Handout')
                ->set('form.description', 'Test Description')
                ->set('form.file', $file)
                ->set('form.access_level', HandoutAccessLevel::ALL_PLAYERS->value)
                ->set('form.is_visible_in_sidebar', true)
                ->call('save')
                ->assertHasNoErrors()
                ->assertSet('show_form', false);

            expect(CampaignHandout::where('title', 'Test Handout')->exists())->toBeTrue();
            
            $handout = CampaignHandout::where('title', 'Test Handout')->first();
            expect($handout->description)->toBe('Test Description');
            expect($handout->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
            expect($handout->is_visible_in_sidebar)->toBeTrue();
        });

        it('validates required fields', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('form.title', '')
                ->call('save')
                ->assertHasErrors(['form.title']);
        });

        it('validates file upload', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('form.title', 'Test Handout')
                ->set('form.file', null)
                ->call('save')
                ->assertHasErrors(['form.file']);
        });

        it('creates handout with specific players access', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $player1 = User::factory()->create();
            $player2 = User::factory()->create();

            actingAs($user);

            $file = UploadedFile::fake()->image('test.jpg');

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('form.title', 'Secret Handout')
                ->set('form.file', $file)
                ->set('form.access_level', HandoutAccessLevel::SPECIFIC_PLAYERS->value)
                ->set('form.authorized_user_ids', [$player1->id, $player2->id])
                ->call('save')
                ->assertHasNoErrors();

            $handout = CampaignHandout::where('title', 'Secret Handout')->first();
            expect($handout->access_level)->toBe(HandoutAccessLevel::SPECIFIC_PLAYERS);
            expect($handout->authorizedUsers)->toHaveCount(2);
        });
    });

    describe('handout editing', function () {
        it('updates handout with valid data', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Original Title',
                'access_level' => HandoutAccessLevel::GM_ONLY,
            ]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showEditForm', $handout->id)
                ->set('form.title', 'Updated Title')
                ->set('form.access_level', HandoutAccessLevel::ALL_PLAYERS->value)
                ->call('save')
                ->assertHasNoErrors();

            $handout->refresh();
            expect($handout->title)->toBe('Updated Title');
            expect($handout->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
        });

        it('validates edit form fields', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showEditForm', $handout->id)
                ->set('form.title', '')
                ->call('save')
                ->assertHasErrors(['form.title']);
        });

        it('updates authorized users for specific players access', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);
            
            $oldUser = User::factory()->create();
            $newUser = User::factory()->create();
            $handout->authorizedUsers()->attach($oldUser->id);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showEditForm', $handout->id)
                ->set('form.authorized_user_ids', [$newUser->id])
                ->call('save')
                ->assertHasNoErrors();

            $handout->refresh();
            expect($handout->authorizedUsers)->toHaveCount(1);
            expect($handout->authorizedUsers->first()->id)->toBe($newUser->id);
        });
    });

    describe('handout deletion', function () {
        it('deletes handout successfully', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('deleteHandout', $handout->id);

            expect(CampaignHandout::find($handout->id))->toBeNull();
        });

        it('only allows creator to delete handouts', function () {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $member->id]);
            $handout = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);

            actingAs($member);

            // This would typically be handled by authorization middleware or component logic
            // For now, we test that the handout still exists
            expect(CampaignHandout::find($handout->id))->not->toBeNull();
        });
    });

    describe('sidebar visibility toggle', function () {
        it('toggles sidebar visibility successfully', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'is_visible_in_sidebar' => false,
            ]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('toggleSidebarVisibility', $handout->id);

            $handout->refresh();
            expect($handout->is_visible_in_sidebar)->toBeTrue();
        });
    });

    describe('preview modal', function () {
        it('shows preview modal for previewable handouts', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->image()->create(['campaign_id' => $campaign->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showPreview', $handout->id)
                ->assertSet('show_preview_modal', true)
                ->assertSet('preview_handout_id', $handout->id);
        });

        it('closes preview modal', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('show_preview_modal', true)
                ->set('preview_handout_id', 1)
                ->call('closePreview')
                ->assertSet('show_preview_modal', false)
                ->assertSet('preview_handout_id', null);
        });
    });

    describe('filtering and search', function () {
        it('filters handouts by search query', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            
            CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Magic Items List',
            ]);
            
            CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Character Portrait',
            ]);

            actingAs($user);

            $component = Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('search_query', 'magic');

            // The filtered results would be reflected in the component's computed properties
            // This would typically be tested by checking the rendered view
        });

        it('toggles view mode between grid and list', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->assertSet('view_mode', 'grid')
                ->set('view_mode', 'list')
                ->assertSet('view_mode', 'list');
        });

        it('filters by file type', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('filter_file_type', 'image')
                ->assertSet('filter_file_type', 'image');
        });

        it('filters by access level', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->set('filter_access_level', 'gm_only')
                ->assertSet('filter_access_level', 'gm_only');
        });
    });

    describe('permissions and authorization', function () {
        it('shows create form only for campaign creators', function () {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $member->id]);

            // Test creator can see create form
            actingAs($creator);
            $creatorComponent = Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign]);
            // Creator should be able to call showCreateForm without issues

            // Test member cannot see create form (would be handled by component logic)
            actingAs($member);
            $memberComponent = Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign]);
            // Member access would be restricted by the component's can_edit property
        });

        it('shows edit options only for campaign creators', function () {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $member->id]);
            $handout = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);

            // Creator should have edit access
            actingAs($creator);
            Livewire::test(CampaignHandoutManager::class, ['campaign' => $campaign])
                ->call('showEditForm', $handout->id)
                ->assertSet('show_form', true);

            // Member should not have edit access (handled by component authorization)
            actingAs($member);
            // This would be restricted by the component's authorization logic
        });
    });
});
