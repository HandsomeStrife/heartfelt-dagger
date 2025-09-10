<?php

declare(strict_types=1);

namespace Domain\Campaign\Repositories;

use Domain\Campaign\Data\CampaignData;
use Domain\Campaign\Data\CampaignMemberData;
use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class CampaignRepository
{
    public function findById(int $id): ?CampaignData
    {
        $campaign = Campaign::with(['creator', 'campaignFrame'])
            ->withCount('members')
            ->find($id);

        return $campaign ? CampaignData::from([
            ...$campaign->toArray(),
            'member_count' => $campaign->members_count,
        ]) : null;
    }

    public function findByInviteCode(string $invite_code): ?CampaignData
    {
        $campaign = Campaign::with(['creator', 'campaignFrame'])
            ->withCount('members')
            ->byInviteCode($invite_code)
            ->first();

        return $campaign ? CampaignData::from([
            ...$campaign->toArray(),
            'member_count' => $campaign->members_count,
        ]) : null;
    }

    /**
     * @return Collection<CampaignData>
     */
    public function getCreatedByUser(User $user): Collection
    {
        return Campaign::with(['creator', 'campaignFrame'])
            ->withCount('members')
            ->byCreator($user)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($campaign) => CampaignData::from([
                ...$campaign->toArray(),
                'member_count' => $campaign->members_count,
            ]));
    }

    /**
     * @return Collection<CampaignData>
     */
    public function getJoinedByUser(User $user): Collection
    {
        return Campaign::with(['creator', 'campaignFrame'])
            ->withCount('members')
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($campaign) => CampaignData::from([
                ...$campaign->toArray(),
                'member_count' => $campaign->members_count,
            ]));
    }

    /**
     * @return Collection<CampaignMemberData>
     */
    public function getCampaignMembers(Campaign $campaign): Collection
    {
        return $campaign->members()
            ->with(['user', 'character'])
            ->orderBy('joined_at', 'asc')
            ->get()
            ->map(fn ($member) => CampaignMemberData::from($member));
    }

    /**
     * @return Collection<CampaignData>
     */
    public function getActiveCampaigns(): Collection
    {
        return Campaign::with(['creator'])
            ->withCount('members')
            ->active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($campaign) => CampaignData::from([
                ...$campaign->toArray(),
                'member_count' => $campaign->members_count,
            ]));
    }

    /**
     * @return Collection<CampaignData>
     */
    public function getAllUserCampaigns(User $user): Collection
    {
        $created = $this->getCreatedByUser($user);
        $joined = $this->getJoinedByUser($user);

        return $created->merge($joined)->sortByDesc('created_at')->values();
    }

    /**
     * Get recent campaigns for dashboard
     * @return Collection<CampaignData>
     */
    public function getRecentByUser(User $user, int $limit = 3): Collection
    {
        // Get both created and joined campaigns, then limit the result
        $created = Campaign::with(['creator', 'campaignFrame'])
            ->withCount('members')
            ->byCreator($user)
            ->get()
            ->map(fn ($campaign) => CampaignData::from([
                ...$campaign->toArray(),
                'member_count' => $campaign->members_count,
            ]));

        $joined = Campaign::with(['creator', 'campaignFrame'])
            ->withCount('members')
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(fn ($campaign) => CampaignData::from([
                ...$campaign->toArray(),
                'member_count' => $campaign->members_count,
            ]));

        return $created->merge($joined)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }
}
