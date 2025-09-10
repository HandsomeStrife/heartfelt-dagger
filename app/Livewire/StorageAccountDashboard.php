<?php

declare(strict_types=1);

namespace App\Livewire;

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

    public bool $showAddAccountModal = false;

    public ?string $selectedProvider = null;

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

        // Test by getting the user profile (minimal API call)
        $service = $googleDriveService->initializeClient();
        $driveService = new \Google\Service\Drive($service);

        // Try to get information about the user's drive
        $driveService->about->get(['fields' => 'user']);
    }

    public function render()
    {
        return view('livewire.storage-account-dashboard');
    }
}
