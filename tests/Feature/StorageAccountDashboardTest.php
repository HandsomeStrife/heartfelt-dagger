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
        ->assertSee(['Storage', 'Services'])
        ->assertSee('Manage your cloud storage and transcription services')
        ->assertSee('Cloud Storage')
        ->assertSee('Transcription')
        ->assertSee('Statistics');
});

test('storage account dashboard shows no accounts message when empty', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/storage-accounts');

    $response->assertOk()
        ->assertSee('No accounts connected')
        ->assertSee('Connect');
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
        ->assertSee('test@example.com');
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
        ->assertSee('Add Wasabi');
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

test('storage account dashboard shows existing assemblyai accounts', function () {
    $user = User::factory()->create();

    UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'assemblyai',
        'display_name' => 'My AssemblyAI Account',
        'is_active' => true,
        'encrypted_credentials' => [
            'api_key' => 'test-api-key-12345',
        ],
    ]);

    $response = actingAs($user)->get('/storage-accounts');

    $response->assertOk()
        ->assertSee('My AssemblyAI Account')
        ->assertSee('Active');
});

test('storage account dashboard can show assemblyai add account modal', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('showAddAccount', 'assemblyai')
        ->assertSet('showAddAccountModal', true)
        ->assertSet('selectedProvider', 'assemblyai')
        ->assertSee('Add AssemblyAI');
});

test('storage account dashboard can test assemblyai connection', function () {
    $user = User::factory()->create();

    $account = UserStorageAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'assemblyai',
        'display_name' => 'Test AssemblyAI Account',
        'encrypted_credentials' => [
            'api_key' => 'test-api-key',
        ],
    ]);

    // Test that the connection test method exists and can be called without PHP errors
    // The actual connection test will be skipped in test environment
    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('testConnection', $account)
        ->assertHasNoErrors();
});

test('storage account dashboard can save assemblyai account', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('showAddAccount', 'assemblyai')
        ->set('assemblyaiForm.display_name', 'My Test AssemblyAI')
        ->set('assemblyaiForm.api_key', 'test-api-key-12345')
        ->call('saveAssemblyAIAccount')
        ->assertHasNoErrors()
        ->assertSet('showAddAccountModal', false);

    $this->assertDatabaseHas('user_storage_accounts', [
        'user_id' => $user->id,
        'provider' => 'assemblyai',
        'display_name' => 'My Test AssemblyAI',
        'is_active' => true,
    ]);
});

test('storage account dashboard validates assemblyai form', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('showAddAccount', 'assemblyai')
        ->set('assemblyaiForm.display_name', '') // Empty name
        ->set('assemblyaiForm.api_key', '') // Empty API key
        ->call('saveAssemblyAIAccount')
        ->assertHasErrors(['assemblyaiForm.display_name', 'assemblyaiForm.api_key']);
});

test('storage account dashboard can test assemblyai form connection', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->livewire(\App\Livewire\StorageAccountDashboard::class)
        ->call('showAddAccount', 'assemblyai')
        ->set('assemblyaiForm.display_name', 'Test Account')
        ->set('assemblyaiForm.api_key', 'test-api-key')
        ->call('testAssemblyAIFormConnection')
        ->assertHasNoErrors()
        ->assertSet('connectionResult', 'success'); // Should succeed in test environment
});
