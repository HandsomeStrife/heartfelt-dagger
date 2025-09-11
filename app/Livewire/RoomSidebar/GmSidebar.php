<?php

declare(strict_types=1);

namespace App\Livewire\RoomSidebar;

use Domain\Campaign\Models\Campaign;
use Domain\Room\Actions\GetGameStateAction as RoomGetGameStateAction;
use Domain\Room\Actions\LoadRoomSessionNotesAction;
use Domain\Room\Actions\ManageCountdownTrackerAction as RoomManageCountdownTrackerAction;
use Domain\Room\Actions\SaveRoomSessionNotesAction;
use Domain\Room\Actions\UpdateFearLevelAction as RoomUpdateFearLevelAction;
use Domain\Room\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GmSidebar extends Component
{
    public Room $room;

    public ?Campaign $campaign;

    public Collection $campaign_pages;

    public Collection $participants;

    public string $session_notes = '';

    // Game State Properties
    public int $current_fear_level = 0;

    public array $countdown_trackers = [];

    public int $fear_level_input = 0;

    // Countdown Form Properties
    public bool $show_add_countdown = false;

    public string $new_countdown_name = '';

    public int $new_countdown_value = 0;

    public Collection $campaign_handouts;

    public function mount(Room $room, ?Campaign $campaign, Collection $campaignPages, Collection $campaignHandouts, Collection $participants): void
    {
        $this->room = $room;
        $this->campaign = $campaign;
        $this->campaign_pages = $campaignPages;
        $this->campaign_handouts = $campaignHandouts;
        $this->participants = $participants;

        // Load existing session notes
        $this->loadSessionNotes();

        // Load game state
        $this->loadGameState();
    }

    public function loadSessionNotes(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $load_action = new LoadRoomSessionNotesAction;
        $notes_record = $load_action->execute($this->room, $user);
        $this->session_notes = $notes_record->notes ?? '';
    }

    public function saveSessionNotes(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $save_action = new SaveRoomSessionNotesAction;
        $save_action->execute($this->room, $user, $this->session_notes);

        // Dispatch a browser event to show success feedback
        $this->dispatch('notes-saved');
    }

    public function loadGameState(): void
    {
        $getGameStateAction = new RoomGetGameStateAction;
        $gameState = $getGameStateAction->execute($this->room);

        $this->current_fear_level = $gameState->fear_tracker->fear_level;
        $this->fear_level_input = $this->current_fear_level;
        $this->countdown_trackers = $gameState->countdown_trackers->mapWithKeys(function ($tracker) {
            return [$tracker->id => [
                'name' => $tracker->name,
                'value' => $tracker->value,
                'updated_at' => $tracker->updated_at->toISOString(),
            ]];
        })->toArray();
    }

    // Fear Level Management
    public function increaseFear(): void
    {
        $this->updateFearLevel($this->current_fear_level + 1);
    }

    public function decreaseFear(): void
    {
        $this->updateFearLevel(max(0, $this->current_fear_level - 1));
    }

    public function setFearLevel(): void
    {
        $this->updateFearLevel($this->fear_level_input);
    }

    private function updateFearLevel(int $newLevel): void
    {
        $updateFearAction = new RoomUpdateFearLevelAction;
        $fearTracker = $updateFearAction->execute($this->campaign, $this->room, $newLevel);

        $this->current_fear_level = $fearTracker->fear_level;
        $this->fear_level_input = $this->current_fear_level;

        // Emit Livewire event for local JavaScript integration
        $this->dispatch('fear-level-updated', [
            'fear_level' => $this->current_fear_level,
            'source_type' => $this->campaign ? 'campaign' : 'room',
            'source_id' => $this->campaign ? $this->campaign->id : $this->room->id,
        ]);

        // Send Ably message for real-time synchronization
        $this->sendAblyMessage('fear-updated', [
            'fear_level' => $this->current_fear_level,
            'source_type' => $this->campaign ? 'campaign' : 'room',
            'source_id' => $this->campaign ? $this->campaign->id : $this->room->id,
        ]);
    }

    // Countdown Tracker Management
    public function createCountdownTracker(): void
    {
        if (empty(trim($this->new_countdown_name))) {
            return;
        }

        $manageCountdownAction = new RoomManageCountdownTrackerAction;
        $tracker = $manageCountdownAction->createCountdownTracker(
            $this->campaign,
            $this->room,
            trim($this->new_countdown_name),
            max(0, $this->new_countdown_value)
        );

        // Update local state
        $this->countdown_trackers[$tracker->id] = [
            'name' => $tracker->name,
            'value' => $tracker->value,
            'updated_at' => $tracker->updated_at->toISOString(),
        ];

        // Reset form
        $this->show_add_countdown = false;
        $this->new_countdown_name = '';
        $this->new_countdown_value = 0;

        // Emit Livewire event for local JavaScript integration
        $this->dispatch('countdown-tracker-updated', [
            'tracker' => $tracker->toArray(),
            'action' => 'created',
        ]);

        // Send Ably message for real-time synchronization
        $this->sendAblyMessage('countdown-updated', [
            'tracker' => $tracker->toArray(),
            'action' => 'created',
        ]);
    }

    public function increaseCountdown(string $trackerId): void
    {
        $this->modifyCountdownValue($trackerId, 1);
    }

    public function decreaseCountdown(string $trackerId): void
    {
        $this->modifyCountdownValue($trackerId, -1);
    }

    private function modifyCountdownValue(string $trackerId, int $change): void
    {
        if (! isset($this->countdown_trackers[$trackerId])) {
            return;
        }

        $tracker = $this->countdown_trackers[$trackerId];
        $newValue = max(0, $tracker['value'] + $change);

        $manageCountdownAction = new RoomManageCountdownTrackerAction;
        $updatedTracker = $manageCountdownAction->updateCountdownTracker(
            $this->campaign,
            $this->room,
            $trackerId,
            $tracker['name'],
            $newValue
        );

        // Update local state
        $this->countdown_trackers[$trackerId] = [
            'name' => $updatedTracker->name,
            'value' => $updatedTracker->value,
            'updated_at' => $updatedTracker->updated_at->toISOString(),
        ];

        // Emit Livewire event for local JavaScript integration
        $this->dispatch('countdown-tracker-updated', [
            'tracker' => $updatedTracker->toArray(),
            'action' => 'updated',
        ]);

        // Send Ably message for real-time synchronization
        $this->sendAblyMessage('countdown-updated', [
            'tracker' => $updatedTracker->toArray(),
            'action' => 'updated',
        ]);
    }

    public function deleteCountdownTracker(string $trackerId): void
    {
        $manageCountdownAction = new RoomManageCountdownTrackerAction;
        $deleted = $manageCountdownAction->deleteCountdownTracker(
            $this->campaign,
            $this->room,
            $trackerId
        );

        if ($deleted) {
            unset($this->countdown_trackers[$trackerId]);

            // Emit Livewire event for local JavaScript integration
            $this->dispatch('countdown-tracker-deleted', [
                'tracker_id' => $trackerId,
            ]);

            // Send Ably message for real-time synchronization
            $this->sendAblyMessage('countdown-deleted', [
                'tracker_id' => $trackerId,
            ]);
        }
    }

    /**
     * Send an Ably message to synchronize game state across all participants
     */
    private function sendAblyMessage(string $type, array $data): void
    {
        // Use JavaScript dispatch to send Ably message
        $this->dispatch('send-ably-message', [
            'type' => $type,
            'data' => $data,
            'room_invite_code' => $this->room->invite_code,
        ]);
    }

    public function render()
    {
        return view('livewire.room-sidebar.gm-sidebar');
    }
}
