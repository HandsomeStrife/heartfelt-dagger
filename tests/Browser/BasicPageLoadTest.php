<?php

declare(strict_types=1);

use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

test('authenticated user can access campaigns page', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    actingAs($user);
    
    $page = visit('/campaigns');
    
    $page->assertSee('Campaigns');
});

test('authenticated user can access campaign creation page', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    actingAs($user);
    
    $page = visit('/campaigns/create');
    
    $page->assertSee('Create Campaign')
        ->assertSee('Campaign Name');
});

test('campaign creation page loads properly', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    actingAs($user);
    
    $page = visit('/campaigns/create');
    
    $page->assertSee('Create Campaign')
        ->assertSee('Campaign Name')
        ->assertSee('Description');
});
