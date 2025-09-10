<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AssemblyAIAccountForm extends Form
{
    #[Validate('required|string|max:100')]
    public string $display_name = '';

    #[Validate('required|string|max:200')]
    public string $api_key = '';

    public function rules(): array
    {
        return [
            'display_name' => 'required|string|max:100',
            'api_key' => 'required|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Please provide a name for this account.',
            'api_key.required' => 'AssemblyAI API Key is required.',
        ];
    }

    public function store(): UserStorageAccount
    {
        $this->validate();

        // Prepare credentials
        $credentials = [
            'api_key' => $this->api_key,
        ];

        // Create storage account
        return UserStorageAccount::create([
            'user_id' => Auth::id(),
            'provider' => 'assemblyai',
            'encrypted_credentials' => $credentials,
            'display_name' => $this->display_name,
            'is_active' => true,
        ]);
    }

    public function fillFromAccount(UserStorageAccount $account): void
    {
        $credentials = $account->encrypted_credentials;

        $this->display_name = $account->display_name;
        $this->api_key = $credentials['api_key'] ?? '';
    }

    public function reset(...$properties): void
    {
        if (empty($properties)) {
            $this->display_name = '';
            $this->api_key = '';
        } else {
            parent::reset(...$properties);
        }
    }
}
