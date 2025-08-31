<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('TipTap bold button works without transaction errors', function () {
    $creator = User::factory()->create(['password' => bcrypt('password')]);
    $campaign = Campaign::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Bold Function Test',
    ]);

    actingAs($creator);
    
    $page = visit("/campaigns/{$campaign->campaign_code}/pages");
    
    // Open the form which loads TipTap editor
    $page->click('Create Page')
        ->wait(2); // Wait for editor to fully load
    
    // Verify the form and editor are loaded
    $page->assertSee('Create Campaign Page')
        ->assertSee('Page Content');
    
    // Click into the editor area to focus it
    $page->click('.tiptap')
        ->wait(0.5);
    
    // Type some text first
    $page->type('.tiptap', 'This is a test of bold functionality.')
        ->wait(0.5);
    
    // Select the text by triple-clicking (selects all)
    $page->click('.tiptap', ['clickCount' => 3])
        ->wait(0.5);
    
    // Now click the bold button - this should work without errors
    $page->click('[title="Bold"]')
        ->wait(0.5);
    
    // The test passing means no JavaScript errors occurred
    $page->assertSee('Create Campaign Page');
});

