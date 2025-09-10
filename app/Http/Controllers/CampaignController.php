<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Campaign\Actions\CreateCampaignAction;
use Domain\Campaign\Actions\JoinCampaignAction;
use Domain\Campaign\Actions\LeaveCampaignAction;
use Domain\Campaign\Data\CreateCampaignData;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Repositories\CampaignRepository;
use Domain\CampaignFrame\Repositories\CampaignFrameRepository;
use Domain\Character\Models\Character;
use Domain\Room\Repositories\RoomRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignRepository $campaign_repository,
        private CampaignFrameRepository $campaign_frame_repository,
        private RoomRepository $room_repository,
        private CreateCampaignAction $create_campaign_action,
        private JoinCampaignAction $join_campaign_action,
        private LeaveCampaignAction $leave_campaign_action,
    ) {}

    /**
     * Show the campaigns dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $created_campaigns = $this->campaign_repository->getCreatedByUser($user);
        $joined_campaigns = $this->campaign_repository->getJoinedByUser($user);

        return view('campaigns.index', [
            'created_campaigns' => $created_campaigns,
            'joined_campaigns' => $joined_campaigns,
        ]);
    }

    /**
     * Show the create campaign form
     */
    public function create()
    {
        $available_frames = $this->campaign_frame_repository->getFramesAvailableForCampaign(Auth::user());

        return view('campaigns.create', [
            'available_frames' => $available_frames,
        ]);
    }

    /**
     * Store a new campaign
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'campaign_frame_id' => 'nullable|exists:campaign_frames,id',
        ]);

        $create_data = CreateCampaignData::from($validated);
        $campaign = $this->create_campaign_action->execute($create_data, Auth::user());

        return redirect()
            ->route('campaigns.show', ['campaign' => $campaign->campaign_code])
            ->with('success', 'Campaign created successfully!');
    }

    /**
     * Show a specific campaign
     */
    public function show(Campaign $campaign)
    {
        $campaign_data = $this->campaign_repository->findById($campaign->id);
        $members = $this->campaign_repository->getCampaignMembers($campaign);
        $campaign_rooms = $this->room_repository->getRoomsByCampaign($campaign);
        $user_is_member = $campaign->hasMember(Auth::user());
        $user_is_creator = $campaign->isCreator(Auth::user());

        return view('campaigns.show', [
            'campaign' => $campaign_data,
            'campaign_model' => $campaign,
            'members' => $members,
            'campaign_rooms' => $campaign_rooms,
            'user_is_member' => $user_is_member,
            'user_is_creator' => $user_is_creator,
        ]);
    }

    /**
     * Show join campaign form via invite code
     */
    public function showJoin(string $invite_code)
    {
        $campaign = Campaign::byInviteCode($invite_code)->firstOrFail();
        $user = Auth::user();

        // Check if user is already a member
        if ($campaign->hasMember($user)) {
            return redirect()
                ->route('campaigns.show', ['campaign' => $campaign])
                ->with('info', 'You are already a member of this campaign.');
        }

        // Get user's characters for selection
        $characters = $user->characters()->get();

        return view('campaigns.join', [
            'campaign' => $campaign,
            'characters' => $characters,
        ]);
    }

    /**
     * Join a campaign by invite code
     */
    public function joinByCode(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        $campaign = Campaign::where('invite_code', $validated['invite_code'])->first();

        if (! $campaign) {
            return redirect()->route('campaigns.index')
                ->withErrors(['invite_code' => 'Invalid invite code. Please check the code and try again.']);
        }

        // Check if user is already a member
        $user = Auth::user();
        if ($campaign->hasMember($user)) {
            return redirect()->route('campaigns.show', $campaign->campaign_code)
                ->with('info', 'You are already a member of this campaign.');
        }

        try {
            $this->join_campaign_action->execute($campaign, $user);

            return redirect()->route('campaigns.show', $campaign->campaign_code)
                ->with('success', 'Successfully joined the campaign!');
        } catch (\Exception $e) {
            return redirect()->route('campaigns.index')
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Join a campaign
     */
    public function join(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'character_id' => 'nullable|exists:characters,id',
        ]);

        $user = Auth::user();
        $character = null;

        if ($validated['character_id'] ?? null) {
            $character = Character::where('id', $validated['character_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        try {
            $this->join_campaign_action->execute($campaign, $user, $character);

            return redirect()
                ->route('campaigns.show', ['campaign' => $campaign])
                ->with('success', 'Successfully joined the campaign!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a campaign member's character
     */
    public function updateCharacter(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:campaign_members,id',
            'character_id' => 'required|exists:characters,id',
        ]);

        $user = Auth::user();
        $member = $campaign->members()->where('id', $validated['member_id'])->first();

        // Ensure the member belongs to the current user
        if (! $member || $member->user_id !== $user->id) {
            return back()->withErrors(['error' => 'You can only update your own character.']);
        }

        // Ensure the character belongs to the current user
        $character = $user->characters()->where('id', $validated['character_id'])->first();
        if (! $character) {
            return back()->withErrors(['error' => 'Character not found or does not belong to you.']);
        }

        // Update the member's character
        $member->update(['character_id' => $character->id]);

        return redirect()
            ->route('campaigns.show', ['campaign' => $campaign])
            ->with('success', 'Character updated successfully!');
    }

    /**
     * Leave a campaign
     */
    public function leave(Campaign $campaign)
    {
        try {
            $this->leave_campaign_action->execute($campaign, Auth::user());

            return redirect()
                ->route('campaigns.index')
                ->with('success', 'Successfully left the campaign.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
