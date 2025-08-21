<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Campaign\Actions\CreateCampaignAction;
use Domain\Campaign\Actions\JoinCampaignAction;
use Domain\Campaign\Actions\LeaveCampaignAction;
use Domain\Campaign\Data\CreateCampaignData;
use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Repositories\CampaignRepository;
use Domain\Character\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function __construct(
        private CampaignRepository $campaign_repository,
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
        return view('campaigns.create');
    }

    /**
     * Store a new campaign
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
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
        $user_is_member = $campaign->hasMember(Auth::user());
        $user_is_creator = $campaign->isCreator(Auth::user());

        return view('campaigns.show', [
            'campaign' => $campaign_data,
            'members' => $members,
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
