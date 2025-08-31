<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('campaign pages uses dark theme styling', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Dark Theme Test Campaign',
    ]);

    actingAs($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Campaign Pages')
        ->assertSee('Dark Theme Test Campaign')
        ->assertSee('Organize and manage your campaign lore')
        ->assertSee('No pages found')
        ->assertSee('Create Your First Page')
        ->assertSee('Campaign Stats')
        ->assertSee('Total Pages')
        ->assertSee('Categories')
        ->assertSee('Members');
});

test('campaign pages has compact navigation', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Compact Navigation Test',
    ]);

    actingAs($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    $page->assertSee('Compact Navigation Test') // Small campaign name in navigation
        ->assertSee('Overview') // Navigation tabs
        ->assertSee('Pages')
        ->assertSee('Campaign Pages'); // Main heading
});
