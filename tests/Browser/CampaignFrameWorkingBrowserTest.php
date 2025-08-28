<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

test('complete campaign frame authentication flow works', function () {
    // Create a user with known credentials
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
    
    // Create a public campaign frame for testing
    $frame = \Domain\CampaignFrame\Models\CampaignFrame::create([
        'name' => 'Test Frame Authentication',
        'description' => 'Testing authentication flow for campaign frames',
        'complexity_rating' => 1,
        'is_public' => true,
        'creator_id' => $user->id,
        'pitch' => ['Test pitch point'],
        'touchstones' => ['Test Touchstone'],
        'tone' => ['Test Tone'],
        'themes' => ['Test Theme'],
        'player_principles' => ['Test Player Principle'],
        'gm_principles' => ['Test GM Principle'],
        'community_guidance' => ['Test Community Guide'],
        'ancestry_guidance' => ['Test Ancestry Guide'],
        'class_guidance' => ['Test Class Guide'],
        'background_overview' => 'Test background overview content',
        'setting_guidance' => ['Test Setting Guide'],
        'setting_distinctions' => ['Test Setting Distinction'],
        'inciting_incident' => 'Test inciting incident content',
        'special_mechanics' => [],
        'campaign_mechanics' => ['Test Campaign Mechanic'],
        'session_zero_questions' => ['Test Question?']
    ]);

    // Visit login page and perform authentication
    $page = visit('/login')
        ->assertSee('Enter the Realm')
        ->type('#email', 'test@example.com')
        ->type('#password', 'password')
        ->click('[data-testid="login-submit-button"]')
        ->wait(2000); // Wait for redirect

    // Now test that we can navigate to campaign frames
    $page->navigate('/campaign-frames')
        ->assertSee('Campaign Frames');

    // Test that we can view the specific frame
    $page->navigate("/campaign-frames/{$frame->id}")
        ->assertSee('Test Frame Authentication')
        ->assertSee('Testing authentication flow')
        ->assertSee('Simple Complexity')
        ->assertSee('Test pitch point')
        ->assertSee('Test Touchstone');

    expect(true)->toBeTrue(); // Ensure at least one assertion
})->group('browser');
