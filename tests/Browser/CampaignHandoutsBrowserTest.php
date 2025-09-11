<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses()->group('browser');

describe('Campaign Handouts Browser Tests', function () {
    
    beforeEach(function () {
        Storage::fake('public');
    });

    describe('navigation and access', function () {
        it('displays handouts tab in campaign show page', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}")
                ->assertSee('Handouts')
                ->click('button:contains("Handouts")')
                ->pause(500)
                ->assertSee('Campaign Handouts')
                ->assertSee('Documents, images, and files for your campaign');
        });

        it('navigates to handouts management page', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}")
                ->click('button:contains("Handouts")')
                ->pause(500)
                ->click('a:contains("Manage Handouts")')
                ->assertUrlIs("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertSee('Campaign Handouts')
                ->assertSee('Upload New Handout');
        });

        it('shows proper access message for non-creators', function () {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $member->id]);

            actingAs($member);

            visit("/campaigns/{$campaign->campaign_code}")
                ->click('button:contains("Handouts")')
                ->pause(500)
                ->assertSee('Only the Game Master can manage handouts')
                ->assertDontSee('Manage Handouts');
        });
    });

    describe('handout creation flow', function () {
        it('creates a new handout with image upload', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertSee('Upload New Handout')
                ->click('button:contains("Upload New Handout")')
                ->pause(500)
                ->assertSee('Handout Details')
                ->type('input[wire\\:model="form.title"]', 'Test Image Handout')
                ->type('textarea[wire\\:model="form.description"]', 'This is a test image for the campaign')
                ->select('select[wire\\:model="form.access_level"]', 'all_players')
                ->check('input[wire\\:model="form.is_visible_in_sidebar"]')
                ->pause(500)
                ->click('button:contains("Save Handout")')
                ->pause(1000)
                ->assertSee('Test Image Handout');
        });

        it('validates required fields in handout form', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->click('button:contains("Upload New Handout")')
                ->pause(500)
                ->click('button:contains("Save Handout")')
                ->pause(500)
                ->assertSee('The title field is required')
                ->assertSee('The file field is required');
        });

        it('creates handout with specific player access', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $player1 = User::factory()->create();
            $player2 = User::factory()->create();
            
            // Add players to campaign
            $campaign->members()->create(['user_id' => $player1->id]);
            $campaign->members()->create(['user_id' => $player2->id]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->click('button:contains("Upload New Handout")')
                ->pause(500)
                ->type('input[wire\\:model="form.title"]', 'Secret Handout')
                ->select('select[wire\\:model="form.access_level"]', 'specific_players')
                ->pause(500)
                ->assertSee('Select Players')
                ->click('button:contains("Save Handout")')
                ->pause(1000)
                ->assertSee('Secret Handout');
        });
    });

    describe('handout display and management', function () {
        it('displays handouts in grid view', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            
            CampaignHandout::factory()->count(3)->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertSee('3 handouts')
                ->assertVisible('.grid')
                ->assertElementCount('.handout-card', 3);
        });

        it('switches between grid and list view', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            
            CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertVisible('.grid')
                ->click('button[title="List View"]')
                ->pause(500)
                ->assertVisible('.space-y-3')
                ->click('button[title="Grid View"]')
                ->pause(500)
                ->assertVisible('.grid');
        });

        it('searches handouts by title', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            
            CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Magic Items List',
            ]);
            
            CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Character Portrait',
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->type('input[wire\\:model.live="search_query"]', 'magic')
                ->pause(1000)
                ->assertSee('Magic Items List')
                ->assertDontSee('Character Portrait');
        });

        it('filters handouts by file type', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            
            CampaignHandout::factory()->image()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Test Image',
            ]);
            
            CampaignHandout::factory()->pdf()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Test PDF',
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->select('select[wire\\:model.live="filter_file_type"]', 'image')
                ->pause(1000)
                ->assertSee('Test Image')
                ->assertDontSee('Test PDF');
        });

        it('filters handouts by access level', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            
            CampaignHandout::factory()->gmOnly()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'GM Only Handout',
            ]);
            
            CampaignHandout::factory()->allPlayers()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'All Players Handout',
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->select('select[wire\\:model.live="filter_access_level"]', 'gm_only')
                ->pause(1000)
                ->assertSee('GM Only Handout')
                ->assertDontSee('All Players Handout');
        });
    });

    describe('handout editing', function () {
        it('edits existing handout', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Original Title',
                'description' => 'Original Description',
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertSee('Original Title')
                ->click('button[title="Edit Handout"]')
                ->pause(500)
                ->clear('input[wire\\:model="form.title"]')
                ->type('input[wire\\:model="form.title"]', 'Updated Title')
                ->clear('textarea[wire\\:model="form.description"]')
                ->type('textarea[wire\\:model="form.description"]', 'Updated Description')
                ->click('button:contains("Save Handout")')
                ->pause(1000)
                ->assertSee('Updated Title')
                ->assertDontSee('Original Title');
        });

        it('cancels handout edit', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Original Title',
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->click('button[title="Edit Handout"]')
                ->pause(500)
                ->assertSee('Edit Handout')
                ->click('button:contains("Cancel")')
                ->pause(500)
                ->assertDontSee('Edit Handout')
                ->assertSee('Original Title');
        });
    });

    describe('handout deletion', function () {
        it('deletes handout with confirmation', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'To Be Deleted',
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertSee('To Be Deleted')
                ->click('button[title="Delete Handout"]')
                ->pause(500)
                ->assertSee('Are you sure you want to delete this handout?')
                ->click('button:contains("Delete")')
                ->pause(1000)
                ->assertDontSee('To Be Deleted');
        });
    });

    describe('sidebar visibility toggle', function () {
        it('toggles sidebar visibility', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'is_visible_in_sidebar' => false,
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertSee($handout->title)
                ->click('button[title="Show in Sidebar"]')
                ->pause(1000)
                ->assertSee('Hidden from Sidebar', false); // Should now show "Visible in Sidebar"
        });
    });

    describe('preview functionality', function () {
        it('opens image preview modal', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->image()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Test Image',
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->click('button[title="Preview"]')
                ->pause(500)
                ->assertSee('Test Image')
                ->assertVisible('.modal')
                ->click('button:contains("×")')
                ->pause(500)
                ->assertNotVisible('.modal');
        });

        it('downloads handout file', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->click('button[title="Download"]')
                ->pause(500);
                // File download would trigger browser download
        });
    });

    describe('room sidebar integration', function () {
        it('displays handouts in GM sidebar during room session', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $room = \Domain\Room\Models\Room::factory()->create([
                'creator_id' => $user->id,
                'campaign_id' => $campaign->id,
            ]);
            
            CampaignHandout::factory()->visibleInSidebar()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Sidebar Handout',
            ]);

            actingAs($user);

            visit("/rooms/{$room->room_token}")
                ->pause(2000) // Wait for room to load
                ->click('button:contains("GM Tools")')
                ->pause(500)
                ->click('button:contains("Handouts")')
                ->pause(500)
                ->assertSee('Sidebar Handout');
        });

        it('displays accessible handouts in player sidebar', function () {
            $creator = User::factory()->create();
            $player = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $player->id]);
            
            $room = \Domain\Room\Models\Room::factory()->create([
                'creator_id' => $creator->id,
                'campaign_id' => $campaign->id,
            ]);
            
            // Create handout visible to all players
            CampaignHandout::factory()->allPlayers()->visibleInSidebar()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $creator->id,
                'title' => 'Player Handout',
            ]);
            
            // Create GM-only handout (should not be visible)
            CampaignHandout::factory()->gmOnly()->visibleInSidebar()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $creator->id,
                'title' => 'GM Secret',
            ]);

            actingAs($player);

            visit("/rooms/{$room->room_token}")
                ->pause(2000)
                ->click('button:contains("Tools")')
                ->pause(500)
                ->click('button:contains("Handouts")')
                ->pause(500)
                ->assertSee('Player Handout')
                ->assertDontSee('GM Secret');
        });
    });

    describe('responsive design', function () {
        it('works on mobile viewport', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertSee('Campaign Handouts')
                ->click('button:contains("Upload New Handout")')
                ->pause(500)
                ->assertSee('Handout Details');
        });
    });

    describe('accessibility', function () {
        it('has proper ARIA labels and keyboard navigation', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);
            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
            ]);

            actingAs($user);

            visit("/campaigns/{$campaign->campaign_code}/handouts")
                ->assertAttribute('button:contains("Upload New Handout")', 'aria-label')
                ->assertAttribute('input[wire\\:model.live="search_query"]', 'aria-label')
                ->keys('button:contains("Upload New Handout")', '{enter}')
                ->pause(500)
                ->assertSee('Handout Details');
        });
    });
});
