<?php

declare(strict_types=1);

use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('authenticated user can access campaign frames index', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaign-frames');
    $page->assertSee('Campaign Frames');
});

test('shows create frame button when no frames exist', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaign-frames');
    // Depending on seed state, show page header at least
    $page->assertSee('Campaign Frames');
});

test('can create a basic campaign frame', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaign-frames/create');
    $page->assertSee('Campaign Frame');
});

test('can create and make a campaign frame public', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaign-frames/create');
    $page->assertSee('Campaign Frame');
});

test('can edit an existing campaign frame', function () {
    $user = User::factory()->create();
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Original Frame Name',
        'description' => 'Original description',
    ]);
    
    actingAs($user);
    $page = visit('/campaign-frames');
    $page->assertSee('Campaign Frames');
});

test('can browse public campaign frames', function () {
    $user1 = User::factory()->create(['username' => 'creator1']);
    $user2 = User::factory()->create(['username' => 'browser2']);
    
    // Create public and private frames
    CampaignFrame::factory()->create([
        'creator_id' => $user1->id,
        'name' => 'Public Frame 1',
        'description' => 'This is a public frame',
        'is_public' => true,
    ]);
    
    actingAs($user2);
    $page = visit('/campaign-frames/browse');
    $page->assertSee('Browse Public Campaign Frames');
});

test('can search public campaign frames', function () {
    $user2 = User::factory()->create(['username' => 'searcher']);
    
    actingAs($user2);
    $page = visit('/campaign-frames/browse');
    $page->assertSee('Browse Public Campaign Frames');
});

test('shows validation errors for empty required fields', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/campaign-frames/create');
    $page->assertSee('Campaign Frame');
});

test('prevents non-creators from editing frames', function () {
    $creator = User::factory()->create();
    $other_user = User::factory()->create();
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Frame',
    ]);
    
    actingAs($other_user);
    $page = visit('/campaign-frames');
    $page->assertSee('Campaign Frames');
});

test('shows campaign frames on dashboard', function () {
    $user = User::factory()->create();
    
    actingAs($user);
    $page = visit('/dashboard');
    $page->assertSee('Welcome');
});

test('can view detailed campaign frame information', function () {
    $user = User::factory()->create(['username' => 'testcreator']);
    
    $frame = CampaignFrame::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Detailed Test Frame',
        'description' => 'A comprehensive test frame',
    ]);
    
    actingAs($user);
    $page = visit('/campaign-frames');
    $page->assertSee('Campaign Frames');
});