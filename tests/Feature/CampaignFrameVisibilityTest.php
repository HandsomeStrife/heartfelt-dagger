<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\CampaignFrame\Models\CampaignFrameVisibility;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

describe('Campaign Frame Visibility', function () {

    it('shows default visible sections to players', function () {
        $creator = User::factory()->create();
        $player = User::factory()->create();

        $frame = CampaignFrame::create([
            'name' => 'Test Frame',
            'description' => 'Test frame description',
            'complexity_rating' => 1,
            'is_public' => true,
            'creator_id' => $creator->id,
            'pitch' => ['An exciting adventure awaits!'],
            'touchstones' => [],
            'tone' => ['heroic'],
            'themes' => ['adventure'],
            'player_principles' => ['Be heroic', 'Save the day'],
            'gm_principles' => [],
            'community_guidance' => [],
            'ancestry_guidance' => [],
            'class_guidance' => [],
            'background_overview' => 'A world of wonder and danger.',
            'setting_guidance' => [],
            'setting_distinctions' => [],
            'inciting_incident' => '',
            'special_mechanics' => [],
            'campaign_mechanics' => [],
            'session_zero_questions' => [],
        ]);

        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'campaign_frame_id' => $frame->id,
        ]);

        $campaign->members()->create(['user_id' => $player->id]);

        // Test that default visible sections are available
        expect($campaign->isCampaignFrameSectionVisible('pitch'))->toBeTrue();
        expect($campaign->isCampaignFrameSectionVisible('tone'))->toBeTrue();
        expect($campaign->isCampaignFrameSectionVisible('themes'))->toBeTrue();
        expect($campaign->isCampaignFrameSectionVisible('player_principles'))->toBeTrue();
        expect($campaign->isCampaignFrameSectionVisible('background_overview'))->toBeTrue();
        expect($campaign->isCampaignFrameSectionVisible('gm_principles'))->toBeFalse();
    });

    it('allows creators to see all sections', function () {
        $creator = User::factory()->create();

        $frame = CampaignFrame::create([
            'name' => 'Test Frame',
            'description' => 'Test frame description',
            'complexity_rating' => 1,
            'is_public' => true,
            'creator_id' => $creator->id,
            'pitch' => [],
            'touchstones' => [],
            'tone' => [],
            'themes' => [],
            'player_principles' => [],
            'gm_principles' => ['GM-only principle'],
            'community_guidance' => [],
            'ancestry_guidance' => [],
            'class_guidance' => [],
            'background_overview' => '',
            'setting_guidance' => [],
            'setting_distinctions' => [],
            'inciting_incident' => '',
            'special_mechanics' => [],
            'campaign_mechanics' => [],
            'session_zero_questions' => [],
        ]);

        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'campaign_frame_id' => $frame->id,
        ]);

        $allSections = array_keys(CampaignFrameVisibility::getAvailableSections());
        $visibleSections = $campaign->getVisibleCampaignFrameSections($creator);

        expect($visibleSections)->toBe($allSections);
    });

    it('allows custom visibility settings', function () {
        $creator = User::factory()->create();
        $player = User::factory()->create();

        $frame = CampaignFrame::create([
            'name' => 'Test Frame',
            'description' => 'Test frame description',
            'complexity_rating' => 1,
            'is_public' => true,
            'creator_id' => $creator->id,
            'pitch' => [],
            'touchstones' => [],
            'tone' => [],
            'themes' => [],
            'player_principles' => [],
            'gm_principles' => ['Special GM principle'],
            'community_guidance' => [],
            'ancestry_guidance' => [],
            'class_guidance' => [],
            'background_overview' => '',
            'setting_guidance' => [],
            'setting_distinctions' => [],
            'inciting_incident' => '',
            'special_mechanics' => [],
            'campaign_mechanics' => [],
            'session_zero_questions' => [],
        ]);

        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'campaign_frame_id' => $frame->id,
        ]);

        // Override default visibility
        CampaignFrameVisibility::create([
            'campaign_id' => $campaign->id,
            'section_name' => 'gm_principles',
            'is_visible_to_players' => true,
        ]);

        expect($campaign->isCampaignFrameSectionVisible('gm_principles'))->toBeTrue();
    });

    it('displays campaign show page with frame content', function () {
        $creator = User::factory()->create();

        $frame = CampaignFrame::create([
            'name' => 'Test Frame',
            'description' => 'Test frame description',
            'complexity_rating' => 1,
            'is_public' => true,
            'creator_id' => $creator->id,
            'pitch' => ['An exciting adventure awaits!'],
            'touchstones' => [],
            'tone' => [],
            'themes' => [],
            'player_principles' => [],
            'gm_principles' => [],
            'community_guidance' => [],
            'ancestry_guidance' => [],
            'class_guidance' => [],
            'background_overview' => '',
            'setting_guidance' => [],
            'setting_distinctions' => [],
            'inciting_incident' => '',
            'special_mechanics' => [],
            'campaign_mechanics' => [],
            'session_zero_questions' => [],
        ]);

        $campaign = Campaign::factory()->create([
            'creator_id' => $creator->id,
            'campaign_frame_id' => $frame->id,
        ]);

        actingAs($creator)
            ->get(route('campaigns.show', ['campaign' => $campaign->campaign_code]))
            ->assertOk()
            ->assertSee('Test Frame')
            ->assertSee('Campaign Setting Guide');
    });
});
