<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterRepository;
use Domain\Room\Actions\CreateRoomAction;
use Domain\Room\Actions\JoinRoomAction;
use Domain\Room\Actions\LeaveRoomAction;
use Domain\Room\Data\CreateRoomData;
use Domain\Room\Models\Room;
use Domain\Room\Repositories\RoomRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RoomController extends Controller
{
    public function __construct(
        private RoomRepository $room_repository,
        private CharacterRepository $character_repository,
        private CreateRoomAction $create_room_action,
        private JoinRoomAction $join_room_action,
        private LeaveRoomAction $leave_room_action,
    ) {}

    public function index()
    {
        $user = Auth::user();
        
        $created_rooms = $this->room_repository->getCreatedByUser($user);
        $joined_rooms = $this->room_repository->getJoinedByUser($user);

        return view('rooms.index', compact('created_rooms', 'joined_rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'password' => 'required|string|max:255',
            'guest_count' => 'required|integer|min:1|max:5',
        ]);

        $createData = CreateRoomData::from($validated);
        $room = $this->create_room_action->execute($createData, Auth::user());

        return redirect()
            ->route('rooms.show', ['room' => $room->id])
            ->with('success', 'Room created successfully!');
    }

    public function show(Room $room)
    {
        $user = Auth::user();
        $participants = $this->room_repository->getRoomParticipants($room);
        
        $user_is_creator = $room->isCreator($user);
        $user_is_participant = $room->hasActiveParticipant($user);

        return view('rooms.show', compact('room', 'participants', 'user_is_creator', 'user_is_participant'));
    }

    public function showJoin(string $invite_code)
    {
        $user = Auth::user();
        $room = Room::byInviteCode($invite_code)->first();

        if (!$room) {
            abort(404, 'Room not found');
        }

        // Check if user is already a participant
        if ($room->hasActiveParticipant($user)) {
            return redirect()
                ->route('rooms.show', ['room' => $room->id])
                ->with('info', 'You are already participating in this room.');
        }

        // Check if room is at capacity
        if ($room->isAtCapacity()) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'This room is at capacity.');
        }

        $characters = $this->character_repository->getByUser($user);

        return view('rooms.join', compact('room', 'characters'));
    }

    public function join(Request $request, Room $room)
    {
        $validated = $request->validate([
            'password' => 'required|string',
            'character_id' => 'nullable|exists:characters,id',
            'character_name' => 'nullable|string|max:100',
            'character_class' => 'nullable|string|max:50',
        ]);

        // Verify room password
        if (!Hash::check($validated['password'], $room->password)) {
            return back()->withErrors(['password' => 'Invalid room password.']);
        }

        $user = Auth::user();
        $character = null;

        // Validate character ownership if provided
        if ($validated['character_id'] ?? null) {
            $character = Character::where('id', $validated['character_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        try {
            $this->join_room_action->execute(
                $room,
                $user,
                $character,
                $validated['character_name'] ?? null,
                $validated['character_class'] ?? null
            );

            return redirect()
                ->route('rooms.session', ['room' => $room->id])
                ->with('success', 'Successfully joined the room!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function leave(Room $room)
    {
        $user = Auth::user();

        try {
            $this->leave_room_action->execute($room, $user);

            return redirect()
                ->route('rooms.index')
                ->with('success', 'Successfully left the room.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function session(Room $room)
    {
        $user = Auth::user();

        // Check if user is participating in this room
        if (!$room->hasActiveParticipant($user)) {
            return redirect()
                ->route('rooms.show', ['room' => $room->id])
                ->with('error', 'You must join the room first.');
        }

        $participants = $this->room_repository->getRoomParticipants($room);
        
        return view('rooms.session', compact('room', 'participants'));
    }
}
