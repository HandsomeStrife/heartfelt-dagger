<?php

declare(strict_types=1);
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('discord route redirects to discord url', function () {
    $response = get('/discord');

    $response->assertStatus(302);
    $response->assertRedirect('https://discord.gg/dNAkDYevGx');
});
test('discord route is accessible to guests', function () {
    // Guests should be able to access the Discord redirect
    $response = get('/discord');

    $response->assertStatus(302);
});
test('discord route is accessible to authenticated users', function () {
    $user = \Domain\User\Models\User::factory()->create();

    $response = actingAs($user)->get('/discord');

    $response->assertStatus(302);
    $response->assertRedirect('https://discord.gg/dNAkDYevGx');
});
