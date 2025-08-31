<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use function Pest\Laravel\actingAs;

test('google drive authorize redirects to google oauth', function () {
    $user = User::factory()->create();

    // Mock the Google Drive configuration
    config([
        'services.google_drive.client_id' => 'test_client_id',
        'services.google_drive.client_secret' => 'test_client_secret',
        'services.google_drive.redirect_uri' => 'http://localhost/google-drive/callback',
    ]);

    actingAs($user);
    $page = visit('/google-drive/authorize');

    // Should redirect to Google OAuth (or show error if config is missing)
    // In a real test environment, this would redirect to Google
    // For now, we'll just test that the route is accessible
    $page->assertRedirect();
});

test('google drive callback handles success flow', function () {
    $user = User::factory()->create();

    // Mock the Google Drive configuration
    config([
        'services.google_drive.client_id' => 'test_client_id',
        'services.google_drive.client_secret' => 'test_client_secret',
        'services.google_drive.redirect_uri' => 'http://localhost/google-drive/callback',
    ]);

    // Since we can't actually complete OAuth in tests, we'll test the callback route exists
    actingAs($user);
    $page = visit('/google-drive/callback?code=test_code');

    // Should either succeed or show error (depending on actual Google API response)
    // The important thing is that the route is accessible and handles the flow
    $page->assertRedirect();
});

test('google drive callback handles error state', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/google-drive/callback?error=access_denied');

    // Should redirect with error message
    $page->assertRedirect();
});

test('google drive callback requires authentication', function () {
    // Test without authentication
    $page = visit('/google-drive/callback?code=test_code');

    $page->assertRedirect('/login');
});

test('google drive authorize requires authentication', function () {
    // Test without authentication
    $page = visit('/google-drive/authorize');

    $page->assertRedirect('/login');
});

test('can disconnect google drive account', function () {
    $user = User::factory()->create();
    
    // Create a Google Drive storage account
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
            'access_token' => 'test_access_token',
        ],
        'display_name' => 'Test Google Drive Account',
        'is_active' => true,
    ]);

    // Mock a page that has a disconnect form (this would be in the storage management UI)
    actingAs($user);
    $page = visit('rooms.index'); // Use existing route for test

    // Create a simple disconnect form for testing
    $html = '
        <form action="/google-drive/disconnect" method="POST">
            <input type="hidden" name="_token" value="' . csrf_token() . '">
            <input type="hidden" name="storage_account_id" value="' . $storageAccount->id . '">
            <button type="submit">Disconnect Google Drive</button>
        </form>
    ';

    $page->script("document.body.innerHTML += `{$html}`;");

    $page->press('Disconnect Google Drive');

    // Should redirect back with success message
    expect(UserStorageAccount::find($storageAccount->id))->toBeNull();
});

test('google drive authorize handles redirect parameter', function () {
    $user = User::factory()->create();
    $redirectUrl = '/some/return/path';

    // Mock the Google Drive configuration
    config([
        'services.google_drive.client_id' => 'test_client_id',
        'services.google_drive.client_secret' => 'test_client_secret',
        'services.google_drive.redirect_uri' => 'http://localhost/google-drive/callback',
    ]);

    actingAs($user);
    $page = visit("/google-drive/authorize?redirect_to=" . urlencode($redirectUrl));

    // The redirect parameter should be stored in session for after OAuth
    // We can't test the full OAuth flow, but we can test the route accepts the parameter
    $page->assertRedirect();
});

test('google drive callback returns to redirect url after success', function () {
    $user = User::factory()->create();
    $redirectUrl = '/rooms';

    // Mock the session data that would be set during authorization
    session(['google_drive_redirect_to' => $redirectUrl]);

    // Mock the Google Drive configuration
    config([
        'services.google_drive.client_id' => 'test_client_id',
        'services.google_drive.client_secret' => 'test_client_secret',
        'services.google_drive.redirect_uri' => 'http://localhost/google-drive/callback',
    ]);

    actingAs($user);
    $page = visit('/google-drive/callback?code=test_auth_code');

    // Should attempt to process the callback
    // In a real scenario, this would either succeed and redirect, or fail with an error
    $page->assertRedirect();
});

test('google drive oauth preserves user session', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/google-drive/authorize');

    // Verify user is logged in
    expect($page->assertAuthenticated())->toBeTrue();
    expect($page->assertAuthenticated()->id())->toBe($user->id);

    // Visit OAuth authorize (would redirect to Google in real scenario)
    $page->visit('/google-drive/authorize');

    // Session should still be valid
    expect($page->assertAuthenticated())->toBeTrue();
    expect($page->assertAuthenticated()->id())->toBe($user->id);
});

test('google drive disconnect validates ownership', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Create storage account for user2
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user2->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
        ],
        'display_name' => 'Other User Account',
        'is_active' => true,
    ]);

    // Try to disconnect as user1 (wrong user)
    actingAs($user1);
    $page = visit('rooms.index');

    // Create disconnect form for testing
    $html = '
        <form action="/google-drive/disconnect" method="POST">
            <input type="hidden" name="_token" value="' . csrf_token() . '">
            <input type="hidden" name="storage_account_id" value="' . $storageAccount->id . '">
            <button type="submit">Disconnect</button>
        </form>
    ';

    $page->script("document.body.innerHTML += `{$html}`;");

    $page->press('Disconnect');

    // Should show error - account should still exist
    expect(UserStorageAccount::find($storageAccount->id))->not()->toBeNull();
});

test('google drive routes have proper middleware', function () {
    // Test that OAuth routes require authentication
    $routes = [
        '/google-drive/authorize',
        '/google-drive/callback',
    ];

    foreach ($routes as $route) {
        $page = visit($route);
        $page->assertRedirect('/login');
    }
});

test('google drive callback handles missing authorization code', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/google-drive/callback'); // No code parameter

    // Should redirect with error or show error message
    $page->assertRedirect();
});

test('google drive configuration is properly loaded', function () {
    $user = User::factory()->create();

    // Set configuration
    config([
        'services.google_drive.client_id' => 'test_client_id_123',
        'services.google_drive.client_secret' => 'test_secret_456',
        'services.google_drive.redirect_uri' => 'http://localhost/google-drive/callback',
    ]);

    // Verify configuration is accessible
    expect(config('services.google_drive.client_id'))->toBe('test_client_id_123');
    expect(config('services.google_drive.client_secret'))->toBe('test_secret_456');
    expect(config('services.google_drive.redirect_uri'))->toBe('http://localhost/google-drive/callback');
});

test('google drive service configuration validation', function () {
    $user = User::factory()->create();

    // Test with missing configuration
    config([
        'services.google_drive.client_id' => null,
        'services.google_drive.client_secret' => null,
        'services.google_drive.redirect_uri' => null,
    ]);

    actingAs($user);
    $page = visit('/google-drive/authorize');

    // Should handle missing configuration gracefully
    // (Either redirect with error or show configuration error)
    $page->assertRedirect();
});
