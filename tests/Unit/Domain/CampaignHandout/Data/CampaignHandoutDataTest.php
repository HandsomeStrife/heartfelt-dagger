<?php

declare(strict_types=1);

use Domain\Campaign\Data\CampaignData;
use Domain\CampaignHandout\Data\CampaignHandoutData;
use Domain\CampaignHandout\Data\CreateCampaignHandoutData;
use Domain\CampaignHandout\Data\UpdateCampaignHandoutData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Data\UserData;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CampaignHandoutData', function () {
    
    describe('data transformation', function () {
        it('creates from campaign handout model', function () {
            $handout = CampaignHandout::factory()->image()->create([
                'title' => 'Test Handout',
                'description' => 'Test Description',
                'file_size' => 1024,
                'metadata' => ['width' => 1920, 'height' => 1080],
            ]);

            $data = CampaignHandoutData::from($handout);

            expect($data->id)->toBe($handout->id);
            expect($data->title)->toBe('Test Handout');
            expect($data->description)->toBe('Test Description');
            expect($data->file_type)->toBe(HandoutFileType::IMAGE);
            expect($data->access_level)->toBe(HandoutAccessLevel::GM_ONLY);
            expect($data->file_size)->toBe(1024);
            expect($data->metadata)->toBe(['width' => 1920, 'height' => 1080]);
            expect($data->campaign)->toBeInstanceOf(CampaignData::class);
            expect($data->creator)->toBeInstanceOf(UserData::class);
            expect($data->authorized_users)->toBeInstanceOf(Collection::class);
        });

        it('creates from array', function () {
            $array = [
                'id' => 1,
                'campaign_id' => 1,
                'creator_id' => 1,
                'title' => 'Test Handout',
                'description' => 'Test Description',
                'file_name' => 'test.jpg',
                'original_file_name' => 'original.jpg',
                'file_path' => 'handouts/test.jpg',
                'file_type' => HandoutFileType::IMAGE,
                'mime_type' => 'image/jpeg',
                'file_size' => 1024,
                'metadata' => ['width' => 1920, 'height' => 1080],
                'access_level' => HandoutAccessLevel::ALL_PLAYERS,
                'is_visible_in_sidebar' => true,
                'display_order' => 0,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $data = CampaignHandoutData::from($array);

            expect($data->id)->toBe(1);
            expect($data->title)->toBe('Test Handout');
            expect($data->file_type)->toBe(HandoutFileType::IMAGE);
            expect($data->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
            expect($data->is_visible_in_sidebar)->toBeTrue();
        });

        it('includes computed properties', function () {
            $handout = CampaignHandout::factory()->image()->create([
                'file_path' => 'handouts/test.jpg',
                'file_size' => 1048576,
                'metadata' => ['width' => 1920, 'height' => 1080],
            ]);

            $data = CampaignHandoutData::from($handout);

            expect($data->file_url)->toBeString();
            expect($data->formatted_file_size)->toBe('1.00 MB');
            expect($data->image_dimensions)->toBe('1920x1080');
        });

        it('handles null metadata gracefully', function () {
            $handout = CampaignHandout::factory()->create(['metadata' => null]);

            $data = CampaignHandoutData::from($handout);

            expect($data->metadata)->toBeNull();
            expect($data->image_dimensions)->toBeNull();
        });
    });

    describe('relationships handling', function () {
        it('includes campaign data when campaign is loaded', function () {
            $handout = CampaignHandout::factory()->create();
            $handout->load('campaign');

            $data = CampaignHandoutData::from($handout);

            expect($data->campaign)->toBeInstanceOf(CampaignData::class);
            expect($data->campaign->id)->toBe($handout->campaign_id);
        });

        it('includes creator data when creator is loaded', function () {
            $handout = CampaignHandout::factory()->create();
            $handout->load('creator');

            $data = CampaignHandoutData::from($handout);

            expect($data->creator)->toBeInstanceOf(UserData::class);
            expect($data->creator->id)->toBe($handout->creator_id);
        });

        it('includes authorized users when relationship is loaded', function () {
            $handout = CampaignHandout::factory()->specificPlayers()->create();
            $users = User::factory()->count(2)->create();
            $handout->authorizedUsers()->attach($users->pluck('id'));
            $handout->load('authorizedUsers');

            $data = CampaignHandoutData::from($handout);

            expect($data->authorized_users)->toHaveCount(2);
            expect($data->authorized_users->first())->toBeInstanceOf(UserData::class);
        });
    });
});

describe('CreateCampaignHandoutData', function () {
    
    describe('validation', function () {
        it('validates required fields', function () {
            $data = [
                'campaign_id' => 1,
                'creator_id' => 1,
                'title' => 'Test Handout',
                'file_name' => 'test.jpg',
                'original_file_name' => 'test.jpg',
                'file_path' => 'handouts/test.jpg',
                'file_type' => HandoutFileType::IMAGE,
                'mime_type' => 'image/jpeg',
                'file_size' => 1024,
                'access_level' => HandoutAccessLevel::GM_ONLY,
            ];

            $createData = CreateCampaignHandoutData::from($data);

            expect($createData->title)->toBe('Test Handout');
            expect($createData->file_type)->toBe(HandoutFileType::IMAGE);
            expect($createData->access_level)->toBe(HandoutAccessLevel::GM_ONLY);
        });

        it('validates title length', function () {
            expect(function () {
                CreateCampaignHandoutData::from([
                    'campaign_id' => 1,
                    'creator_id' => 1,
                    'title' => str_repeat('a', 201),
                    'file_name' => 'test.jpg',
                    'original_file_name' => 'test.jpg',
                    'file_path' => 'handouts/test.jpg',
                    'file_type' => HandoutFileType::IMAGE,
                    'mime_type' => 'image/jpeg',
                    'file_size' => 1024,
                    'access_level' => HandoutAccessLevel::GM_ONLY,
                ]);
            })->toThrow();
        });

        it('validates description length', function () {
            expect(function () {
                CreateCampaignHandoutData::from([
                    'campaign_id' => 1,
                    'creator_id' => 1,
                    'title' => 'Test',
                    'description' => str_repeat('a', 1001),
                    'file_name' => 'test.jpg',
                    'original_file_name' => 'test.jpg',
                    'file_path' => 'handouts/test.jpg',
                    'file_type' => HandoutFileType::IMAGE,
                    'mime_type' => 'image/jpeg',
                    'file_size' => 1024,
                    'access_level' => HandoutAccessLevel::GM_ONLY,
                ]);
            })->toThrow();
        });

        it('accepts optional fields', function () {
            $data = [
                'campaign_id' => 1,
                'creator_id' => 1,
                'title' => 'Test Handout',
                'description' => 'Test Description',
                'file_name' => 'test.jpg',
                'original_file_name' => 'test.jpg',
                'file_path' => 'handouts/test.jpg',
                'file_type' => HandoutFileType::IMAGE,
                'mime_type' => 'image/jpeg',
                'file_size' => 1024,
                'metadata' => ['width' => 1920],
                'access_level' => HandoutAccessLevel::ALL_PLAYERS,
                'is_visible_in_sidebar' => true,
                'is_published' => false,
                'authorized_user_ids' => [1, 2, 3],
            ];

            $createData = CreateCampaignHandoutData::from($data);

            expect($createData->description)->toBe('Test Description');
            expect($createData->metadata)->toBe(['width' => 1920]);
            expect($createData->is_visible_in_sidebar)->toBeTrue();
            expect($createData->authorized_user_ids)->toBe([1, 2, 3]);
        });
    });
});

describe('UpdateCampaignHandoutData', function () {
    
    describe('partial updates', function () {
        it('allows partial field updates', function () {
            $data = [
                'title' => 'Updated Title',
                'access_level' => HandoutAccessLevel::ALL_PLAYERS,
            ];

            $updateData = UpdateCampaignHandoutData::from($data);

            expect($updateData->title)->toBe('Updated Title');
            expect($updateData->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
            expect($updateData->description)->toBeNull();
        });

        it('validates updated title length', function () {
            expect(function () {
                UpdateCampaignHandoutData::from([
                    'title' => str_repeat('a', 201),
                ]);
            })->toThrow();
        });

        it('allows updating authorized user ids', function () {
            $data = [
                'access_level' => HandoutAccessLevel::SPECIFIC_PLAYERS,
                'authorized_user_ids' => [1, 2, 3],
            ];

            $updateData = UpdateCampaignHandoutData::from($data);

            expect($updateData->access_level)->toBe(HandoutAccessLevel::SPECIFIC_PLAYERS);
            expect($updateData->authorized_user_ids)->toBe([1, 2, 3]);
        });

        it('allows toggling boolean fields', function () {
            $data = [
                'is_visible_in_sidebar' => false,
                'is_published' => true,
            ];

            $updateData = UpdateCampaignHandoutData::from($data);

            expect($updateData->is_visible_in_sidebar)->toBeFalse();
            expect($updateData->is_published)->toBeTrue();
        });
    });
});
