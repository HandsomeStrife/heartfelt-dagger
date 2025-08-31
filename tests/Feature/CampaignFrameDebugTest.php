<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\CampaignFrame\Models\CampaignFrame;
use function Pest\Laravel\{actingAs, get};

test('debug campaign frame complexity display', function () {
    $user = User::factory()->create();
    
    $frame = CampaignFrame::create([
        'name' => 'Debug Test Frame',
        'description' => 'Testing complexity display',
        'complexity_rating' => 2,
        'is_public' => true,
        'creator_id' => $user->id,
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
        'session_zero_questions' => []
    ]);

    $response = actingAs($user)->get("/campaign-frames/{$frame->id}");
    
    $content = $response->getContent();
    
    // Let's see what complexity text is actually in the HTML
    if (preg_match('/class=".*?"[^>]*>([^<]*(?:Simple|Moderate|Complex|Very Complex)[^<]*)</', $content, $matches)) {
        dump("Found complexity text: " . $matches[1]);
    } else {
        dump("No complexity text found");
    }
    
    // Let's also check the frame object itself
    dump("Frame complexity_rating: " . $frame->complexity_rating);
    
    $response->assertStatus(200);
});
