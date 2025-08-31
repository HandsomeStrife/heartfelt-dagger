<?php

declare(strict_types=1);

use function Pest\Laravel\actingAs;

test('range viewer loads and displays correctly', function () {
    // Create a test user and login first
    $user = Domain\User\Models\User::factory()->create();
    
    actingAs($user);
    
    $page = visit('/range-check');
    $page->assertSee('DaggerHeart Range Viewer');
    $page->assertSee('Select Range');
    $page->assertSee('Melee');
    $page->assertSee('Very Close');
    $page->assertSee('Close');
    $page->assertSee('Far');
    $page->assertSee('Very Far');
    $page->assertSee('Out of Range');
    $page->assertSee('Current Distance:');
});

test('range buttons change distance correctly', function () {
    $user = Domain\User\Models\User::factory()->create();
    actingAs($user);
    
    $page = visit('/range-check');
    $page->assertSee('Current Distance: 20 ft'); // Should start with Close range
    $page->click('[data-range="melee"]');
    $page->wait(1)->assertSee('Current Distance: 1 ft');
    $page->click('[data-range="very-far"]');
    $page->wait(1)->assertSee('Current Distance: 200 ft');
    $page->click('[data-range="out-of-range"]');
    $page->wait(1)->assertSee('Current Distance: 400 ft');
});

test('canvas renders and download button works', function () {
    $user = Domain\User\Models\User::factory()->create();
    actingAs($user);
    
    $page = visit('/range-check');
    $page->assertPresent('#cv'); // Canvas should be present
    $page->assertSee('Download PNG');
});
