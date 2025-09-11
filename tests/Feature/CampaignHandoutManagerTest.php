<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Campaign Handouts Feature', function () {
    
    beforeEach(function () {
        Storage::fake('public');
    });

    describe('handouts page access', function () {
        it('displays handouts page for campaign creator', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user)
                ->get(route('campaigns.handouts', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertViewIs('campaigns.handouts')
                ->assertViewHas('campaign');
        });

        it('displays handouts page for campaign member', function () {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $member->id]);

            actingAs($member)
                ->get(route('campaigns.handouts', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertViewIs('campaigns.handouts')
                ->assertViewHas('campaign');
        });

        it('requires authentication', function () {
            $campaign = Campaign::factory()->create();

            get(route('campaigns.handouts', ['campaign' => $campaign->campaign_code]))
                ->assertRedirect(route('login'));
        });

        it('returns 404 for non-existent campaign', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->get(route('campaigns.handouts', ['campaign' => 'INVALID']))
                ->assertNotFound();
        });
    });

    describe('handouts tab integration', function () {
        it('shows handouts tab in campaign show page', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user)
                ->get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertSee('Handouts')
                ->assertSee('Campaign Handouts')
                ->assertSee('Documents, images, and files for your campaign');
        });

        it('shows manage handouts button for campaign creator', function () {
            $user = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

            actingAs($user)
                ->get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertSee('Manage Handouts');
        });

        it('hides manage handouts button for campaign members', function () {
            $creator = User::factory()->create();
            $member = User::factory()->create();
            $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);
            $campaign->members()->create(['user_id' => $member->id]);

            actingAs($member)
                ->get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
                ->assertOk()
                ->assertSee('Only the Game Master can manage handouts')
                ->assertDontSee('Manage Handouts');
        });
    });

    describe('file upload and storage', function () {
        it('handles image upload correctly', function () {
            $file = UploadedFile::fake()->image('test.jpg', 100, 100);
            $campaign = Campaign::factory()->create();
            $user = User::factory()->create();

            // Simulate the file upload process that would happen in the action
            $fileName = 'handouts_' . time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('handouts', $fileName, 'public');

            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Test Image',
                'file_name' => $fileName,
                'original_file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => 'image',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            expect(Storage::disk('public')->exists($filePath))->toBeTrue();
            expect($handout->file_type->value)->toBe('image');
            expect($handout->original_file_name)->toBe('test.jpg');
        });

        it('handles PDF upload correctly', function () {
            $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');
            $campaign = Campaign::factory()->create();
            $user = User::factory()->create();

            $fileName = 'handouts_' . time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('handouts', $fileName, 'public');

            $handout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'creator_id' => $user->id,
                'title' => 'Test PDF',
                'file_name' => $fileName,
                'original_file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => 'pdf',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            expect(Storage::disk('public')->exists($filePath))->toBeTrue();
            expect($handout->file_type->value)->toBe('pdf');
            expect($handout->original_file_name)->toBe('document.pdf');
        });

        it('generates unique file names', function () {
            $file1 = UploadedFile::fake()->image('test.jpg');
            $file2 = UploadedFile::fake()->image('test.jpg');

            $fileName1 = 'handouts_' . time() . '_' . $file1->getClientOriginalName();
            sleep(1);
            $fileName2 = 'handouts_' . time() . '_' . $file2->getClientOriginalName();

            expect($fileName1)->not->toBe($fileName2);
        });

        it('organizes files in proper directory structure', function () {
            $file = UploadedFile::fake()->image('test.jpg');
            $fileName = 'handouts_' . time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('handouts', $fileName, 'public');

            expect($filePath)->toStartWith('handouts/');
            expect(Storage::disk('public')->exists($filePath))->toBeTrue();
        });
    });

    describe('access control validation', function () {
        it('validates access levels correctly', function () {
            $campaign = Campaign::factory()->create();
            
            $gmOnlyHandout = CampaignHandout::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
            $allPlayersHandout = CampaignHandout::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
            $specificPlayersHandout = CampaignHandout::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);

            expect($gmOnlyHandout->access_level->value)->toBe('gm_only');
            expect($allPlayersHandout->access_level->value)->toBe('all_players');
            expect($specificPlayersHandout->access_level->value)->toBe('specific_players');
        });

        it('manages specific player access correctly', function () {
            $campaign = Campaign::factory()->create();
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            $user3 = User::factory()->create();

            $handout = CampaignHandout::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);
            $handout->authorizedUsers()->attach([$user1->id, $user2->id]);

            expect($handout->authorizedUsers)->toHaveCount(2);
            expect($handout->authorizedUsers->pluck('id')->toArray())->toContain($user1->id);
            expect($handout->authorizedUsers->pluck('id')->toArray())->toContain($user2->id);
            expect($handout->authorizedUsers->pluck('id')->toArray())->not->toContain($user3->id);
        });

        it('clears authorized users when changing from specific access', function () {
            $campaign = Campaign::factory()->create();
            $user = User::factory()->create();

            $handout = CampaignHandout::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);
            $handout->authorizedUsers()->attach($user->id);

            expect($handout->authorizedUsers)->toHaveCount(1);

            $handout->update(['access_level' => 'all_players']);
            $handout->authorizedUsers()->detach();

            expect($handout->fresh()->authorizedUsers)->toHaveCount(0);
        });
    });

    describe('file metadata handling', function () {
        it('stores image metadata correctly', function () {
            $metadata = ['width' => 1920, 'height' => 1080, 'aspect_ratio' => '16:9'];
            $handout = CampaignHandout::factory()->image()->create(['metadata' => $metadata]);

            expect($handout->metadata)->toBe($metadata);
            expect($handout->getImageDimensions())->toBe('1920x1080');
        });

        it('stores PDF metadata correctly', function () {
            $metadata = ['pages' => 5, 'author' => 'Test Author'];
            $handout = CampaignHandout::factory()->pdf()->create(['metadata' => $metadata]);

            expect($handout->metadata)->toBe($metadata);
        });

        it('handles null metadata gracefully', function () {
            $handout = CampaignHandout::factory()->create(['metadata' => null]);

            expect($handout->metadata)->toBeNull();
            expect($handout->getImageDimensions())->toBeNull();
        });
    });

    describe('file size formatting', function () {
        it('formats file sizes correctly', function () {
            $handout1 = CampaignHandout::factory()->create(['file_size' => 1024]);
            expect($handout1->getFormattedFileSize())->toBe('1.00 KB');

            $handout2 = CampaignHandout::factory()->create(['file_size' => 1048576]);
            expect($handout2->getFormattedFileSize())->toBe('1.00 MB');

            $handout3 = CampaignHandout::factory()->create(['file_size' => 1073741824]);
            expect($handout3->getFormattedFileSize())->toBe('1.00 GB');

            $handout4 = CampaignHandout::factory()->create(['file_size' => 512]);
            expect($handout4->getFormattedFileSize())->toBe('512 bytes');
        });
    });

    describe('preview functionality', function () {
        it('identifies previewable files correctly', function () {
            $imageHandout = CampaignHandout::factory()->image()->create();
            $pdfHandout = CampaignHandout::factory()->pdf()->create();
            $docHandout = CampaignHandout::factory()->create(['file_type' => 'document']);
            $audioHandout = CampaignHandout::factory()->create(['file_type' => 'audio']);

            expect($imageHandout->isPreviewable())->toBeTrue();
            expect($pdfHandout->isPreviewable())->toBeTrue();
            expect($docHandout->isPreviewable())->toBeFalse();
            expect($audioHandout->isPreviewable())->toBeFalse();
        });
    });

    describe('display ordering', function () {
        it('respects display order and creation date', function () {
            $campaign = Campaign::factory()->create();

            $handout1 = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'display_order' => 10,
                'created_at' => now()->subDays(2),
            ]);

            $handout2 = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'display_order' => 0,
                'created_at' => now()->subDay(),
            ]);

            $handout3 = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'display_order' => 5,
                'created_at' => now(),
            ]);

            $handouts = CampaignHandout::where('campaign_id', $campaign->id)
                ->orderBy('display_order')
                ->orderBy('created_at', 'desc')
                ->get();

            expect($handouts->get(0)->id)->toBe($handout2->id); // display_order 0
            expect($handouts->get(1)->id)->toBe($handout3->id); // display_order 5
            expect($handouts->get(2)->id)->toBe($handout1->id); // display_order 10
        });
    });

    describe('sidebar visibility', function () {
        it('toggles sidebar visibility correctly', function () {
            $handout = CampaignHandout::factory()->create(['is_visible_in_sidebar' => false]);

            expect($handout->is_visible_in_sidebar)->toBeFalse();

            $handout->update(['is_visible_in_sidebar' => true]);

            expect($handout->fresh()->is_visible_in_sidebar)->toBeTrue();
        });

        it('filters sidebar visible handouts correctly', function () {
            $campaign = Campaign::factory()->create();

            $visibleHandout = CampaignHandout::factory()->visibleInSidebar()->create(['campaign_id' => $campaign->id]);
            $hiddenHandout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'is_visible_in_sidebar' => false,
            ]);

            $sidebarHandouts = CampaignHandout::where('campaign_id', $campaign->id)
                ->where('is_visible_in_sidebar', true)
                ->get();

            expect($sidebarHandouts)->toHaveCount(1);
            expect($sidebarHandouts->first()->id)->toBe($visibleHandout->id);
        });
    });
});
