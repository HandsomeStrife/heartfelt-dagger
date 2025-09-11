<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CampaignHandout Model', function () {
    
    describe('relationships', function () {
        it('belongs to a campaign', function () {
            $campaign = Campaign::factory()->create();
            $handout = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);

            expect($handout->campaign)->toBeInstanceOf(Campaign::class);
            expect($handout->campaign->id)->toBe($campaign->id);
        });

        it('belongs to a creator user', function () {
            $user = User::factory()->create();
            $handout = CampaignHandout::factory()->create(['creator_id' => $user->id]);

            expect($handout->creator)->toBeInstanceOf(User::class);
            expect($handout->creator->id)->toBe($user->id);
        });

        it('has many authorized users', function () {
            $handout = CampaignHandout::factory()->specificPlayers()->create();
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            
            $handout->authorizedUsers()->attach([$user1->id, $user2->id]);

            expect($handout->authorizedUsers)->toHaveCount(2);
            expect($handout->authorizedUsers->pluck('id')->toArray())->toEqual([$user1->id, $user2->id]);
        });
    });

    describe('casts and attributes', function () {
        it('casts access_level to enum', function () {
            $handout = CampaignHandout::factory()->create(['access_level' => 'gm_only']);

            expect($handout->access_level)->toBeInstanceOf(HandoutAccessLevel::class);
            expect($handout->access_level)->toBe(HandoutAccessLevel::GM_ONLY);
        });

        it('casts file_type to enum', function () {
            $handout = CampaignHandout::factory()->image()->create();

            expect($handout->file_type)->toBeInstanceOf(HandoutFileType::class);
            expect($handout->file_type)->toBe(HandoutFileType::IMAGE);
        });

        it('casts metadata to array', function () {
            $metadata = ['width' => 1920, 'height' => 1080];
            $handout = CampaignHandout::factory()->create(['metadata' => $metadata]);

            expect($handout->metadata)->toBeArray();
            expect($handout->metadata)->toBe($metadata);
        });

        it('casts boolean fields correctly', function () {
            $handout = CampaignHandout::factory()->create([
                'is_visible_in_sidebar' => true,
                'is_published' => false,
            ]);

            expect($handout->is_visible_in_sidebar)->toBeBool();
            expect($handout->is_visible_in_sidebar)->toBeTrue();
            expect($handout->is_published)->toBeBool();
            expect($handout->is_published)->toBeFalse();
        });
    });

    describe('helper methods', function () {
        beforeEach(function () {
            Storage::fake('public');
        });

        it('generates file url for public storage', function () {
            config(['filesystems.default' => 'public']);
            $handout = CampaignHandout::factory()->create(['file_path' => 'handouts/test.jpg']);

            $url = $handout->getFileUrl();

            expect($url)->toContain('/storage/handouts/test.jpg');
        });

        it('generates file url for s3 storage', function () {
            config(['filesystems.default' => 's3']);
            $handout = CampaignHandout::factory()->create(['file_path' => 'handouts/test.jpg']);

            $url = $handout->getFileUrl();

            // Should contain S3 temporary URL
            expect($url)->toBeString();
        });

        it('formats file size correctly', function () {
            $handout = CampaignHandout::factory()->create(['file_size' => 1024]);
            expect($handout->getFormattedFileSize())->toBe('1.00 KB');

            $handout = CampaignHandout::factory()->create(['file_size' => 1048576]);
            expect($handout->getFormattedFileSize())->toBe('1.00 MB');

            $handout = CampaignHandout::factory()->create(['file_size' => 1073741824]);
            expect($handout->getFormattedFileSize())->toBe('1.00 GB');
        });

        it('extracts image dimensions from metadata', function () {
            $handout = CampaignHandout::factory()->create([
                'metadata' => ['width' => 1920, 'height' => 1080],
            ]);

            $dimensions = $handout->getImageDimensions();

            expect($dimensions)->toBe('1920x1080');
        });

        it('returns null for image dimensions when no metadata', function () {
            $handout = CampaignHandout::factory()->create(['metadata' => null]);

            expect($handout->getImageDimensions())->toBeNull();
        });

        it('returns null for image dimensions when missing width/height', function () {
            $handout = CampaignHandout::factory()->create(['metadata' => ['other' => 'data']]);

            expect($handout->getImageDimensions())->toBeNull();
        });

        it('determines if file is previewable', function () {
            $imageHandout = CampaignHandout::factory()->image()->create();
            expect($imageHandout->isPreviewable())->toBeTrue();

            $pdfHandout = CampaignHandout::factory()->pdf()->create();
            expect($pdfHandout->isPreviewable())->toBeTrue();

            $docHandout = CampaignHandout::factory()->create(['file_type' => HandoutFileType::DOCUMENT]);
            expect($docHandout->isPreviewable())->toBeFalse();
        });
    });

    describe('validation and constraints', function () {
        it('requires all mandatory fields', function () {
            expect(fn () => CampaignHandout::create([]))->toThrow(\Illuminate\Database\QueryException::class);
        });

        it('validates file_type enum values', function () {
            $handout = CampaignHandout::factory()->make(['file_type' => 'invalid_type']);

            expect(fn () => $handout->save())->toThrow();
        });

        it('validates access_level enum values', function () {
            $handout = CampaignHandout::factory()->make(['access_level' => 'invalid_level']);

            expect(fn () => $handout->save())->toThrow();
        });

        it('validates title length', function () {
            $handout = CampaignHandout::factory()->make(['title' => str_repeat('a', 201)]);

            expect(fn () => $handout->save())->toThrow();
        });

        it('validates file_path length', function () {
            $handout = CampaignHandout::factory()->make(['file_path' => str_repeat('a', 501)]);

            expect(fn () => $handout->save())->toThrow();
        });
    });

    describe('scopes and queries', function () {
        it('can be queried by campaign', function () {
            $campaign1 = Campaign::factory()->create();
            $campaign2 = Campaign::factory()->create();
            
            $handout1 = CampaignHandout::factory()->create(['campaign_id' => $campaign1->id]);
            $handout2 = CampaignHandout::factory()->create(['campaign_id' => $campaign2->id]);

            $campaign1Handouts = CampaignHandout::where('campaign_id', $campaign1->id)->get();

            expect($campaign1Handouts)->toHaveCount(1);
            expect($campaign1Handouts->first()->id)->toBe($handout1->id);
        });

        it('can be queried by access level', function () {
            $gmOnlyHandout = CampaignHandout::factory()->gmOnly()->create();
            $allPlayersHandout = CampaignHandout::factory()->allPlayers()->create();

            $gmOnlyHandouts = CampaignHandout::where('access_level', HandoutAccessLevel::GM_ONLY)->get();
            $allPlayersHandouts = CampaignHandout::where('access_level', HandoutAccessLevel::ALL_PLAYERS)->get();

            expect($gmOnlyHandouts)->toHaveCount(1);
            expect($gmOnlyHandouts->first()->id)->toBe($gmOnlyHandout->id);
            expect($allPlayersHandouts)->toHaveCount(1);
            expect($allPlayersHandouts->first()->id)->toBe($allPlayersHandout->id);
        });

        it('can be queried by sidebar visibility', function () {
            $visibleHandout = CampaignHandout::factory()->visibleInSidebar()->create();
            $hiddenHandout = CampaignHandout::factory()->create(['is_visible_in_sidebar' => false]);

            $visibleHandouts = CampaignHandout::where('is_visible_in_sidebar', true)->get();

            expect($visibleHandouts)->toHaveCount(1);
            expect($visibleHandouts->first()->id)->toBe($visibleHandout->id);
        });
    });

    describe('factory states', function () {
        it('creates image handouts with proper attributes', function () {
            $handout = CampaignHandout::factory()->image()->create();

            expect($handout->file_type)->toBe(HandoutFileType::IMAGE);
            expect($handout->mime_type)->toContain('image/');
        });

        it('creates pdf handouts with proper attributes', function () {
            $handout = CampaignHandout::factory()->pdf()->create();

            expect($handout->file_type)->toBe(HandoutFileType::PDF);
            expect($handout->mime_type)->toBe('application/pdf');
        });

        it('creates gm only handouts', function () {
            $handout = CampaignHandout::factory()->gmOnly()->create();

            expect($handout->access_level)->toBe(HandoutAccessLevel::GM_ONLY);
        });

        it('creates all players handouts', function () {
            $handout = CampaignHandout::factory()->allPlayers()->create();

            expect($handout->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
        });

        it('creates specific players handouts', function () {
            $handout = CampaignHandout::factory()->specificPlayers()->create();

            expect($handout->access_level)->toBe(HandoutAccessLevel::SPECIFIC_PLAYERS);
        });

        it('creates sidebar visible handouts', function () {
            $handout = CampaignHandout::factory()->visibleInSidebar()->create();

            expect($handout->is_visible_in_sidebar)->toBeTrue();
        });
    });
});
