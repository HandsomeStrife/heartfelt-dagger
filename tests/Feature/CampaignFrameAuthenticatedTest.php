<?php

declare(strict_types=1);

use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('authenticated user can view campaign frames index', function () {
    $user = User::factory()->create();

    actingAs($user)->get('/campaign-frames')
        ->assertStatus(200)
        ->assertSee('Campaign Frames');
});

test('authenticated user can view enhanced campaign frame details', function () {
    $user = User::factory()->create();

    $frame = CampaignFrame::create([
        'name' => 'Enhanced Test Frame',
        'description' => 'Testing enhanced display functionality',
        'complexity_rating' => 2,
        'is_public' => true,
        'creator_id' => $user->id,
        'pitch' => ['Epic adventure awaits', 'Heroes must unite'],
        'touchstones' => ['Lord of the Rings', 'Game of Thrones'],
        'tone' => ['Epic', 'Dark', 'Heroic'],
        'themes' => ['Good vs Evil', 'Power Corrupts', 'Redemption'],
        'player_principles' => ['Stay true to your character', 'Support the party'],
        'gm_principles' => ['Challenge the players', 'Show consequences'],
        'community_guidance' => ['Highborne value honor and tradition'],
        'ancestry_guidance' => ['Elves are long-lived and wise'],
        'class_guidance' => ['Warriors protect the innocent'],
        'background_overview' => 'A rich fantasy world filled with ancient magic and political intrigue where heroes must navigate complex moral choices.',
        'setting_guidance' => ['Magic has a price', 'History shapes the present'],
        'setting_distinctions' => ['Ancient ruins dot the landscape', 'Multiple kingdoms vie for power'],
        'inciting_incident' => 'The High King has been assassinated by unknown forces, throwing the realm into chaos and uncertainty.',
        'special_mechanics' => [],
        'campaign_mechanics' => ['Political intrigue affects story outcomes', 'Magic corruption system'],
        'session_zero_questions' => ['What drives your character to adventure?', 'What are you most afraid of losing?'],
    ]);

    actingAs($user)->get("/campaign-frames/{$frame->id}")
        ->assertStatus(200)
        ->assertSee('Enhanced Test Frame')
        ->assertSee('Testing enhanced display functionality')
        ->assertSee('Moderate')
        ->assertSee('Complexity')
        ->assertSee('Epic adventure awaits')
        ->assertSee('Heroes must unite')
        ->assertSee('Lord of the Rings')
        ->assertSee('Game of Thrones')
        ->assertSee('Epic')
        ->assertSee('Dark')
        ->assertSee('Good vs Evil')
        ->assertSee('Power Corrupts')
        ->assertSee('Stay true to your character')
        ->assertSee('Challenge the players')
        ->assertSee('Highborne value honor')
        ->assertSee('Elves are long-lived')
        ->assertSee('Warriors protect the innocent')
        ->assertSee('A rich fantasy world filled with ancient magic')
        ->assertSee('Magic has a price')
        ->assertSee('Ancient ruins dot the landscape')
        ->assertSee('The High King has been assassinated')
        ->assertSee('Political intrigue affects story')
        ->assertSee('What drives your character to adventure?');
});

test('authenticated user can create campaign frame', function () {
    $user = User::factory()->create();

    actingAs($user)->get('/campaign-frames/create')
        ->assertStatus(200)
        ->assertSee('Create Campaign Frame');
});

test('authenticated user can edit own campaign frame', function () {
    $user = User::factory()->create();

    $frame = CampaignFrame::create([
        'name' => 'My Test Frame',
        'description' => 'Testing edit functionality',
        'complexity_rating' => 1,
        'is_public' => false,
        'creator_id' => $user->id,
        'pitch' => ['Test pitch'],
        'touchstones' => ['Test touchstone'],
        'tone' => ['Test tone'],
        'themes' => ['Test theme'],
        'player_principles' => ['Test player principle'],
        'gm_principles' => ['Test gm principle'],
        'community_guidance' => ['Test community guidance'],
        'ancestry_guidance' => ['Test ancestry guidance'],
        'class_guidance' => ['Test class guidance'],
        'background_overview' => 'Test background',
        'setting_guidance' => ['Test setting guidance'],
        'setting_distinctions' => ['Test setting distinction'],
        'inciting_incident' => 'Test inciting incident',
        'special_mechanics' => [],
        'campaign_mechanics' => ['Test campaign mechanic'],
        'session_zero_questions' => ['Test question?'],
    ]);

    actingAs($user)->get("/campaign-frames/{$frame->id}/edit")
        ->assertStatus(200)
        ->assertSee('Edit Campaign Frame')
        ->assertSee('My Test Frame');
});

test('authenticated user cannot edit others campaign frame', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $frame = CampaignFrame::create([
        'name' => 'Others Frame',
        'description' => 'Not mine',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $owner->id,
        'pitch' => [],
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

    actingAs($otherUser)->get("/campaign-frames/{$frame->id}/edit")
        ->assertStatus(403);
});

test('unauthenticated user cannot access campaign frames', function () {
    get('/campaign-frames')
        ->assertRedirect('/login');

    get('/campaign-frames/create')
        ->assertRedirect('/login');
});
