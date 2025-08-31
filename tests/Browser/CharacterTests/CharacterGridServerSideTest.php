<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use function Pest\Laravel\actingAs;

it('shows server-side characters for authenticated users without localStorage', function () {
    $user = User::factory()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'ServerSide One',
    ]);

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'ServerSide Two',
    ]);

    // Ensure we do not rely on localStorage and we have an authenticated context
    actingAs($user);
    $page = visit('/characters')->assertPathIs('/characters');
    $page->script('localStorage.removeItem("daggerheart_characters")');

    $page->assertSee('Your Characters');
    $page->waitForText('ServerSide One');
    $page->assertSee('ServerSide Two');
});


