<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Room\Actions\UpdateRoomRecordingSettings;
use Domain\Room\Data\RoomRecordingSettingsData;
use Domain\Room\Data\RoomRecordingSettingsFormData;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class RoomRecordingSettings extends Component
{
    public Room $room;
    
    #[Validate]
    public RoomRecordingSettingsFormData $form;
    
    public Collection $wasabiAccounts;
    public Collection $googleDriveAccounts;
    public bool $canManageSettings = false;

    public function mount(Room $room): void
    {
        $this->room = $room;
        
        // Check if current user can manage settings
        $this->canManageSettings = auth()->check() && $this->room->creator_id === auth()->id();
        
        if (!$this->canManageSettings) {
            abort(403, 'Only the room creator can manage recording settings');
        }

        // Load current settings
        $this->room->load('recordingSettings');
        $currentSettings = $this->room->recordingSettings 
            ? RoomRecordingSettingsData::from($this->room->recordingSettings)
            : null;
            
        $this->form = RoomRecordingSettingsFormData::fromRoomRecordingSettings($currentSettings);
        
        // Load user's storage accounts
        $this->loadStorageAccounts();
    }

    public function loadStorageAccounts(): void
    {
        /** @var \Domain\User\Models\User $user */
        $user = auth()->user();
        
        $this->wasabiAccounts = $user->storageAccounts()
            ->where('provider', 'wasabi')
            ->where('is_active', true)
            ->get();
            
        $this->googleDriveAccounts = $user->storageAccounts()
            ->where('provider', 'google_drive')
            ->where('is_active', true)
            ->get();
    }

    public function rules(): array
    {
        return [
            'form.recording_enabled' => 'boolean',
            'form.stt_enabled' => 'boolean',
            'form.storage_provider' => 'nullable|in:local,wasabi,google_drive',
            'form.storage_account_id' => 'nullable|integer',
        ];
    }

    public function updatedFormRecordingEnabled(): void
    {
        // If recording is disabled, also disable STT and clear storage
        if (!$this->form->recording_enabled) {
            $this->form->stt_enabled = false;
            $this->form->storage_provider = null;
            $this->form->storage_account_id = null;
        } else if (!$this->form->storage_provider) {
            // If recording is enabled but no storage provider, default to local
            $this->form->storage_provider = 'local';
        }
    }

    public function updatedFormStorageProvider(): void
    {
        // Clear storage account when switching providers
        $this->form->storage_account_id = null;
        
        // If switching to local, no account needed
        if ($this->form->storage_provider === 'local') {
            return;
        }
        
        // Auto-select account if only one available
        $accounts = $this->getAccountsForProvider($this->form->storage_provider);
        if ($accounts->count() === 1) {
            $this->form->storage_account_id = $accounts->first()->id;
        }
    }

    public function getAccountsForProvider(?string $provider): Collection
    {
        return match ($provider) {
            'wasabi' => $this->wasabiAccounts,
            'google_drive' => $this->googleDriveAccounts,
            default => collect(),
        };
    }

    public function save(): void
    {
        $this->validate();
        
        if (!$this->form->isValid()) {
            $this->addError('form', $this->form->getValidationMessage());
            return;
        }

        try {
            $updateAction = new UpdateRoomRecordingSettings();
            
            /** @var User $user */
            $user = auth()->user();
            
            $updateAction->execute(
                $this->room,
                $user,
                $this->form->recording_enabled,
                $this->form->stt_enabled,
                $this->form->storage_provider,
                $this->form->storage_account_id
            );

            session()->flash('success', 'Recording settings updated successfully!');
            
            // Reload the room settings
            $this->room->refresh();
            $this->room->load('recordingSettings');
            
        } catch (\Exception $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    public function connectWasabi(): void
    {
        $this->redirect('/wasabi/connect?redirect_to=' . urlencode(request()->url()));
    }

    public function connectGoogleDrive(): void
    {
        $this->redirect('/google-drive/authorize?redirect_to=' . urlencode(request()->url()));
    }

    public function getStorageAccountName(?int $accountId): string
    {
        if (!$accountId) {
            return 'No account selected';
        }

        $account = UserStorageAccount::find($accountId);
        return $account ? $account->display_name : 'Unknown account';
    }

    public function render()
    {
        return view('livewire.room-recording-settings');
    }
}
