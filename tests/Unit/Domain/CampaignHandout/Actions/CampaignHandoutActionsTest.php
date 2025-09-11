<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Actions\CreateCampaignHandoutAction;
use Domain\CampaignHandout\Actions\DeleteCampaignHandoutAction;
use Domain\CampaignHandout\Actions\ToggleHandoutSidebarVisibilityAction;
use Domain\CampaignHandout\Actions\UpdateCampaignHandoutAction;
use Domain\CampaignHandout\Data\CreateCampaignHandoutData;
use Domain\CampaignHandout\Data\UpdateCampaignHandoutData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CreateCampaignHandoutAction', function () {
    
    beforeEach(function () {
        Storage::fake('public');
    });

    it('creates a handout with minimal data', function () {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $data = CreateCampaignHandoutData::from([
            'campaign_id' => $campaign->id,
            'creator_id' => $user->id,
            'title' => 'Test Handout',
            'file_name' => 'test.jpg',
            'original_file_name' => 'test.jpg',
            'file_path' => 'handouts/test.jpg',
            'file_type' => HandoutFileType::IMAGE,
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'access_level' => HandoutAccessLevel::GM_ONLY,
        ]);

        $action = new CreateCampaignHandoutAction();
        $handout = $action->execute($data);

        expect($handout)->toBeInstanceOf(CampaignHandout::class);
        expect($handout->title)->toBe('Test Handout');
        expect($handout->campaign_id)->toBe($campaign->id);
        expect($handout->creator_id)->toBe($user->id);
        expect($handout->file_type)->toBe(HandoutFileType::IMAGE);
        expect($handout->access_level)->toBe(HandoutAccessLevel::GM_ONLY);
    });

    it('creates a handout with full data', function () {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $data = CreateCampaignHandoutData::from([
            'campaign_id' => $campaign->id,
            'creator_id' => $user->id,
            'title' => 'Complete Handout',
            'description' => 'Complete description',
            'file_name' => 'complete.pdf',
            'original_file_name' => 'original.pdf',
            'file_path' => 'handouts/complete.pdf',
            'file_type' => HandoutFileType::PDF,
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'metadata' => ['pages' => 5],
            'access_level' => HandoutAccessLevel::ALL_PLAYERS,
            'is_visible_in_sidebar' => true,
            'is_published' => true,
            'display_order' => 10,
        ]);

        $action = new CreateCampaignHandoutAction();
        $handout = $action->execute($data);

        expect($handout->title)->toBe('Complete Handout');
        expect($handout->description)->toBe('Complete description');
        expect($handout->file_type)->toBe(HandoutFileType::PDF);
        expect($handout->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
        expect($handout->is_visible_in_sidebar)->toBeTrue();
        expect($handout->is_published)->toBeTrue();
        expect($handout->display_order)->toBe(10);
        expect($handout->metadata)->toBe(['pages' => 5]);
    });

    it('creates handout with specific player access', function () {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();
        $player1 = User::factory()->create();
        $player2 = User::factory()->create();

        $data = CreateCampaignHandoutData::from([
            'campaign_id' => $campaign->id,
            'creator_id' => $user->id,
            'title' => 'Secret Handout',
            'file_name' => 'secret.jpg',
            'original_file_name' => 'secret.jpg',
            'file_path' => 'handouts/secret.jpg',
            'file_type' => HandoutFileType::IMAGE,
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'access_level' => HandoutAccessLevel::SPECIFIC_PLAYERS,
            'authorized_user_ids' => [$player1->id, $player2->id],
        ]);

        $action = new CreateCampaignHandoutAction();
        $handout = $action->execute($data);

        expect($handout->access_level)->toBe(HandoutAccessLevel::SPECIFIC_PLAYERS);
        $handout->load('authorizedUsers');
        expect($handout->authorizedUsers)->toHaveCount(2);
        expect($handout->authorizedUsers->pluck('id')->toArray())->toEqual([$player1->id, $player2->id]);
    });

    it('sets default values for optional fields', function () {
        $campaign = Campaign::factory()->create();
        $user = User::factory()->create();

        $data = CreateCampaignHandoutData::from([
            'campaign_id' => $campaign->id,
            'creator_id' => $user->id,
            'title' => 'Default Handout',
            'file_name' => 'default.jpg',
            'original_file_name' => 'default.jpg',
            'file_path' => 'handouts/default.jpg',
            'file_type' => HandoutFileType::IMAGE,
            'mime_type' => 'image/jpeg',
            'file_size' => 1024,
            'access_level' => HandoutAccessLevel::GM_ONLY,
        ]);

        $action = new CreateCampaignHandoutAction();
        $handout = $action->execute($data);

        expect($handout->description)->toBeNull();
        expect($handout->is_visible_in_sidebar)->toBeFalse();
        expect($handout->is_published)->toBeTrue();
        expect($handout->display_order)->toBe(0);
        expect($handout->metadata)->toBeNull();
    });
});

describe('UpdateCampaignHandoutAction', function () {
    
    it('updates handout title and description', function () {
        $handout = CampaignHandout::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old Description',
        ]);

        $data = UpdateCampaignHandoutData::from([
            'id' => $handout->id,
            'title' => 'New Title',
            'description' => 'New Description',
        ]);

        $action = new UpdateCampaignHandoutAction();
        $updatedHandout = $action->execute($data);

        expect($updatedHandout->title)->toBe('New Title');
        expect($updatedHandout->description)->toBe('New Description');
    });

    it('updates access level and visibility', function () {
        $handout = CampaignHandout::factory()->gmOnly()->create([
            'is_visible_in_sidebar' => false,
        ]);

        $data = UpdateCampaignHandoutData::from([
            'id' => $handout->id,
            'access_level' => HandoutAccessLevel::ALL_PLAYERS,
            'is_visible_in_sidebar' => true,
        ]);

        $action = new UpdateCampaignHandoutAction();
        $updatedHandout = $action->execute($data);

        expect($updatedHandout->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
        expect($updatedHandout->is_visible_in_sidebar)->toBeTrue();
    });

    it('updates authorized users for specific players access', function () {
        $handout = CampaignHandout::factory()->specificPlayers()->create();
        $oldUser = User::factory()->create();
        $newUser1 = User::factory()->create();
        $newUser2 = User::factory()->create();
        
        $handout->authorizedUsers()->attach($oldUser->id);

        $data = UpdateCampaignHandoutData::from([
            'id' => $handout->id,
            'access_level' => HandoutAccessLevel::SPECIFIC_PLAYERS,
            'authorized_user_ids' => [$newUser1->id, $newUser2->id],
        ]);

        $action = new UpdateCampaignHandoutAction();
        $updatedHandout = $action->execute($data);

        expect($updatedHandout->authorized_users)->toHaveCount(2);
        expect($updatedHandout->authorized_users->pluck('id')->toArray())->toEqual([$newUser1->id, $newUser2->id]);
    });

    it('clears authorized users when changing from specific players', function () {
        $handout = CampaignHandout::factory()->specificPlayers()->create();
        $user = User::factory()->create();
        $handout->authorizedUsers()->attach($user->id);

        $data = UpdateCampaignHandoutData::from([
            'id' => $handout->id,
            'access_level' => HandoutAccessLevel::ALL_PLAYERS,
        ]);

        $action = new UpdateCampaignHandoutAction();
        $updatedHandout = $action->execute($data);

        expect($updatedHandout->access_level)->toBe(HandoutAccessLevel::ALL_PLAYERS);
        expect($updatedHandout->authorized_users)->toHaveCount(0);
    });

    it('preserves fields not included in update data', function () {
        $handout = CampaignHandout::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
            'is_published' => true,
        ]);

        $data = UpdateCampaignHandoutData::from([
            'id' => $handout->id,
            'title' => 'Updated Title',
        ]);

        $action = new UpdateCampaignHandoutAction();
        $updatedHandout = $action->execute($data);

        expect($updatedHandout->title)->toBe('Updated Title');
        expect($updatedHandout->description)->toBe('Original Description');
        expect($updatedHandout->is_published)->toBeTrue();
    });
});

describe('DeleteCampaignHandoutAction', function () {
    
    beforeEach(function () {
        Storage::fake('public');
    });

    it('deletes handout and removes file', function () {
        $user = User::factory()->create();
        $handout = CampaignHandout::factory()->create([
            'file_path' => 'handouts/test.jpg',
            'creator_id' => $user->id,
        ]);
        
        // Create a fake file to simulate the existence
        Storage::disk('public')->put($handout->file_path, 'fake content');
        expect(Storage::disk('public')->exists($handout->file_path))->toBeTrue();

        $action = new DeleteCampaignHandoutAction();
        $action->execute($handout->id, $user);

        expect(CampaignHandout::find($handout->id))->toBeNull();
        expect(Storage::disk('public')->exists($handout->file_path))->toBeFalse();
    });

    it('deletes handout even if file does not exist', function () {
        $user = User::factory()->create();
        $handout = CampaignHandout::factory()->create([
            'file_path' => 'handouts/nonexistent.jpg',
            'creator_id' => $user->id,
        ]);

        $action = new DeleteCampaignHandoutAction();
        $action->execute($handout->id, $user);

        expect(CampaignHandout::find($handout->id))->toBeNull();
    });

    it('removes authorized user relationships', function () {
        $user = User::factory()->create();
        $handout = CampaignHandout::factory()->specificPlayers()->create(['creator_id' => $user->id]);
        $authorizedUser = User::factory()->create();
        $handout->authorizedUsers()->attach($authorizedUser->id);

        expect($handout->authorizedUsers)->toHaveCount(1);

        $action = new DeleteCampaignHandoutAction();
        $action->execute($handout->id, $user);

        // Check that the pivot table entry is removed
        expect(DB::table('campaign_handout_access')->where('campaign_handout_id', $handout->id)->count())->toBe(0);
    });
});

describe('ToggleHandoutSidebarVisibilityAction', function () {
    
    it('toggles sidebar visibility from false to true', function () {
        $user = User::factory()->create();
        $handout = CampaignHandout::factory()->create([
            'is_visible_in_sidebar' => false,
            'creator_id' => $user->id,
        ]);

        $action = new ToggleHandoutSidebarVisibilityAction();
        $updatedHandout = $action->execute($handout->id, $user);

        expect($updatedHandout->is_visible_in_sidebar)->toBeTrue();
    });

    it('toggles sidebar visibility from true to false', function () {
        $user = User::factory()->create();
        $handout = CampaignHandout::factory()->visibleInSidebar()->create(['creator_id' => $user->id]);

        $action = new ToggleHandoutSidebarVisibilityAction();
        $updatedHandout = $action->execute($handout->id, $user);

        expect($updatedHandout->is_visible_in_sidebar)->toBeFalse();
    });

    it('persists the change to database', function () {
        $user = User::factory()->create();
        $handout = CampaignHandout::factory()->create([
            'is_visible_in_sidebar' => false,
            'creator_id' => $user->id,
        ]);

        $action = new ToggleHandoutSidebarVisibilityAction();
        $action->execute($handout->id, $user);

        $freshHandout = CampaignHandout::find($handout->id);
        expect($freshHandout->is_visible_in_sidebar)->toBeTrue();
    });
});
