<?php

declare(strict_types=1);
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('discord route redirects to discord url', function () {
    $response = $this->get('/discord');

    $response->assertStatus(302);
    $response->assertRedirect('https://discord.gg/dNAkDYevGx');
});
test('discord route is accessible to guests', function () {
    // Guests should be able to access the Discord redirect
    $response = $this->get('/discord');

    $response->assertStatus(302);
});
test('discord route is accessible to authenticated users', function () {
    $user = \Domain\User\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/discord');

    $response->assertStatus(302);
    $response->assertRedirect('https://discord.gg/dNAkDYevGx');
});
