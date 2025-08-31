<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('campaign creator can access campaign pages without layout error', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Campaign for Pages Access',
    ]);

    actingAs($creator);
    
    // This should reproduce the "Unable to locate a class or view for component [layout.default]" error
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Campaign Pages')
        ->assertDontSee('Unable to locate a class or view for component');
});
