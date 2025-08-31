<?php

declare(strict_types=1);

use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

test('can create and save wasabi storage account with encrypted credentials', function () {
    $user = User::factory()->create();
    
    $credentials = [
        'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
        'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
        'bucket_name' => 'my-test-bucket',
        'region' => 'us-east-1',
        'endpoint' => 'https://s3.wasabisys.com',
    ];

    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => $credentials,
        'display_name' => 'My Wasabi Account',
        'is_active' => true,
    ]);

    // Verify the account was created
    expect($storageAccount)->toBeInstanceOf(UserStorageAccount::class);
    expect($storageAccount->user_id)->toBe($user->id);
    expect($storageAccount->provider)->toBe('wasabi');
    expect($storageAccount->display_name)->toBe('My Wasabi Account');
    expect($storageAccount->is_active)->toBe(true);

    // Verify credentials are accessible (Laravel should decrypt them automatically)
    $decryptedCredentials = $storageAccount->encrypted_credentials;
    expect($decryptedCredentials)->toBe($credentials);
    expect($decryptedCredentials['access_key_id'])->toBe('AKIAIOSFODNN7EXAMPLE');
    expect($decryptedCredentials['bucket_name'])->toBe('my-test-bucket');
});

test('credentials are actually encrypted in database', function () {
    $user = User::factory()->create();
    
    $credentials = [
        'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
        'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
        'bucket_name' => 'my-test-bucket',
        'region' => 'us-east-1',
        'endpoint' => 'https://s3.wasabisys.com',
    ];

    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => $credentials,
        'display_name' => 'My Wasabi Account',
        'is_active' => true,
    ]);

    // Check raw database value - should be encrypted, not plain text
    $rawRecord = \DB::table('user_storage_accounts')->find($storageAccount->id);
    $rawCredentials = $rawRecord->encrypted_credentials;
    
    // Raw data should NOT contain the plain text credentials
    expect($rawCredentials)->not()->toContain('AKIAIOSFODNN7EXAMPLE');
    expect($rawCredentials)->not()->toContain('my-test-bucket');
    expect($rawCredentials)->not()->toContain('wJalrXUtnFEMI/K7MDENG');
    
    // But the model should decrypt them properly
    expect($storageAccount->encrypted_credentials['access_key_id'])->toBe('AKIAIOSFODNN7EXAMPLE');
});

test('can retrieve user storage accounts by provider', function () {
    $user = User::factory()->create();
    
    // Create a Wasabi account
    $wasabiAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'Wasabi Account',
        'is_active' => true,
    ]);

    // Create a Google Drive account
    $googleAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
        ],
        'display_name' => 'Google Drive Account',
        'is_active' => true,
    ]);

    // Test filtering by provider
    $userWasabiAccounts = $user->storageAccounts()->where('provider', 'wasabi')->get();
    $userGoogleAccounts = $user->storageAccounts()->where('provider', 'google_drive')->get();

    expect($userWasabiAccounts)->toHaveCount(1);
    expect($userWasabiAccounts->first()->id)->toBe($wasabiAccount->id);
    
    expect($userGoogleAccounts)->toHaveCount(1);
    expect($userGoogleAccounts->first()->id)->toBe($googleAccount->id);
});

test('can initialize wasabi service with storage account', function () {
    $user = User::factory()->create();
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
            'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'bucket_name' => 'my-test-bucket',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.wasabisys.com',
        ],
        'display_name' => 'My Wasabi Account',
        'is_active' => true,
    ]);

    // Test that WasabiS3Service can be initialized with this storage account
    $wasabiService = new WasabiS3Service($storageAccount);
    
    expect($wasabiService)->toBeInstanceOf(WasabiS3Service::class);
    expect($wasabiService->getStorageAccount()->id)->toBe($storageAccount->id);
});

test('wasabi service rejects non-wasabi storage accounts', function () {
    $user = User::factory()->create();
    
    $googleAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'google_drive', // Not Wasabi
        'encrypted_credentials' => [
            'refresh_token' => 'test_refresh_token',
        ],
        'display_name' => 'Google Drive Account',
        'is_active' => true,
    ]);

    // This should throw an exception
    expect(function () use ($googleAccount) {
        new WasabiS3Service($googleAccount);
    })->toThrow(\InvalidArgumentException::class, 'Storage account must be for Wasabi provider');
});

test('can update storage account credentials', function () {
    $user = User::factory()->create();
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'old_key',
            'secret_access_key' => 'old_secret',
            'bucket_name' => 'old-bucket',
        ],
        'display_name' => 'Old Wasabi Account',
        'is_active' => true,
    ]);

    // Update credentials
    $newCredentials = [
        'access_key_id' => 'new_key',
        'secret_access_key' => 'new_secret',
        'bucket_name' => 'new-bucket',
        'region' => 'us-west-2',
        'endpoint' => 'https://s3.us-west-2.wasabisys.com',
    ];

    $storageAccount->update([
        'encrypted_credentials' => $newCredentials,
        'display_name' => 'Updated Wasabi Account',
    ]);

    // Refresh from database
    $storageAccount->refresh();

    // Verify updates
    expect($storageAccount->display_name)->toBe('Updated Wasabi Account');
    expect($storageAccount->encrypted_credentials['access_key_id'])->toBe('new_key');
    expect($storageAccount->encrypted_credentials['bucket_name'])->toBe('new-bucket');
    expect($storageAccount->encrypted_credentials['region'])->toBe('us-west-2');
});

test('can deactivate storage account', function () {
    $user = User::factory()->create();
    
    $storageAccount = UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => [
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'bucket_name' => 'test-bucket',
        ],
        'display_name' => 'Test Account',
        'is_active' => true,
    ]);

    expect($storageAccount->is_active)->toBe(true);

    // Deactivate account
    $storageAccount->update(['is_active' => false]);

    expect($storageAccount->is_active)->toBe(false);

    // Test filtering active accounts
    $activeAccounts = $user->storageAccounts()->where('is_active', true)->get();
    expect($activeAccounts)->toHaveCount(0);

    $allAccounts = $user->storageAccounts()->get();
    expect($allAccounts)->toHaveCount(1);
});

test('enforces unique constraint on user provider display name combination', function () {
    $user = User::factory()->create();
    
    // Create first storage account
    UserStorageAccount::create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => ['test' => 'data'],
        'display_name' => 'My Wasabi Account',
        'is_active' => true,
    ]);

    // Try to create another with same user, provider, and display name
    expect(function () use ($user) {
        UserStorageAccount::create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'encrypted_credentials' => ['test' => 'data2'],
            'display_name' => 'My Wasabi Account', // Same display name
            'is_active' => true,
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});

test('allows same display name for different users or providers', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Same display name, different users - should work
    $account1 = UserStorageAccount::create([
        'user_id' => $user1->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => ['test' => 'data1'],
        'display_name' => 'My Account',
        'is_active' => true,
    ]);

    $account2 = UserStorageAccount::create([
        'user_id' => $user2->id,
        'provider' => 'wasabi',
        'encrypted_credentials' => ['test' => 'data2'],
        'display_name' => 'My Account', // Same name, different user
        'is_active' => true,
    ]);

    // Same user, same display name, different provider - should work
    $account3 = UserStorageAccount::create([
        'user_id' => $user1->id,
        'provider' => 'google_drive',
        'encrypted_credentials' => ['test' => 'data3'],
        'display_name' => 'My Account', // Same name, different provider
        'is_active' => true,
    ]);

    expect($account1)->toBeInstanceOf(UserStorageAccount::class);
    expect($account2)->toBeInstanceOf(UserStorageAccount::class);
    expect($account3)->toBeInstanceOf(UserStorageAccount::class);
});

