<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

test('authenticated user can generate assemblyai token', function () {
    $user = User::factory()->create();

    // Mock the AssemblyAI API response
    Http::fake([
        'streaming.assemblyai.com/v3/token*' => Http::response([
            'token' => 'test_token_12345',
        ], 200),
    ]);

    actingAs($user);

    $response = postJson('/api/assemblyai/token', [
        'api_key' => 'test_api_key',
    ]);

    $response->assertOk()
        ->assertJson([
            'token' => 'test_token_12345',
        ]);

    // Verify the request was made to AssemblyAI
    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'streaming.assemblyai.com/v3/token') &&
               $request->header('Authorization')[0] === 'test_api_key' &&
               str_contains($request->url(), 'expires_in_seconds=600');
    });
});

test('unauthenticated user cannot generate assemblyai token', function () {
    $response = postJson('/api/assemblyai/token', [
        'api_key' => 'test_api_key',
    ]);

    $response->assertUnauthorized();
});

test('assemblyai token generation handles api errors', function () {
    $user = User::factory()->create();

    // Mock the AssemblyAI API error response
    Http::fake([
        'streaming.assemblyai.com/v3/token*' => Http::response([
            'error' => 'Invalid API key',
        ], 401),
    ]);

    actingAs($user);

    $response = postJson('/api/assemblyai/token', [
        'api_key' => 'invalid_key',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'error' => 'Failed to generate AssemblyAI token',
        ]);
});
