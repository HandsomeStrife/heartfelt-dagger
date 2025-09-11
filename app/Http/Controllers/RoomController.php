<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Repositories\CampaignPageRepository;
use Domain\CampaignHandout\Repositories\CampaignHandoutRepository;
use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterRepository;
use Domain\Room\Actions\CreateRoomAction;
use Domain\Room\Actions\DeleteRoomAction;
use Domain\Room\Actions\JoinRoomAction;
use Domain\Room\Actions\KickUserAction;
use Domain\Room\Actions\LeaveRoomAction;
use Domain\Room\Data\CreateRoomData;
use Domain\Room\Models\Room;
use Domain\Room\Repositories\RoomRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    public function __construct(
        private RoomRepository $room_repository,
        private CharacterRepository $character_repository,
        private CampaignPageRepository $campaign_page_repository,
        private CampaignHandoutRepository $campaign_handout_repository,
        private CreateRoomAction $create_room_action,
        private DeleteRoomAction $delete_room_action,
        private JoinRoomAction $join_room_action,
        private KickUserAction $kick_user_action,
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
            'password' => 'nullable|string|max:255',
            'guest_count' => 'required|integer|min:2|max:6',
        ]);

        $createData = CreateRoomData::from($validated);
        $roomData = $this->create_room_action->execute($createData, Auth::user());

        // Get the actual Room model for route generation
        $room = Room::find($roomData->id);

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', 'Room created successfully!');
    }

    public function show(Request $request, Room $room)
    {
        $user = Auth::user();

        // Check if user can access this room
        if (! $room->canUserAccess($user)) {
            // Campaign rooms require authentication
            if ($room->campaign_id && ! $user) {
                return redirect()->route('login')->with('error', 'Please log in to access this campaign room.');
            }
            abort(403, 'You do not have access to this room.');
        }

        // Check password if room is password protected (creators bypass this check)
        if ($room->password && ! $room->isCreator($user)) {
            $passwordFromUrl = $request->query('password');

            if (! $passwordFromUrl || ! Hash::check($passwordFromUrl, $room->password)) {
                // If no password in URL or invalid password, redirect to join page
                return redirect()->route('rooms.invite', $room->invite_code);
            }
        }

        $participants = $this->room_repository->getRoomParticipants($room);

        $user_is_creator = $room->isCreator($user);
        $user_is_participant = $room->hasActiveParticipant($user);

        return view('rooms.show', compact('room', 'participants', 'user_is_creator', 'user_is_participant'));
    }

    public function showJoin(Request $request, string $invite_code)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : 'anonymous';

        Log::info('Room join page accessed', [
            'invite_code' => $invite_code,
            'user_id' => $userId,
            'user_type' => $user ? 'authenticated' : 'anonymous',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $room = Room::byInviteCode($invite_code)->first();

        if (! $room) {
            Log::warning('Room join page - Room not found', [
                'invite_code' => $invite_code,
                'user_id' => $userId,
            ]);
            abort(404, 'Room not found');
        }

        Log::info('Room found for join page', [
            'room_id' => $room->id,
            'room_name' => $room->name,
            'invite_code' => $invite_code,
            'user_id' => $userId,
            'is_campaign_room' => (bool) $room->campaign_id,
            'has_password' => (bool) $room->password,
        ]);

        // Check if user can access this room
        if (! $room->canUserAccess($user)) {
            // Campaign rooms require authentication
            if ($room->campaign_id && ! $user) {
                return redirect()->route('login')->with('error', 'Please log in to access this campaign room.');
            }
            abort(403, 'You do not have access to this room.');
        }

        // Auto-join campaign members (non-GMs) using their campaign character
        if ($room->campaign_id && $user) {
            $campaign = Campaign::find($room->campaign_id);
            if ($campaign) {
                $campaignMember = $campaign->members()->where('user_id', $user->id)->first();

                // If user is a campaign member but not the GM/creator, and has a character, auto-join them
                if ($campaignMember && $campaignMember->user_id !== $campaign->creator_id && $campaignMember->character) {

                    Log::info('Auto-joining campaign member to room', [
                        'room_id' => $room->id,
                        'user_id' => $user->id,
                        'campaign_id' => $campaign->id,
                        'character_id' => $campaignMember->character_id,
                    ]);

                    // Check if user is already participating
                    if (! $room->hasActiveParticipant($user)) {
                        // Auto-join with their campaign character
                        try {
                            $this->join_room_action->execute(
                                room: $room,
                                user: $user,
                                character: $campaignMember->character,
                                temporaryCharacterName: null,
                                temporaryCharacterClass: null
                            );

                            Log::info('Successfully auto-joined campaign member', [
                                'room_id' => $room->id,
                                'user_id' => $user->id,
                                'character_id' => $campaignMember->character_id,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to auto-join campaign member', [
                                'room_id' => $room->id,
                                'user_id' => $user->id,
                                'error' => $e->getMessage(),
                            ]);

                            // Fall back to showing join form
                            $characters = $this->character_repository->getByUser($user);

                            return view('rooms.join', compact('room', 'characters'));
                        }
                    }

                    // Redirect directly to the room session
                    $redirectUrl = route('rooms.session', $room);
                    if ($room->password && $request->query('password')) {
                        $redirectUrl .= '?password='.urlencode($request->query('password'));
                    }

                    return redirect($redirectUrl)
                        ->with('success', 'Joined room with your campaign character!');
                }
            }
        }

        // Check if user is already a participant
        $isAlreadyParticipating = false;
        $participantCheckDetails = [];

        if ($user) {
            // Authenticated user check
            $isAlreadyParticipating = $room->hasActiveParticipant($user);
            $participantCheckDetails = [
                'check_type' => 'authenticated',
                'is_participating' => $isAlreadyParticipating,
            ];
        } else {
            // Anonymous user check - see if they have a valid session
            $anonymousParticipantId = session('anonymous_room_participant_'.$room->id);
            $participantCheckDetails = [
                'check_type' => 'anonymous',
                'session_participant_id' => $anonymousParticipantId,
            ];

            if ($anonymousParticipantId) {
                $participant = $room->activeParticipants()
                    ->where('id', $anonymousParticipantId)
                    ->where('user_id', null)
                    ->first();
                $isAlreadyParticipating = (bool) $participant;
                $participantCheckDetails['participant_found'] = (bool) $participant;
                if ($participant) {
                    $participantCheckDetails['participant_id'] = $participant->id;
                }
            }
        }

        Log::info('Room join page - Participant check completed', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'is_already_participating' => $isAlreadyParticipating,
            'details' => $participantCheckDetails,
        ]);

        if ($isAlreadyParticipating) {
            // Check password for direct redirect to session page
            $passwordFromUrl = $request->query('password');
            $passwordValid = ! $room->password || ($passwordFromUrl && Hash::check($passwordFromUrl, $room->password));

            Log::info('Room join page - Already participating, checking password', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'room_has_password' => (bool) $room->password,
                'password_in_url' => (bool) $passwordFromUrl,
                'password_valid' => $passwordValid,
            ]);

            if (! $passwordValid) {
                // Password required but not provided/invalid, show join form
                Log::info('Room join page - Showing join form for password entry', [
                    'room_id' => $room->id,
                    'user_id' => $userId,
                ]);
                $characters = $user ? $this->character_repository->getByUser($user) : collect();

                return view('rooms.join', compact('room', 'characters'));
            }

            // Redirect to session page instead of show page for already participating users
            $redirectUrl = route('rooms.session', $room);
            if ($passwordFromUrl) {
                $redirectUrl .= '?password='.urlencode($passwordFromUrl);
            }

            Log::info('Room join page - Redirecting already participating user to session', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'redirect_url' => $redirectUrl,
            ]);

            return redirect($redirectUrl)
                ->with('info', 'You are already participating in this room.');
        }

        // Check if room is at capacity
        if ($room->isAtCapacity()) {
            Log::warning('Room join page - Room at capacity', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'current_participants' => $room->getActiveParticipantCount(),
                'total_capacity' => $room->getTotalCapacity(),
            ]);

            return redirect()->back()
                ->with('error', 'This room is at capacity.');
        }

        $characters = $user ? $this->character_repository->getByUser($user) : collect();

        Log::info('Room join page - Showing join form', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'character_count' => $characters->count(),
            'room_capacity' => $room->getActiveParticipantCount().'/'.$room->getTotalCapacity(),
        ]);

        return view('rooms.join', compact('room', 'characters'));
    }

    public function join(Request $request, Room $room)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : 'anonymous';

        Log::info('Room join form submitted', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'user_type' => $user ? 'authenticated' : 'anonymous',
            'ip' => $request->ip(),
            'form_data' => [
                'has_password' => ! empty($request->input('password')),
                'character_id' => $request->input('character_id'),
                'character_name' => $request->input('character_name'),
                'character_class' => $request->input('character_class'),
            ],
        ]);

        $validated = $request->validate([
            'password' => $room->password ? 'required|string' : 'nullable|string',
            'character_id' => 'nullable|exists:characters,id',
            'character_name' => 'required_without:character_id|nullable|string|max:100',
            'character_class' => 'required_without:character_id|nullable|string|max:50|in:Bard,Druid,Guardian,Ranger,Rogue,Seraph,Sorcerer,Warrior,Wizard',
        ]);

        // Convert empty strings to null for proper validation logic
        if (empty($validated['character_id'])) {
            $validated['character_id'] = null;
        }

        // Verify room password if room has one
        if ($room->password && ! Hash::check($validated['password'], $room->password)) {
            Log::warning('Room join - Invalid password', [
                'room_id' => $room->id,
                'user_id' => $userId,
            ]);

            return back()->withErrors(['password' => 'Invalid room password.']);
        }

        // Campaign rooms require authentication
        if ($room->campaign_id && ! $user) {
            Log::warning('Room join - Campaign room requires authentication', [
                'room_id' => $room->id,
                'user_id' => $userId,
            ]);

            return redirect()->route('login')->with('error', 'Please log in to join this campaign room.');
        }

        // Check if user can access this room
        if (! $room->canUserAccess($user)) {
            Log::warning('Room join - Access denied', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'is_campaign_room' => (bool) $room->campaign_id,
            ]);
            if ($room->campaign_id && ! $user) {
                return redirect()->route('login')->with('error', 'Please log in to access this campaign room.');
            }
            abort(403, 'You do not have access to this room.');
        }

        $character = null;

        // Validate character ownership if provided
        if ($validated['character_id'] ?? null) {
            if (! $user) {
                return back()->withErrors(['character_id' => 'You must be logged in to use an existing character.'])->withInput();
            }

            $character = Character::where('id', $validated['character_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        Log::info('Room join - Attempting to create participant', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'character_id' => $character ? $character->id : null,
            'temporary_character_name' => $validated['character_name'] ?? null,
            'temporary_character_class' => $validated['character_class'] ?? null,
        ]);

        try {
            $participant = $this->join_room_action->execute(
                $room,
                $user,
                $character,
                $validated['character_name'] ?? null,
                $validated['character_class'] ?? null
            );

            Log::info('Room join - Participant created successfully', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'participant_id' => $participant->id,
                'participant_user_id' => $participant->user_id,
                'participant_character_id' => $participant->character_id,
                'participant_character_name' => $participant->character_name,
                'participant_character_class' => $participant->character_class,
            ]);

            // For anonymous users, store their participant ID in session to track them
            if (! $user) {
                session(['anonymous_room_participant_'.$room->id => $participant->id]);
                Log::info('Room join - Anonymous user session stored', [
                    'room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'session_key' => 'anonymous_room_participant_'.$room->id,
                ]);
            }

            // Build session URL with password if room is password protected
            $sessionUrl = route('rooms.session', $room);
            if ($room->password && isset($validated['password'])) {
                $sessionUrl .= '?password='.urlencode($validated['password']);
            }

            Log::info('Room join - Redirecting to session', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'participant_id' => $participant->id,
                'redirect_url' => $sessionUrl,
                'includes_password' => $room->password && isset($validated['password']),
            ]);

            return redirect($sessionUrl)
                ->with('success', 'Successfully joined the room!');
        } catch (\Exception $e) {
            Log::error('Room join - Failed to create participant', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

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

    public function kickParticipant(Room $room, int $participant)
    {
        $user = Auth::user();

        Log::info('Room kick participant request', [
            'room_id' => $room->id,
            'kicking_user_id' => $user->id,
            'participant_id' => $participant,
            'ip' => request()->ip(),
        ]);

        try {
            $this->kick_user_action->execute($room, $user, $participant);

            return redirect()
                ->back()
                ->with('success', 'Participant has been removed from the room.');
        } catch (\Exception $e) {
            Log::error('Room kick participant failed', [
                'room_id' => $room->id,
                'kicking_user_id' => $user->id,
                'participant_id' => $participant,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Room $room)
    {
        $user = Auth::user();

        try {
            $this->delete_room_action->execute($room, $user);

            return redirect()
                ->route('rooms.index')
                ->with('success', 'Room deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function kickParticipant(Request $request, Room $room)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $validated = $request->validate([
            'participant_id' => 'required|integer|exists:room_participants,id',
        ]);

        try {
            $kickAction = new \Domain\Room\Actions\KickParticipantAction();
            $kickAction->execute($room, $user, $validated['participant_id']);

            return response()->json([
                'success' => true,
                'message' => 'Participant kicked successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function session(Request $request, Room $room)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : 'anonymous';

        Log::info('Room session page accessed', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'user_type' => $user ? 'authenticated' : 'anonymous',
            'ip' => $request->ip(),
            'referrer' => $request->header('referer'),
            'query_params' => $request->query(),
        ]);

        // Check if user can access this room
        if (! $room->canUserAccess($user)) {
            Log::warning('Room session - Access denied', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'is_campaign_room' => (bool) $room->campaign_id,
                'reason' => $room->campaign_id && ! $user ? 'campaign_requires_auth' : 'access_denied',
            ]);

            // Campaign rooms require authentication
            if ($room->campaign_id && ! $user) {
                return redirect()->route('login')->with('error', 'Please log in to access this campaign room.');
            }
            abort(403, 'You do not have access to this room.');
        }

        // Check password if room is password protected (creators bypass this check)
        if ($room->password && ! $room->isCreator($user)) {
            $passwordFromUrl = $request->query('password');

            if (! $passwordFromUrl || ! Hash::check($passwordFromUrl, $room->password)) {
                // If no password in URL or invalid password, redirect to join page
                return redirect()->route('rooms.invite', $room->invite_code);
            }
        }

        // Check if user is participating (handle both authenticated and anonymous users)
        $isParticipating = false;
        $participationDetails = [];

        if ($user) {
            // Authenticated user - check if they're the creator or an active participant
            $isCreator = $room->isCreator($user);
            $isActiveParticipant = $room->hasActiveParticipant($user);
            $isParticipating = $isCreator || $isActiveParticipant;
            $participationDetails = [
                'check_type' => 'authenticated',
                'is_creator' => $isCreator,
                'is_active_participant' => $isActiveParticipant,
                'is_participating' => $isParticipating,
            ];
        } else {
            // Anonymous user - check if they have a valid participant session
            $anonymousParticipantId = session('anonymous_room_participant_'.$room->id);
            $participationDetails = [
                'check_type' => 'anonymous',
                'session_participant_id' => $anonymousParticipantId,
            ];

            if ($anonymousParticipantId) {
                // Verify the participant still exists and is active
                $participant = $room->activeParticipants()
                    ->where('id', $anonymousParticipantId)
                    ->where('user_id', null) // Ensure it's an anonymous participant
                    ->first();
                $isParticipating = (bool) $participant;
                $participationDetails['participant_found'] = (bool) $participant;
                if ($participant) {
                    $participationDetails['participant_id'] = $participant->id;
                    $participationDetails['participant_character_name'] = $participant->character_name;
                }
            }
        }

        Log::info('Room session - Participation check completed', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'is_participating' => $isParticipating,
            'details' => $participationDetails,
        ]);

        // If not participating, redirect to join
        if (! $isParticipating) {
            Log::info('Room session - Redirecting to join page (not participating)', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'participation_details' => $participationDetails,
            ]);

            return redirect()->route('rooms.invite', $room->invite_code)
                ->with('error', 'You must join the room first.');
        }

        $participants = $this->room_repository->getRoomParticipants($room);

        // Determine user role and load additional data for campaign rooms
        $user_is_creator = $room->isCreator($user);
        $current_participant = null;
        $campaign_pages = collect();
        $campaign_handouts = collect();
        $campaign = null;

        // Find current user's participant record
        if ($user) {
            $current_participant = $participants->first(fn ($p) => $p->user_id === $user->id);
        } else {
            // Anonymous user - find by session
            $anonymousParticipantId = session('anonymous_room_participant_'.$room->id);
            if ($anonymousParticipantId) {
                $current_participant = $participants->first(fn ($p) => $p->id === $anonymousParticipantId);
            }
        }

        // Load campaign data if this is a campaign room
        if ($room->campaign_id) {
            $campaign = Campaign::with(['creator', 'members'])->find($room->campaign_id);

            if ($campaign) {
                // Load campaign pages for GM
                if ($user_is_creator) {
                    $campaign_pages = $this->campaign_page_repository->getRootPagesForCampaign($campaign, $user);
                }

                // Load handouts visible in sidebar (for both GM and players)
                $campaign_handouts = $this->campaign_handout_repository->getVisibleInSidebar($campaign, $user);
            }
        }

        Log::info('Room session - Rendering session page', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'participant_count' => $participants->count(),
            'room_capacity' => $room->getActiveParticipantCount().'/'.$room->getTotalCapacity(),
            'is_campaign_room' => (bool) $room->campaign_id,
            'user_is_creator' => $user_is_creator,
            'has_current_participant' => (bool) $current_participant,
            'campaign_pages_count' => $campaign_pages->count(),
            'participants' => $participants->map(fn ($p) => [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'character_name' => $p->character_name ?: ($p->user ? $p->user->username : 'Unknown'),
                'character_class' => $p->character_class ?: ($p->character ? $p->character->class : 'Unknown'),
            ]),
        ]);

        // Load recording settings for this room
        $room->load('recordingSettings');

        return view('rooms.session', compact(
            'room',
            'participants',
            'user_is_creator',
            'current_participant',
            'campaign',
            'campaign_pages',
            'campaign_handouts'
        ));
    }

    /**
     * Show the create room form for a specific campaign
     */
    public function createForCampaign(Campaign $campaign)
    {
        $user = Auth::user();

        // Check if user can access this campaign
        if (! $campaign->canUserAccess($user)) {
            abort(403, 'You do not have access to this campaign.');
        }

        return view('rooms.create-for-campaign', compact('campaign'));
    }

    /**
     * Store a new room for a specific campaign
     */
    public function storeForCampaign(Request $request, Campaign $campaign)
    {
        $user = Auth::user();

        // Check if user can access this campaign
        if (! $campaign->canUserAccess($user)) {
            abort(403, 'You do not have access to this campaign.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'guest_count' => 'required|integer|min:2|max:6',
        ]);

        $validated['campaign_id'] = $campaign->id;
        $validated['password'] = null; // Campaign rooms don't use passwords
        $createData = CreateRoomData::from($validated);
        $roomData = $this->create_room_action->execute($createData, $user);

        // Get the actual Room model for route generation
        $room = Room::find($roomData->id);

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', 'Campaign room created successfully!');
    }

    /**
     * Show the viewer interface for a room (read-only access)
     */
    public function viewer(Request $request, string $viewer_code)
    {
        $room = Room::byViewerCode($viewer_code)->with('recordingSettings')->first();

        if (! $room) {
            abort(404, 'Room not found');
        }

        // Check if viewer password is required
        if ($room->recordingSettings && $room->recordingSettings->hasViewerPassword()) {
            $passwordFromUrl = $request->query('password');

            if (! $passwordFromUrl || ! $room->recordingSettings->verifyViewerPassword($passwordFromUrl)) {
                // Show password form or redirect to password entry
                return view('rooms.viewer-password', compact('room'));
            }
        }

        $participants = $this->room_repository->getRoomParticipants($room);

        return view('rooms.viewer', compact('room', 'participants'));
    }

    /**
     * Handle viewer password submission
     */
    public function viewerPassword(Request $request, string $viewer_code)
    {
        $room = Room::byViewerCode($viewer_code)->with('recordingSettings')->first();

        if (! $room) {
            abort(404, 'Room not found');
        }

        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        // Check if password is correct
        if ($room->recordingSettings && $room->recordingSettings->verifyViewerPassword($validated['password'])) {
            // Redirect to viewer with password in URL
            return redirect(route('rooms.viewer', $viewer_code).'?password='.urlencode($validated['password']));
        }

        return back()->withErrors(['password' => 'Invalid viewer password.']);
    }
}
