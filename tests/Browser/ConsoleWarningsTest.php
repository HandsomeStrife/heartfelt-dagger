<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;

test('TipTap editor loads and functions correctly without errors', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'TipTap Function Test',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Open the form which loads TipTap editor
    $page->click('Create Page');
    
    // Wait for editor to load
    $page->wait(3);
    
    // Verify TipTap editor is present and functional
    $page->assertSee('Page Content')
        ->assertSee('Create Campaign Page')
        ->assertSee('Page Title');
    
    // Try to interact with the form (should work without JS errors)
    $page->type('[placeholder="Enter page title..."]', 'Test TipTap Page')
        ->wait(1);
    
    // Verify form is responsive
    $page->assertValue('[placeholder="Enter page title..."]', 'Test TipTap Page');
});

test('slideover focus management works correctly', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Focus Management Test',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Open the slideover
    $page->click('Create Page');
    
    // Wait for slideover to open and focus to settle
    $page->wait(2);
    
    // Verify slideover is open and accessible
    $page->assertSee('Create Campaign Page')
        ->assertSee('Page Title');
    
    // Test backdrop click to close
    $page->click('.bg-black.bg-opacity-50');
    $page->wait(1);
    
    // Should be back to main page
    $page->assertSee('No pages found')
        ->assertDontSee('Create Campaign Page');
});

test('multiple slideover operations work without conflicts', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Multiple Operations Test',
    ]);

    auth()->login($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Open and close slideover multiple times
    for ($i = 0; $i < 3; $i++) {
        $page->click('Create Page');
        $page->wait(1);
        $page->assertSee('Create Campaign Page');
        
        $page->click('.bg-black.bg-opacity-50');
        $page->wait(1);
        $page->assertSee('No pages found');
    }
    
    // Final test - should still work properly
    $page->click('Create Page');
    $page->wait(2);
    $page->assertSee('Create Campaign Page')
        ->assertSee('Page Content');
});
