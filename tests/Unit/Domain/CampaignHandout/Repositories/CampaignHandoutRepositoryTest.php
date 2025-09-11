<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Data\CampaignHandoutData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\CampaignHandout\Repositories\CampaignHandoutRepository;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('CampaignHandoutRepository', function () {
    
    beforeEach(function () {
        $this->repository = new CampaignHandoutRepository();
    });

    describe('getForCampaign', function () {
        it('returns all handouts for a campaign', function () {
            $campaign = Campaign::factory()->create();
            $handout1 = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);
            $handout2 = CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);
            $handout3 = CampaignHandout::factory()->create(); // Different campaign

            $handouts = $this->repository->getForCampaign($campaign);

            expect($handouts)->toBeInstanceOf(Collection::class);
            expect($handouts)->toHaveCount(2);
            expect($handouts->first())->toBeInstanceOf(CampaignHandoutData::class);
            
            $ids = $handouts->pluck('id')->toArray();
            expect($ids)->toContain($handout1->id);
            expect($ids)->toContain($handout2->id);
            expect($ids)->not->toContain($handout3->id);
        });

        it('orders handouts by display order and created date', function () {
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

            $handouts = $this->repository->getForCampaign($campaign);

            // Should be ordered by display_order ASC, then created_at DESC
            expect($handouts->first()->id)->toBe($handout2->id); // display_order 0
            expect($handouts->get(1)->id)->toBe($handout3->id);  // display_order 5
            expect($handouts->last()->id)->toBe($handout1->id);  // display_order 10
        });

        it('filters by search query when provided', function () {
            $campaign = Campaign::factory()->create();
            
            $handout1 = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Magic Items List',
                'description' => 'A list of magical artifacts',
            ]);
            
            $handout2 = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Character Portrait',
                'description' => 'Main character artwork',
            ]);
            
            $handout3 = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Map of the Region',
                'description' => 'Detailed regional map',
            ]);

            $handouts = $this->repository->getForCampaign($campaign, 'magic');

            expect($handouts)->toHaveCount(1);
            expect($handouts->first()->id)->toBe($handout1->id);
        });

        it('filters by file type when provided', function () {
            $campaign = Campaign::factory()->create();
            
            $imageHandout = CampaignHandout::factory()->image()->create(['campaign_id' => $campaign->id]);
            $pdfHandout = CampaignHandout::factory()->pdf()->create(['campaign_id' => $campaign->id]);

            $handouts = $this->repository->getForCampaign($campaign, null, 'image');

            expect($handouts)->toHaveCount(1);
            expect($handouts->first()->id)->toBe($imageHandout->id);
        });

        it('filters by access level when provided', function () {
            $campaign = Campaign::factory()->create();
            
            $gmOnlyHandout = CampaignHandout::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
            $allPlayersHandout = CampaignHandout::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

            $handouts = $this->repository->getForCampaign($campaign, null, null, 'gm_only');

            expect($handouts)->toHaveCount(1);
            expect($handouts->first()->id)->toBe($gmOnlyHandout->id);
        });

        it('combines multiple filters', function () {
            $campaign = Campaign::factory()->create();
            
            $targetHandout = CampaignHandout::factory()->image()->gmOnly()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Secret Map',
            ]);
            
            CampaignHandout::factory()->pdf()->gmOnly()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Secret Document',
            ]);
            
            CampaignHandout::factory()->image()->allPlayers()->create([
                'campaign_id' => $campaign->id,
                'title' => 'Public Map',
            ]);

            $handouts = $this->repository->getForCampaign($campaign, 'map', 'image', 'gm_only');

            expect($handouts)->toHaveCount(1);
            expect($handouts->first()->id)->toBe($targetHandout->id);
        });

        it('returns empty collection when no handouts match filters', function () {
            $campaign = Campaign::factory()->create();
            CampaignHandout::factory()->create(['campaign_id' => $campaign->id]);

            $handouts = $this->repository->getForCampaign($campaign, 'nonexistent');

            expect($handouts)->toHaveCount(0);
        });
    });

    describe('getVisibleInSidebar', function () {
        it('returns handouts visible in sidebar for GM', function () {
            $campaign = Campaign::factory()->create();
            $gm = User::factory()->create();
            $campaign->update(['creator_id' => $gm->id]);

            $visibleGmHandout = CampaignHandout::factory()->gmOnly()->visibleInSidebar()->create(['campaign_id' => $campaign->id]);
            $visibleAllHandout = CampaignHandout::factory()->allPlayers()->visibleInSidebar()->create(['campaign_id' => $campaign->id]);
            $hiddenHandout = CampaignHandout::factory()->create([
                'campaign_id' => $campaign->id,
                'is_visible_in_sidebar' => false,
            ]);

            $handouts = $this->repository->getVisibleInSidebar($campaign, $gm);

            expect($handouts)->toHaveCount(2);
            
            $ids = $handouts->pluck('id')->toArray();
            expect($ids)->toContain($visibleGmHandout->id);
            expect($ids)->toContain($visibleAllHandout->id);
            expect($ids)->not->toContain($hiddenHandout->id);
        });

        it('returns only accessible handouts for regular player', function () {
            $campaign = Campaign::factory()->create();
            $player = User::factory()->create();
            $campaign->members()->create(['user_id' => $player->id]);

            $gmOnlyHandout = CampaignHandout::factory()->gmOnly()->visibleInSidebar()->create(['campaign_id' => $campaign->id]);
            $allPlayersHandout = CampaignHandout::factory()->allPlayers()->visibleInSidebar()->create(['campaign_id' => $campaign->id]);
            $specificHandout = CampaignHandout::factory()->specificPlayers()->visibleInSidebar()->create(['campaign_id' => $campaign->id]);
            $specificHandout->authorizedUsers()->attach($player->id);

            $handouts = $this->repository->getVisibleInSidebar($campaign, $player);

            expect($handouts)->toHaveCount(2);
            
            $ids = $handouts->pluck('id')->toArray();
            expect($ids)->toContain($allPlayersHandout->id);
            expect($ids)->toContain($specificHandout->id);
            expect($ids)->not->toContain($gmOnlyHandout->id);
        });

        it('excludes unpublished handouts', function () {
            $campaign = Campaign::factory()->create();
            $gm = User::factory()->create();
            $campaign->update(['creator_id' => $gm->id]);

            $publishedHandout = CampaignHandout::factory()->visibleInSidebar()->create([
                'campaign_id' => $campaign->id,
                'is_published' => true,
            ]);
            
            $unpublishedHandout = CampaignHandout::factory()->visibleInSidebar()->create([
                'campaign_id' => $campaign->id,
                'is_published' => false,
            ]);

            $handouts = $this->repository->getVisibleInSidebar($campaign, $gm);

            expect($handouts)->toHaveCount(1);
            expect($handouts->first()->id)->toBe($publishedHandout->id);
        });

        it('returns empty collection for non-member', function () {
            $campaign = Campaign::factory()->create();
            $nonMember = User::factory()->create();

            CampaignHandout::factory()->allPlayers()->visibleInSidebar()->create(['campaign_id' => $campaign->id]);

            $handouts = $this->repository->getVisibleInSidebar($campaign, $nonMember);

            expect($handouts)->toHaveCount(0);
        });

        it('orders by display order and creation date', function () {
            $campaign = Campaign::factory()->create();
            $gm = User::factory()->create();
            $campaign->update(['creator_id' => $gm->id]);

            $handout1 = CampaignHandout::factory()->visibleInSidebar()->create([
                'campaign_id' => $campaign->id,
                'display_order' => 5,
                'created_at' => now()->subDay(),
            ]);
            
            $handout2 = CampaignHandout::factory()->visibleInSidebar()->create([
                'campaign_id' => $campaign->id,
                'display_order' => 0,
                'created_at' => now(),
            ]);

            $handouts = $this->repository->getVisibleInSidebar($campaign, $gm);

            expect($handouts->first()->id)->toBe($handout2->id); // display_order 0 comes first
            expect($handouts->last()->id)->toBe($handout1->id);  // display_order 5 comes second
        });
    });

    describe('getAccessibleByUser', function () {
        it('returns all handouts for campaign creator', function () {
            $campaign = Campaign::factory()->create();
            $creator = User::factory()->create();
            $campaign->update(['creator_id' => $creator->id]);

            $gmHandout = CampaignHandout::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
            $allHandout = CampaignHandout::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
            $specificHandout = CampaignHandout::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);

            $handouts = $this->repository->getAccessibleByUser($campaign, $creator);

            expect($handouts)->toHaveCount(3);
        });

        it('returns only accessible handouts for regular player', function () {
            $campaign = Campaign::factory()->create();
            $player = User::factory()->create();
            $campaign->members()->create(['user_id' => $player->id]);

            $gmHandout = CampaignHandout::factory()->gmOnly()->create(['campaign_id' => $campaign->id]);
            $allHandout = CampaignHandout::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);
            $specificHandout = CampaignHandout::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);
            $specificHandout->authorizedUsers()->attach($player->id);
            $otherSpecificHandout = CampaignHandout::factory()->specificPlayers()->create(['campaign_id' => $campaign->id]);

            $handouts = $this->repository->getAccessibleByUser($campaign, $player);

            expect($handouts)->toHaveCount(2);
            
            $ids = $handouts->pluck('id')->toArray();
            expect($ids)->toContain($allHandout->id);
            expect($ids)->toContain($specificHandout->id);
            expect($ids)->not->toContain($gmHandout->id);
            expect($ids)->not->toContain($otherSpecificHandout->id);
        });

        it('returns empty collection for non-member', function () {
            $campaign = Campaign::factory()->create();
            $nonMember = User::factory()->create();

            CampaignHandout::factory()->allPlayers()->create(['campaign_id' => $campaign->id]);

            $handouts = $this->repository->getAccessibleByUser($campaign, $nonMember);

            expect($handouts)->toHaveCount(0);
        });

        it('excludes unpublished handouts for non-creators', function () {
            $campaign = Campaign::factory()->create();
            $player = User::factory()->create();
            $campaign->members()->create(['user_id' => $player->id]);

            $publishedHandout = CampaignHandout::factory()->allPlayers()->create([
                'campaign_id' => $campaign->id,
                'is_published' => true,
            ]);
            
            $unpublishedHandout = CampaignHandout::factory()->allPlayers()->create([
                'campaign_id' => $campaign->id,
                'is_published' => false,
            ]);

            $handouts = $this->repository->getAccessibleByUser($campaign, $player);

            expect($handouts)->toHaveCount(1);
            expect($handouts->first()->id)->toBe($publishedHandout->id);
        });
    });

    describe('findById', function () {
        it('returns handout data by id when exists', function () {
            $handout = CampaignHandout::factory()->create();

            $result = $this->repository->findById($handout->id);

            expect($result)->toBeInstanceOf(CampaignHandoutData::class);
            expect($result->id)->toBe($handout->id);
        });

        it('returns null when handout does not exist', function () {
            $result = $this->repository->findById(99999);

            expect($result)->toBeNull();
        });
    });
});
