<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('can access terms of service page', function () {
    $response = get(route('terms-of-service'));

    $response->assertStatus(200);
    $response->assertSee('Terms of Service');
    $response->assertSee('HeartfeltDagger');
    $response->assertSee('Google Drive');
    $response->assertSee('Video Recording and Consent');
});

it('can access privacy policy page', function () {
    $response = get(route('privacy-policy'));

    $response->assertStatus(200);
    $response->assertSee('Privacy Policy');
    $response->assertSee('HeartfeltDagger');
    $response->assertSee('Google Drive Integration');
    $response->assertSee('OAuth Authorization');
});

it('has legal links in footer', function () {
    $response = get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('Terms of Service');
    $response->assertSee('Privacy Policy');
    $response->assertSee(route('terms-of-service'));
    $response->assertSee(route('privacy-policy'));
});
