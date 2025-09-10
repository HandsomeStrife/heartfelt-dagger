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

    public Collection $assemblyAIAccounts;

    public bool $canManageSettings = false;

    public function mount(Room $room): void
    {
        $this->room = $room;

        // Check if current user can manage settings
        $this->canManageSettings = auth()->check() && $this->room->creator_id === auth()->id();

        if (! $this->canManageSettings) {
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

        $this->assemblyAIAccounts = $user->storageAccounts()
            ->where('provider', 'assemblyai')
            ->where('is_active', true)
            ->get();
    }

    public function rules(): array
    {
        return [
            'form.recording_enabled' => 'boolean',
            'form.stt_enabled' => 'boolean',
            'form.storage_provider' => 'nullable|in:local_device,wasabi,google_drive',
            'form.storage_account_id' => 'nullable|integer',
            'form.stt_provider' => 'nullable|in:browser,assemblyai',
            'form.stt_account_id' => 'nullable|integer',
            'form.stt_consent_requirement' => 'in:optional,required',
            'form.recording_consent_requirement' => 'in:optional,required',
            'form.viewer_password' => 'nullable|string|min:3|max:255',
        ];
    }

    public function updatedFormRecordingEnabled(): void
    {
        // If recording is disabled, clear storage settings
        if (! $this->form->recording_enabled) {
            $this->form->storage_provider = null;
            $this->form->storage_account_id = null;
        } elseif (! $this->form->storage_provider) {
            // If recording is enabled but no storage provider, default to local_device
            $this->form->storage_provider = 'local_device';
        }
    }

    public function updatedFormSttEnabled(): void
    {
        // If STT is disabled, clear STT settings
        if (! $this->form->stt_enabled) {
            $this->form->stt_provider = null;
            $this->form->stt_account_id = null;
        } elseif (! $this->form->stt_provider) {
            // If STT is enabled but no provider, default to browser
            $this->form->stt_provider = 'browser';
        }
    }

    public function updatedFormStorageProvider(): void
    {
        // Clear storage account when switching providers
        $this->form->storage_account_id = null;

        // If switching to local_device, no account needed
        if ($this->form->storage_provider === 'local_device') {
            return;
        }

        // Auto-select account if only one available
        $accounts = $this->getAccountsForProvider($this->form->storage_provider);
        if ($accounts->count() === 1) {
            $this->form->storage_account_id = $accounts->first()->id;
        }
    }

    public function updatedFormSttProvider(): void
    {
        // Clear STT account when switching providers
        $this->form->stt_account_id = null;

        // If switching to browser, no account needed
        if ($this->form->stt_provider === 'browser') {
            return;
        }

        // Auto-select account if only one available
        $accounts = $this->getSttAccountsForProvider($this->form->stt_provider);
        if ($accounts->count() === 1) {
            $this->form->stt_account_id = $accounts->first()->id;
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

    public function getSttAccountsForProvider(?string $provider): Collection
    {
        return match ($provider) {
            'assemblyai' => $this->assemblyAIAccounts,
            default => collect(),
        };
    }

    public function save(): void
    {
        $this->validate();

        if (! $this->form->isValid()) {
            $this->addError('form', $this->form->getValidationMessage());

            return;
        }

        try {
            $updateAction = new UpdateRoomRecordingSettings;

            /** @var User $user */
            $user = auth()->user();

            $updateAction->execute(
                $this->room,
                $user,
                $this->form->recording_enabled,
                $this->form->stt_enabled,
                $this->form->storage_provider,
                $this->form->storage_account_id,
                $this->form->stt_provider,
                $this->form->stt_account_id,
                $this->form->stt_consent_requirement,
                $this->form->recording_consent_requirement,
                $this->form->viewer_password
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
        $this->redirect('/wasabi/connect?redirect_to='.urlencode(request()->url()));
    }

    public function connectGoogleDrive(): void
    {
        $this->redirect('/google-drive/authorize?redirect_to='.urlencode(request()->url()));
    }

    public function getStorageAccountName(?int $accountId): string
    {
        if (! $accountId) {
            return 'No account selected';
        }

        $account = UserStorageAccount::find($accountId);

        return $account ? $account->display_name : 'Unknown account';
    }

    /**
     * Get participants with their consent status
     */
    public function getParticipantsWithConsent(): Collection
    {
        return $this->room->participants()
            ->with(['user'])
            ->whereNull('left_at')
            ->get()
            ->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'user_id' => $participant->user_id,
                    'display_name' => $participant->getDisplayName(),
                    'character_class' => $participant->getCharacterClass(),
                    'stt_consent_given' => $participant->stt_consent_given,
                    'stt_consent_at' => $participant->stt_consent_at,
                    'recording_consent_given' => $participant->recording_consent_given,
                    'recording_consent_at' => $participant->recording_consent_at,
                    'joined_at' => $participant->joined_at,
                ];
            });
    }

    /**
     * Reset STT consent for a specific participant
     */
    public function resetSttConsent(int $participantId): void
    {
        $participant = $this->room->participants()->find($participantId);

        if (! $participant) {
            session()->flash('error', 'Participant not found.');

            return;
        }

        $participant->resetSttConsent();
        session()->flash('success', "STT consent reset for {$participant->getDisplayName()}.");
    }

    /**
     * Reset recording consent for a specific participant
     */
    public function resetRecordingConsent(int $participantId): void
    {
        $participant = $this->room->participants()->find($participantId);

        if (! $participant) {
            session()->flash('error', 'Participant not found.');

            return;
        }

        $participant->resetRecordingConsent();
        session()->flash('success', "Recording consent reset for {$participant->getDisplayName()}.");
    }

    /**
     * Reset all STT consent decisions
     */
    public function resetAllSttConsent(): void
    {
        $count = $this->room->participants()
            ->whereNull('left_at')
            ->whereNotNull('stt_consent_given')
            ->count();

        $this->room->participants()
            ->whereNull('left_at')
            ->whereNotNull('stt_consent_given')
            ->each(function ($participant) {
                $participant->resetSttConsent();
            });

        session()->flash('success', "STT consent reset for {$count} participants.");
    }

    /**
     * Reset all recording consent decisions
     */
    public function resetAllRecordingConsent(): void
    {
        $count = $this->room->participants()
            ->whereNull('left_at')
            ->whereNotNull('recording_consent_given')
            ->count();

        $this->room->participants()
            ->whereNull('left_at')
            ->whereNotNull('recording_consent_given')
            ->each(function ($participant) {
                $participant->resetRecordingConsent();
            });

        session()->flash('success', "Recording consent reset for {$count} participants.");
    }

    public function render()
    {
        $participants = $this->getParticipantsWithConsent();

        return view('livewire.room-recording-settings', [
            'participants' => $participants,
        ]);
    }
}
