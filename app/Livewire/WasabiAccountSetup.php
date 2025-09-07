<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Forms\WasabiAccountForm;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\UserStorageAccount;
use Livewire\Component;

class WasabiAccountSetup extends Component
{
    public WasabiAccountForm $form;
    public bool $isTestingConnection = false;
    public ?string $connectionResult = null;
    public ?string $redirectTo = null;

    public function mount(): void
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'You must be logged in to connect storage accounts');
        }

        // Get redirect URL from request
        $this->redirectTo = request()->get('redirect_to');
    }

    public function save(): void
    {
        try {
            // Create the storage account
            $account = $this->form->store();

            session()->flash('success', 'Wasabi account connected successfully!');

            // Redirect to appropriate page
            if ($this->redirectTo) {
                $this->redirect($this->redirectTo);
            } else {
                $this->redirect('/dashboard'); // or appropriate route
            }

        } catch (\Exception $e) {
            $this->addError('form', 'Failed to connect Wasabi account: ' . $e->getMessage());
        }
    }

    public function testConnection(): void
    {
        $this->validate();
        $this->isTestingConnection = true;
        $this->connectionResult = null;

        try {
            // Create temporary credentials for testing
            $testCredentials = [
                'access_key_id' => $this->form->access_key_id,
                'secret_access_key' => $this->form->secret_access_key,
                'bucket_name' => $this->form->bucket_name,
                'region' => $this->form->region,
                'endpoint' => $this->form->endpoint ?: "https://s3.{$this->form->region}.wasabisys.com",
            ];

            // Create a temporary storage account for testing
            $tempStorageAccount = new UserStorageAccount([
                'provider' => 'wasabi',
                'encrypted_credentials' => $testCredentials,
            ]);

            // Test the connection using the service's built-in test method
            $wasabiService = new WasabiS3Service($tempStorageAccount);
            $connectionSuccess = $wasabiService->testConnection();

            if ($connectionSuccess) {
                $this->connectionResult = 'success';
            } else {
                $this->connectionResult = 'error';
                $this->addError('connection', 'Connection test failed. Please check your credentials and try again.');
            }

        } catch (\Exception $e) {
            $this->connectionResult = 'error';
            $this->addError('connection', 'Connection failed: ' . $e->getMessage());
        } finally {
            $this->isTestingConnection = false;
        }
    }

    public function render()
    {
        return view('livewire.wasabi-account-setup');
    }
}
