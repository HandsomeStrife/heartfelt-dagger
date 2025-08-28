<?php

declare(strict_types=1);

use Domain\CampaignFrame\Data\CreateCampaignFrameData;
use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('can create enhanced campaign frame with all new fields', function () {
    $user = User::factory()->create();
    $campaign_frame_data = CreateCampaignFrameData::from([
        'name' => 'The Witherwild Enhanced',
        'description' => 'A test of the enhanced campaign frame system',
        'complexity_rating' => ComplexityRating::MODERATE,
        'is_public' => true,
        'pitch' => [
            'Fanewick was once a place of great abundance and peace',
            'When Haven invaded the wilds, a dangerous bloom took hold'
        ],
        'touchstones' => [
            'Princess Mononoke',
            'The Legend of Zelda',
            'The Dark Crystal'
        ],
        'tone' => [
            'Adventurous',
            'Dynamic',
            'Epic',
            'Heroic'
        ],
        'themes' => [
            'Cultural Clash',
            'People vs. Nature',
            'Transformation and Change'
        ],
        'player_principles' => [
            'Make the invasion personal',
            'Treat death with importance',
            'Embrace vulnerability'
        ],
        'gm_principles' => [
            'Paint the world in contrast',
            'Show them true danger',
            'Offer alternatives to violence'
        ],
        'community_guidance' => [
            'Loreborne communities value knowledge above all',
            'Ridgeborne are hardy mountain dwellers'
        ],
        'ancestry_guidance' => [
            'Fungril have grown larger since the Witherwild',
            'Drakona horns grow faster in this corrupted land'
        ],
        'class_guidance' => [
            'Druids feel the corruption of nature deeply',
            'Warriors often come from Haven\'s army'
        ],
        'background_overview' => 'Fanewick is a wild and untamed land, long avoided by outside forces...',
        'setting_guidance' => [
            'Focus on the contrast between nature and civilization',
            'Emphasize the cost of magical intervention'
        ],
        'setting_distinctions' => [
            'The weeks of day and night',
            'The Serpent\'s Sickness plague',
            'The ever-growing Witherwild'
        ],
        'inciting_incident' => 'The Reaping Eye is stowed in a secure vault beneath Haven\'s wizarding school...',
        'special_mechanics' => [
            'Corruption from the Witherwild'
        ],
        'campaign_mechanics' => [
            'Wither tokens accumulate from Withered adversaries',
            'Weekly day/night cycles affect gameplay'
        ],
        'session_zero_questions' => [
            'What dangerous animal comes out during the week of night?',
            'What superstitions does your character have about traversing Fanewick?'
        ]
    ]);

    expect($campaign_frame_data->name)->toBe('The Witherwild Enhanced');
    expect($campaign_frame_data->touchstones)->toHaveCount(3);
    expect($campaign_frame_data->tone)->toHaveCount(4);
    expect($campaign_frame_data->themes)->toHaveCount(3);
    expect($campaign_frame_data->player_principles)->toHaveCount(3);
    expect($campaign_frame_data->gm_principles)->toHaveCount(3);
    expect($campaign_frame_data->community_guidance)->toHaveCount(2);
    expect($campaign_frame_data->ancestry_guidance)->toHaveCount(2);
    expect($campaign_frame_data->class_guidance)->toHaveCount(2);
    expect($campaign_frame_data->campaign_mechanics)->toHaveCount(2);
    expect($campaign_frame_data->session_zero_questions)->toHaveCount(2);
});

test('campaign frame model properly casts all enhanced fields', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::create([
        'name' => 'Test Enhanced Frame',
        'description' => 'Testing enhanced fields',
        'complexity_rating' => 2,
        'is_public' => true,
        'creator_id' => $user->id,
        'pitch' => ['Test pitch point'],
        'touchstones' => ['Test Movie', 'Test Game'],
        'tone' => ['Epic', 'Heroic'],
        'themes' => ['Good vs Evil'],
        'player_principles' => ['Be heroic'],
        'gm_principles' => ['Create drama'],
        'community_guidance' => ['Test guidance'],
        'ancestry_guidance' => ['Test ancestry info'],
        'class_guidance' => ['Test class info'],
        'background_overview' => 'Test background',
        'setting_guidance' => ['Test setting guidance'],
        'setting_distinctions' => ['Test distinction'],
        'inciting_incident' => 'Test incident',
        'special_mechanics' => ['Test special mechanic'],
        'campaign_mechanics' => ['Test campaign mechanic'],
        'session_zero_questions' => ['Test question?']
    ]);

    expect($frame->touchstones)->toBeArray()->toHaveCount(2);
    expect($frame->tone)->toBeArray()->toHaveCount(2);
    expect($frame->themes)->toBeArray()->toHaveCount(1);
    expect($frame->player_principles)->toBeArray()->toHaveCount(1);
    expect($frame->gm_principles)->toBeArray()->toHaveCount(1);
    expect($frame->community_guidance)->toBeArray()->toHaveCount(1);
    expect($frame->ancestry_guidance)->toBeArray()->toHaveCount(1);
    expect($frame->class_guidance)->toBeArray()->toHaveCount(1);
    expect($frame->campaign_mechanics)->toBeArray()->toHaveCount(1);
    expect($frame->session_zero_questions)->toBeArray()->toHaveCount(1);
});

test('campaign frame data object correctly transforms from model', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::create([
        'name' => 'Transform Test Frame',
        'description' => 'Testing data transformation',
        'complexity_rating' => 3,
        'is_public' => false,
        'creator_id' => $user->id,
        'pitch' => ['Pitch 1', 'Pitch 2'],
        'touchstones' => ['Movie A', 'Game B', 'Book C'],
        'tone' => ['Dark', 'Mysterious'],
        'themes' => ['Betrayal', 'Redemption'],
        'player_principles' => ['Stay true to character'],
        'gm_principles' => ['Maintain tension'],
        'community_guidance' => ['Guidance for community A'],
        'ancestry_guidance' => ['Info about ancestry B'],
        'class_guidance' => ['Notes for class C'],
        'background_overview' => 'Rich background story here',
        'setting_guidance' => ['Setting note 1'],
        'setting_distinctions' => ['Unique feature 1'],
        'inciting_incident' => 'The spark that starts it all',
        'special_mechanics' => ['Legacy mechanic'],
        'campaign_mechanics' => ['New enhanced mechanic'],
        'session_zero_questions' => ['What drives your character?']
    ]);

    $frame_data = \Domain\CampaignFrame\Data\CampaignFrameData::fromModel($frame);

    expect($frame_data->name)->toBe('Transform Test Frame');
    expect($frame_data->complexity_rating)->toBe(ComplexityRating::COMPLEX);
    expect($frame_data->touchstones)->toHaveCount(3);
    expect($frame_data->tone)->toHaveCount(2);
    expect($frame_data->themes)->toHaveCount(2);
    expect($frame_data->player_principles)->toHaveCount(1);
    expect($frame_data->gm_principles)->toHaveCount(1);
    expect($frame_data->community_guidance)->toHaveCount(1);
    expect($frame_data->ancestry_guidance)->toHaveCount(1);
    expect($frame_data->class_guidance)->toHaveCount(1);
    expect($frame_data->campaign_mechanics)->toHaveCount(1);
    expect($frame_data->session_zero_questions)->toHaveCount(1);
    expect($frame_data->background_overview)->toBe('Rich background story here');
    expect($frame_data->inciting_incident)->toBe('The spark that starts it all');
});
