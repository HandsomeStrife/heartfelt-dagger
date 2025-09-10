<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Forms\AssemblyAIAccountForm;
use Domain\Room\Services\GoogleDriveService;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StorageAccountDashboard extends Component
{
    public Collection $wasabiAccounts;

    public Collection $googleDriveAccounts;

    public Collection $assemblyaiAccounts;

    public AssemblyAIAccountForm $assemblyaiForm;

    public bool $showAddAccountModal = false;

    public ?string $selectedProvider = null;

    public bool $isTestingConnection = false;

    public ?string $connectionResult = null;

    public function mount(): void
    {
        $this->loadStorageAccounts();
    }

    public function loadStorageAccounts(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $this->wasabiAccounts = $user->storageAccounts()
            ->where('provider', 'wasabi')
            ->orderBy('display_name')
            ->get();

        $this->googleDriveAccounts = $user->storageAccounts()
            ->where('provider', 'google_drive')
            ->orderBy('display_name')
            ->get();

        $this->assemblyaiAccounts = $user->storageAccounts()
            ->where('provider', 'assemblyai')
            ->orderBy('display_name')
            ->get();
    }

    public function showAddAccount(string $provider): void
    {
        $this->selectedProvider = $provider;
        $this->showAddAccountModal = true;
    }

    public function hideAddAccountModal(): void
    {
        $this->showAddAccountModal = false;
        $this->selectedProvider = null;
    }

    public function deleteAccount(UserStorageAccount $account): void
    {
        // Check if account is in use by any rooms
        $user = $account->user;

        if (! $user instanceof User) {
            session()->flash('error', 'Unable to verify account usage - account not found.');

            return;
        }

        $roomsUsingAccount = $user->rooms()
            ->whereHas('recordingSettings', function ($query) use ($account) {
                $query->where('storage_account_id', $account->id);
            })
            ->count();

        if ($roomsUsingAccount > 0) {
            session()->flash('error', "Cannot delete account '{$account->display_name}' - it's currently being used by {$roomsUsingAccount} room(s).");

            return;
        }

        $account->delete();
        $this->loadStorageAccounts();
        session()->flash('success', "Storage account '{$account->display_name}' deleted successfully.");
    }

    public function toggleAccountStatus(UserStorageAccount $account): void
    {
        $account->is_active = ! $account->is_active;
        $account->save();

        $this->loadStorageAccounts();

        $status = $account->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Account '{$account->display_name}' {$status} successfully.");
    }

    public function testConnection(UserStorageAccount $account): void
    {
        try {
            if ($account->isWasabi()) {
                $this->testWasabiConnection($account);
            } elseif ($account->isGoogleDrive()) {
                $this->testGoogleDriveConnection($account);
            } elseif ($account->provider === 'assemblyai') {
                $this->testAssemblyAIConnection($account);
            } else {
                session()->flash('error', "Unsupported provider: {$account->provider}");

                return;
            }

            session()->flash('success', "Connection test successful for '{$account->display_name}'!");
        } catch (\Exception $e) {
            session()->flash('error', "Connection test failed for '{$account->display_name}': ".$e->getMessage());
        }
    }

    private function testWasabiConnection(UserStorageAccount $account): void
    {
        $wasabiService = new WasabiS3Service($account);
        $credentials = $account->getCredentials();

        $client = $wasabiService->createS3ClientWithCredentials($credentials);

        // Test by listing objects in the bucket (limited to 1 for efficiency)
        $client->listObjects([
            'Bucket' => $credentials['bucket'],
            'MaxKeys' => 1,
        ]);
    }

    private function testGoogleDriveConnection(UserStorageAccount $account): void
    {
        $googleDriveService = new GoogleDriveService($account);

        // Test the connection using the built-in test method
        if (!$googleDriveService->testConnection()) {
            throw new \Exception('Google Drive connection test failed');
        }
    }

    private function testAssemblyAIConnection(UserStorageAccount $account): void
    {
        $credentials = $account->getCredentials();
        $apiKey = $credentials['api_key'] ?? '';

        if (empty($apiKey)) {
            throw new \Exception('API key not found');
        }

        // Skip API validation in testing environment
        if (app()->environment('testing')) {
            return;
        }

        // Test the API key by making a simple request to AssemblyAI
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.assemblyai.com/v2/transcript');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: '.$apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 401) {
            throw new \Exception('Invalid API key');
        } elseif ($httpCode !== 200 && $httpCode !== 400) {
            // 200 = success, 400 = expected for empty request but API key is valid
            throw new \Exception('Unexpected response from AssemblyAI API');
        }
    }

    public function saveAssemblyAIAccount(): void
    {
        try {
            // Create the storage account
            $account = $this->assemblyaiForm->store();

            session()->flash('success', 'AssemblyAI account connected successfully!');
            $this->hideAddAccountModal();
            $this->loadStorageAccounts();
            $this->assemblyaiForm->reset();

        } catch (\Exception $e) {
            $this->addError('assemblyai_form', 'Failed to connect AssemblyAI account: '.$e->getMessage());
        }
    }

    public function testAssemblyAIFormConnection(): void
    {
        $this->assemblyaiForm->validate();
        $this->isTestingConnection = true;
        $this->connectionResult = null;

        try {
            // Skip API validation in testing environment
            if (app()->environment('testing')) {
                $this->connectionResult = 'success';
                return;
            }

            // Test the API key by making a simple request to AssemblyAI
            $response = $this->makeAssemblyAITestRequest($this->assemblyaiForm->api_key);

            if ($response['success']) {
                $this->connectionResult = 'success';
            } else {
                $this->connectionResult = 'error';
                $this->addError('assemblyai_connection', 'Connection failed: '.$response['error']);
            }

        } catch (\Exception $e) {
            $this->connectionResult = 'error';
            $this->addError('assemblyai_connection', 'Connection failed: '.$e->getMessage());
        } finally {
            $this->isTestingConnection = false;
        }
    }

    private function makeAssemblyAITestRequest(string $apiKey): array
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.assemblyai.com/v2/transcript');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: '.$apiKey,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 401) {
                return ['success' => false, 'error' => 'Invalid API key'];
            } elseif ($httpCode === 200 || $httpCode === 400) {
                // 200 = success, 400 = expected for empty request but API key is valid
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Unexpected response from AssemblyAI API'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.storage-account-dashboard');
    }
}
