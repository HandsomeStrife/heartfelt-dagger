<?php

declare(strict_types=1);

use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('actual plays page loads successfully', function () {
    $response = get('/actual-plays');

    $response->assertStatus(200);
    $response->assertSee('Actual Plays');
    $response->assertSee('Video Actual Plays');
    $response->assertSee('Audio Actual Plays');
    $response->assertSee('DMDanT (u/ShiaLovekraft)');
    $response->assertSee('thepartywipes@gmail.com');
    $response->assertSee('Want to be added, removed, or updated');
});

test('actual plays page displays video content', function () {
    $response = get('/actual-plays');

    $response->assertStatus(200);
    $response->assertSee('Explorers of Elsewhere');
    $response->assertSee('The Titan Isles');
    $response->assertSee('YouTube');
});

test('actual plays page displays audio content', function () {
    $response = get('/actual-plays');

    $response->assertStatus(200);
    $response->assertSee('DodoBorne');
    $response->assertSee('Hag Deal Gaming');
    $response->assertSee('Queen of Daggers, Heart of Fire');
});

test('actual plays route is accessible', function () {
    $response = get(route('actual-plays'));

    $response->assertStatus(200);
});
