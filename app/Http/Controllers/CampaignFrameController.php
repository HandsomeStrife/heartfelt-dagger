<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\CampaignFrame\Actions\CreateCampaignFrameAction;
use Domain\CampaignFrame\Actions\DeleteCampaignFrameAction;
use Domain\CampaignFrame\Actions\UpdateCampaignFrameAction;
use Domain\CampaignFrame\Data\CreateCampaignFrameData;
use Domain\CampaignFrame\Data\UpdateCampaignFrameData;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\CampaignFrame\Repositories\CampaignFrameRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignFrameController extends Controller
{
    public function __construct(
        private CampaignFrameRepository $campaign_frame_repository,
        private CreateCampaignFrameAction $create_campaign_frame_action,
        private UpdateCampaignFrameAction $update_campaign_frame_action,
        private DeleteCampaignFrameAction $delete_campaign_frame_action,
    ) {}

    /**
     * Display a listing of campaign frames
     */
    public function index()
    {
        $user = Auth::user();
        $user_frames = $this->campaign_frame_repository->getFramesByUser($user);
        $public_frames = $this->campaign_frame_repository->getPublicFrames();

        return view('campaign-frames.index', [
            'user_frames' => $user_frames,
            'public_frames' => $public_frames,
        ]);
    }

    /**
     * Show the create campaign frame form
     */
    public function create()
    {
        return view('campaign-frames.create');
    }

    /**
     * Store a new campaign frame
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'complexity_rating' => 'required|integer|min:1|max:4',
            'is_public' => 'boolean',
            'pitch' => 'array',
            'touchstones' => 'array',
            'tone' => 'array',
            'themes' => 'array',
            'player_principles' => 'array',
            'gm_principles' => 'array',
            'community_guidance' => 'array',
            'ancestry_guidance' => 'array',
            'class_guidance' => 'array',
            'background_overview' => 'string|max:2000',
            'setting_guidance' => 'array',
            'setting_distinctions' => 'array',
            'inciting_incident' => 'string|max:1000',
            'special_mechanics' => 'array',
            'campaign_mechanics' => 'array',
            'session_zero_questions' => 'array',
        ]);

        $create_data = CreateCampaignFrameData::from($validated);
        $frame = $this->create_campaign_frame_action->execute($create_data, Auth::user());

        return redirect()
            ->route('campaign-frames.show', ['campaign_frame' => $frame])
            ->with('success', 'Campaign frame created successfully!');
    }

    /**
     * Display the specified campaign frame
     */
    public function show(CampaignFrame $campaign_frame)
    {
        $user = Auth::user();
        
        // Check if user can view this frame
        if (!$campaign_frame->canBeViewedBy($user)) {
            abort(403, 'You do not have permission to view this campaign frame.');
        }

        $frame_data = $this->campaign_frame_repository->findById($campaign_frame->id);
        $can_edit = $campaign_frame->canBeEditedBy($user);
        $usage_count = $campaign_frame->campaigns()->count();

        return view('campaign-frames.show', [
            'frame' => $frame_data,
            'can_edit' => $can_edit,
            'usage_count' => $usage_count,
        ]);
    }

    /**
     * Show the edit form for a campaign frame
     */
    public function edit(CampaignFrame $campaign_frame)
    {
        $user = Auth::user();
        
        // Check if user can edit this frame
        if (!$campaign_frame->canBeEditedBy($user)) {
            abort(403, 'You do not have permission to edit this campaign frame.');
        }

        $frame_data = $this->campaign_frame_repository->findById($campaign_frame->id);

        return view('campaign-frames.edit', [
            'frame' => $frame_data,
        ]);
    }

    /**
     * Update the specified campaign frame
     */
    public function update(Request $request, CampaignFrame $campaign_frame)
    {
        $user = Auth::user();
        
        // Check if user can edit this frame
        if (!$campaign_frame->canBeEditedBy($user)) {
            abort(403, 'You do not have permission to edit this campaign frame.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'complexity_rating' => 'required|integer|min:1|max:4',
            'is_public' => 'boolean',
            'pitch' => 'array',
            'touchstones' => 'array',
            'tone' => 'array',
            'themes' => 'array',
            'player_principles' => 'array',
            'gm_principles' => 'array',
            'community_guidance' => 'array',
            'ancestry_guidance' => 'array',
            'class_guidance' => 'array',
            'background_overview' => 'string|max:2000',
            'setting_guidance' => 'array',
            'setting_distinctions' => 'array',
            'inciting_incident' => 'string|max:1000',
            'special_mechanics' => 'array',
            'campaign_mechanics' => 'array',
            'session_zero_questions' => 'array',
        ]);

        $update_data = UpdateCampaignFrameData::from($validated);
        $updated_frame = $this->update_campaign_frame_action->execute($campaign_frame, $update_data);

        return redirect()
            ->route('campaign-frames.show', ['campaign_frame' => $updated_frame])
            ->with('success', 'Campaign frame updated successfully!');
    }

    /**
     * Remove the specified campaign frame from storage
     */
    public function destroy(CampaignFrame $campaign_frame)
    {
        $user = Auth::user();
        
        // Check if user can delete this frame
        if (!$campaign_frame->canBeEditedBy($user)) {
            abort(403, 'You do not have permission to delete this campaign frame.');
        }

        try {
            $this->delete_campaign_frame_action->execute($campaign_frame);
            
            return redirect()
                ->route('campaign-frames.index')
                ->with('success', 'Campaign frame deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('campaign-frames.show', ['campaign_frame' => $campaign_frame])
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Browse public campaign frames
     */
    public function browse(Request $request)
    {
        $search = $request->get('search');
        
        if ($search) {
            $frames = $this->campaign_frame_repository->searchPublicFrames($search);
        } else {
            $frames = $this->campaign_frame_repository->getPublicFrames();
        }

        return view('campaign-frames.browse', [
            'frames' => $frames,
            'search' => $search,
        ]);
    }
}
