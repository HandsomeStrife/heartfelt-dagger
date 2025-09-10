<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

test('ice config endpoint returns fallback when TURN is disabled', function () {
    Config::set('CF_TURN_DISABLED', true);

    $response = $this->getJson('/api/webrtc/ice-config');

    $response->assertOk()
        ->assertJson([
            'iceServers' => [
                [
                    'urls' => [
                        'stun:stun.cloudflare.com:3478',
                        'stun:stun.l.google.com:19302',
                    ],
                ],
            ],
        ]);
});

test('ice config endpoint returns fallback when cloudflare credentials missing', function () {
    Config::set('CF_TURN_DISABLED', false);
    Config::set('CF_TURN_KEY_ID', null);
    Config::set('CF_TURN_API_TOKEN', null);

    $response = $this->getJson('/api/webrtc/ice-config');

    $response->assertOk()
        ->assertJson([
            'iceServers' => [
                [
                    'urls' => [
                        'stun:stun.cloudflare.com:3478',
                        'stun:stun.l.google.com:19302',
                    ],
                ],
            ],
        ]);
});

test('ice config endpoint returns cloudflare config when available', function () {
    Config::set('CF_TURN_DISABLED', false);
    Config::set('CF_TURN_KEY_ID', 'test-key-id');
    Config::set('CF_TURN_API_TOKEN', 'test-token');
    Config::set('CF_TURN_TTL', 3600);

    // Mock successful Cloudflare API response
    Http::fake([
        'rtc.live.cloudflare.com/v1/turn/keys/test-key-id/credentials/generate-ice-servers' => Http::response([
            'iceServers' => [
                [
                    'urls' => ['stun:stun.cloudflare.com:3478'],
                ],
                [
                    'urls' => ['turn:turn.cloudflare.com:3478'],
                    'username' => 'test-username',
                    'credential' => 'test-credential',
                ],
            ],
        ], 200),
    ]);

    $response = $this->getJson('/api/webrtc/ice-config');

    $response->assertOk()
        ->assertJsonStructure([
            'iceServers' => [
                '*' => ['urls'],
            ],
        ]);

    $data = $response->json();

    // Should have both STUN and TURN servers
    expect($data['iceServers'])->toHaveCount(2);

    // Verify the API was called with correct parameters
    Http::assertSent(function ($request) {
        return $request->url() === 'https://rtc.live.cloudflare.com/v1/turn/keys/test-key-id/credentials/generate-ice-servers' &&
               $request['ttl'] === 3600;
    });
});

test('ice config endpoint returns fallback when cloudflare api fails', function () {
    Config::set('CF_TURN_DISABLED', false);
    Config::set('CF_TURN_KEY_ID', 'test-key-id');
    Config::set('CF_TURN_API_TOKEN', 'test-token');

    // Mock failed Cloudflare API response
    Http::fake([
        'rtc.live.cloudflare.com/v1/turn/keys/test-key-id/credentials/generate-ice-servers' => Http::response([], 500),
    ]);

    $response = $this->getJson('/api/webrtc/ice-config');

    $response->assertOk()
        ->assertJson([
            'iceServers' => [
                [
                    'urls' => [
                        'stun:stun.cloudflare.com:3478',
                        'stun:stun.l.google.com:19302',
                    ],
                ],
            ],
        ]);
});

test('ice config endpoint caches responses to avoid rate limiting', function () {
    Config::set('CF_TURN_DISABLED', false);
    Config::set('CF_TURN_KEY_ID', 'test-key-id');
    Config::set('CF_TURN_API_TOKEN', 'test-token');

    // Clear cache first
    Cache::flush();

    // Mock successful Cloudflare API response
    Http::fake([
        'rtc.live.cloudflare.com/v1/turn/keys/test-key-id/credentials/generate-ice-servers' => Http::response([
            'iceServers' => [
                ['urls' => ['stun:stun.cloudflare.com:3478']],
            ],
        ], 200),
    ]);

    // First request
    $response1 = $this->getJson('/api/webrtc/ice-config');
    $response1->assertOk();

    // Second request within cache window
    $response2 = $this->getJson('/api/webrtc/ice-config');
    $response2->assertOk();

    // Should only have been called once due to caching
    Http::assertSentCount(1);

    // Responses should be identical
    expect($response1->json())->toEqual($response2->json());
});

test('ice config endpoint filters out port 53 candidates', function () {
    Config::set('CF_TURN_DISABLED', false);
    Config::set('CF_TURN_KEY_ID', 'test-key-id');
    Config::set('CF_TURN_API_TOKEN', 'test-token');

    // Mock Cloudflare response with :53 candidates
    Http::fake([
        'rtc.live.cloudflare.com/v1/turn/keys/test-key-id/credentials/generate-ice-servers' => Http::response([
            'iceServers' => [
                [
                    'urls' => [
                        'stun:stun.cloudflare.com:3478',
                        'stun:stun.cloudflare.com:53',  // This should be filtered out
                        'turn:turn.cloudflare.com:3478',
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->getJson('/api/webrtc/ice-config');

    $response->assertOk();

    $data = $response->json();
    $urls = $data['iceServers'][0]['urls'];

    // Should not contain the :53 URL
    expect($urls)->not->toContain('stun:stun.cloudflare.com:53');
    expect($urls)->toContain('stun:stun.cloudflare.com:3478');
    expect($urls)->toContain('turn:turn.cloudflare.com:3478');
});

test('ice config endpoint handles malformed cloudflare response', function () {
    Config::set('CF_TURN_DISABLED', false);
    Config::set('CF_TURN_KEY_ID', 'test-key-id');
    Config::set('CF_TURN_API_TOKEN', 'test-token');

    // Mock malformed Cloudflare API response
    Http::fake([
        'rtc.live.cloudflare.com/v1/turn/keys/test-key-id/credentials/generate-ice-servers' => Http::response([
            'invalid' => 'response',
        ], 200),
    ]);

    $response = $this->getJson('/api/webrtc/ice-config');

    $response->assertOk()
        ->assertJson([
            'iceServers' => [
                [
                    'urls' => [
                        'stun:stun.cloudflare.com:3478',
                        'stun:stun.l.google.com:19302',
                    ],
                ],
            ],
        ]);
});
