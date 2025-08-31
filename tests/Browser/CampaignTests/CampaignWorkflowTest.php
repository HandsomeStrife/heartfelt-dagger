<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('authenticated user can access campaigns page', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaigns');
    
    $page->wait(3)
        ->assertSee('Campaigns');
});

test('authenticated user can access campaign create page', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaigns/create');
    
    $page->wait(3)
        ->assertSee('Create Campaign')
        ->assertSee('Campaign Name');
});

test('campaign shows basic information', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Test Campaign',
    ]);
    
    actingAs($user);
    $page = visit("/campaigns/{$campaign->campaign_code}");
    
    $page->wait(3)
        ->assertSee('Test Campaign');
});

test('can interact with campaign creation form', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaigns/create');
    
    $page->wait(3)
        ->assertSee('Campaign Name')
        ->type('name', 'My Test Campaign')
        ->type('description', 'A test campaign description')
        ->wait(1);
    
    // Just verify we can interact with the form, don't test submission
    expect(true)->toBeTrue();
});
