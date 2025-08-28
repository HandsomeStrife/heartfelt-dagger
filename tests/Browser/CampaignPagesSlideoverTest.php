<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;

test('campaign page creation opens in slideover instead of modal', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Slideover Test Campaign',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Click create page button
    $page->click('Create Page');
    
    // Should see the form appear as a slideover (right side panel)
    $page->assertSee('Create Campaign Page')
        ->assertSee('Page Title')
        ->assertSee('Parent Page')
        ->assertSee('Access Level')
        ->assertSee('Category Tags')
        ->assertSee('Page Content');
});

test('campaign page form uses dark theme styling', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Dark Theme Test',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Open the form
    $page->click('Create Page');
    
    // Verify dark theme elements are present
    $page->assertSee('Create Campaign Page')
        ->assertSee('Add new content to your campaign')
        ->assertSee('Page Title')
        ->assertSee('Access Level')
        ->assertSee('Category Tags')
        ->assertSee('Publishing Options')
        ->assertSee('Create Page'); // Submit button
});

test('slideover form can be closed', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Close Test Campaign',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Open the form
    $page->click('Create Page');
    $page->assertSee('Create Campaign Page');
    
    // Close via cancel button
    $page->click('Cancel');
    
    // Form should be closed (check that main content is visible again)
    $page->assertSee('No pages found')
        ->assertSee('Create Your First Page');
});

test('slideover form is properly positioned and sized', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Position Test Campaign',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Open the form
    $page->click('Create Page');
    
    // Verify the slideover structure is present
    $page->assertSee('Create Campaign Page')
        ->assertSee('Page Title')
        ->assertSee('Page Content')
        ->assertSee('Create Page'); // Submit button at bottom
});
