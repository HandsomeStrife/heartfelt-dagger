<?php

declare(strict_types=1);

namespace App\Livewire\RoomSidebar;

use Domain\Campaign\Models\Campaign;
use Domain\Room\Actions\LoadRoomSessionNotesAction;
use Domain\Room\Actions\SaveRoomSessionNotesAction;
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

    public function mount(Room $room, ?Campaign $campaign, Collection $campaignPages, Collection $participants): void
    {
        $this->room = $room;
        $this->campaign = $campaign;
        $this->campaign_pages = $campaignPages;
        $this->participants = $participants;
        
        // Load existing session notes
        $this->loadSessionNotes();
    }

    public function loadSessionNotes(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $load_action = new LoadRoomSessionNotesAction();
        $notes_record = $load_action->execute($this->room, $user);
        $this->session_notes = $notes_record->notes ?? '';
    }

    public function saveSessionNotes(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $save_action = new SaveRoomSessionNotesAction();
        $save_action->execute($this->room, $user, $this->session_notes);
        
        // Dispatch a browser event to show success feedback
        $this->dispatch('notes-saved');
    }

    public function render()
    {
        return view('livewire.room-sidebar.gm-sidebar');
    }
}
