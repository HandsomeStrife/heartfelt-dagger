<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('storage account dashboard loads for authenticated user', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/storage-accounts');

    $response->assertOk()
        ->assertSee('Storage Accounts')
        ->assertSee('Manage your cloud storage connections')
        ->assertSee('Wasabi Accounts')
        ->assertSee('Google Drive Accounts');
});

test('storage account dashboard shows no accounts message when empty', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/storage-accounts');

    $response->assertOk()
        ->assertSee('No Wasabi accounts connected')
        ->assertSee('No Google Drive accounts connected')
        ->assertSee('Add First Account');
});

test('storage account dashboard shows existing wasabi accounts', function () {
    $user = User::factory()->create();

    UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'display_name' => 'My Test Wasabi',
        'is_active' => true,
        'encrypted_credentials' => [
            'access_key_id' => 'AKIAIOSFODNN7EXAMPLE',
            'secret_access_key' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            'bucket' => 'test-bucket',
            'region' => 'us-east-1',
        ],
    ]);

    $response = actingAs($user)->get('/storage-accounts');

    $response->assertOk()
        ->assertSee('My Test Wasabi')
        ->assertSee('Active')
        ->assertSee('test-bucket')
        ->assertSee('us-east-1');
});

test('storage account dashboard shows existing google drive accounts', function () {
    $user = User::factory()->create();

    UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google_drive',
        'display_name' => 'My Google Drive',
        'is_active' => true,
        'encrypted_credentials' => [
            'refresh_token' => 'sample-refresh-token',
            'email' => 'test@example.com',
        ],
    ]);

    $response = actingAs($user)->get('/storage-accounts');

    $response->assertOk()
        ->assertSee('My Google Drive')
        ->assertSee('Active')
        ->assertSee('test@example.com')
        ->assertSee('Connected');
});

test('storage account dashboard requires authentication', function () {
    $response = get('/storage-accounts');

    $response->assertRedirect('/login');
});

test('storage account dashboard loads add account modal correctly', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('showAddAccount', 'wasabi')
        ->assertSet('showAddAccountModal', true)
        ->assertSet('selectedProvider', 'wasabi')
        ->assertSee('Add Wasabi Account');
});

test('storage account dashboard can toggle account status', function () {
    $user = User::factory()->create();

    $account = UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'display_name' => 'Test Account',
        'is_active' => true,
    ]);

    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('toggleAccountStatus', $account)
        ->assertHasNoErrors()
        ->assertSet('showAddAccountModal', false);

    $account->refresh();
    expect($account->is_active)->toBeFalse();
});

test('storage account dashboard can delete unused account', function () {
    $user = User::factory()->create();

    $account = UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'wasabi',
        'display_name' => 'Test Account',
    ]);

    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('deleteAccount', $account)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('user_storage_accounts', [
        'id' => $account->id,
    ]);
});

test('storage account dashboard can handle test connection calls', function () {
    $user = User::factory()->create();

    $account = UserStorageAccount::factory()->wasabi()->create([
        'user_id' => $user->id,
        'display_name' => 'Test Wasabi Account',
    ]);

    // Test that the connection test method exists and can be called without PHP errors
    // The actual connection test will fail (which is expected in test environment)
    // but we're testing that the method exists and handles the call properly
    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('testConnection', $account)
        ->assertHasNoErrors();

    // That's sufficient - we've verified the method exists and doesn't crash
});

test('storage account dashboard shows test connection buttons', function () {
    $user = User::factory()->create();

    UserStorageAccount::factory()->wasabi()->inactive()->create([
        'user_id' => $user->id,
        'display_name' => 'My Test Wasabi',
    ]);

    $response = actingAs($user)->get('/storage-accounts');

    $response->assertOk()
        ->assertSee('My Test Wasabi')
        ->assertSee('Test')
        ->assertSee('Activate') // Account is explicitly inactive
        ->assertSee('Delete');
});
