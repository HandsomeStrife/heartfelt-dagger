<?php

declare(strict_types=1);

use Domain\User\Models\User;

test('authenticated user can access campaigns page', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    auth()->login($user);
    
    $page = visit('/campaigns');
    
    $page->assertSee('Campaigns');
});

test('authenticated user can access campaign creation page', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    auth()->login($user);
    
    $page = visit('/campaigns/create');
    
    $page->assertSee('Create Campaign')
        ->assertSee('Campaign Name');
});

test('campaign frame integration loads on campaign creation page', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    auth()->login($user);
    
    $page = visit('/campaigns/create');
    
    $page->assertSee('Create Campaign')
        ->assertSee('Campaign Frame')
        ->assertSee('optional');
});
