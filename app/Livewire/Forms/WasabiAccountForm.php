<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\User\Models\UserStorageAccount;
use Livewire\Attributes\Validate;
use Livewire\Form;

class WasabiAccountForm extends Form
{
    #[Validate('required|string|max:100')]
    public string $display_name = '';

    #[Validate('required|string|max:100')]
    public string $access_key_id = '';

    #[Validate('required|string|max:100')]
    public string $secret_access_key = '';

    #[Validate('required|string|max:100')]
    public string $bucket_name = '';

    #[Validate('required|string|max:100')]
    public string $region = 'us-east-1';

    #[Validate('nullable|string|max:200')]
    public string $endpoint = '';

    public function rules(): array
    {
        return [
            'display_name' => 'required|string|max:100',
            'access_key_id' => 'required|string|max:100',
            'secret_access_key' => 'required|string|max:100',
            'bucket_name' => 'required|string|max:100|regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/',
            'region' => 'required|string|max:100',
            'endpoint' => 'nullable|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Please provide a name for this account.',
            'access_key_id.required' => 'Wasabi Access Key ID is required.',
            'secret_access_key.required' => 'Wasabi Secret Access Key is required.',
            'bucket_name.required' => 'Bucket name is required.',
            'bucket_name.regex' => 'Bucket name must contain only lowercase letters, numbers, and hyphens.',
            'region.required' => 'Region is required.',
        ];
    }

    public function store(): UserStorageAccount
    {
        $this->validate();

        // Prepare credentials
        $credentials = [
            'access_key_id' => $this->access_key_id,
            'secret_access_key' => $this->secret_access_key,
            'bucket_name' => $this->bucket_name,
            'region' => $this->region,
        ];

        // Add endpoint if provided, otherwise use default Wasabi endpoint
        if (! empty($this->endpoint)) {
            $credentials['endpoint'] = $this->endpoint;
        } else {
            $credentials['endpoint'] = "https://s3.{$this->region}.wasabisys.com";
        }

        // Create storage account
        return UserStorageAccount::create([
            'user_id' => auth()->id(),
            'provider' => 'wasabi',
            'encrypted_credentials' => $credentials,
            'display_name' => $this->display_name,
            'is_active' => true,
        ]);
    }

    public function fillFromAccount(UserStorageAccount $account): void
    {
        $credentials = $account->encrypted_credentials;

        $this->display_name = $account->display_name;
        $this->access_key_id = $credentials['access_key_id'] ?? '';
        $this->secret_access_key = $credentials['secret_access_key'] ?? '';
        $this->bucket_name = $credentials['bucket_name'] ?? '';
        $this->region = $credentials['region'] ?? 'us-east-1';
        $this->endpoint = $credentials['endpoint'] ?? '';
    }

    public function reset(...$properties): void
    {
        if (empty($properties)) {
            $this->display_name = '';
            $this->access_key_id = '';
            $this->secret_access_key = '';
            $this->bucket_name = '';
            $this->region = 'us-east-1';
            $this->endpoint = '';
        } else {
            parent::reset(...$properties);
        }
    }
}
