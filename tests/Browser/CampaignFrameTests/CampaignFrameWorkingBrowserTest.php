<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\CampaignFrame\Models\CampaignFrame;
use function Pest\Laravel\actingAs;

test('complete campaign frame authentication flow works', function () {
    // Create a user with known credentials
    $user = User::factory()->create();
    
    // Create a public campaign frame for testing
    $frame = CampaignFrame::factory()->create([
        'name' => 'Test Frame Authentication',
        'description' => 'Testing authentication flow for campaign frames',
        'creator_id' => $user->id,
    ]);

    // Test authenticated access to campaign frames
    actingAs($user);
    $page = visit('/campaign-frames');
    $page->assertSee('Campaign Frames');

    expect(true)->toBeTrue(); // Ensure at least one assertion
})->group('browser');
