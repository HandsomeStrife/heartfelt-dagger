<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Repositories;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Data\CampaignHandoutData;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class CampaignHandoutRepository
{
    /**
     * Get all handouts for a campaign
     */
    public function getForCampaign(Campaign $campaign, ?User $user = null): Collection
    {
        $query = CampaignHandout::where('campaign_id', $campaign->id)
            ->with(['creator', 'authorizedUsers'])
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc');

        // Filter by access permissions if not the campaign creator
        if ($user && $user->id !== $campaign->creator_id) {
            $query->where(function ($q) use ($user) {
                $q->where('access_level', HandoutAccessLevel::ALL_PLAYERS)
                  ->orWhere(function ($subQ) use ($user) {
                      $subQ->where('access_level', HandoutAccessLevel::SPECIFIC_PLAYERS)
                           ->whereHas('authorizedUsers', function ($authQ) use ($user) {
                               $authQ->where('user_id', $user->id);
                           });
                  });
            });
        }

        return $query->get()->map(fn(CampaignHandout $handout) => CampaignHandoutData::fromModel($handout));
    }

    /**
     * Get handouts visible in sidebar for a campaign
     */
    public function getVisibleInSidebar(Campaign $campaign, ?User $user = null): Collection
    {
        $query = CampaignHandout::where('campaign_id', $campaign->id)
            ->where('is_visible_in_sidebar', true)
            ->where('is_published', true)
            ->with(['creator', 'authorizedUsers'])
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc');

        // Filter by access permissions
        if ($user) {
            // Campaign creator sees all sidebar handouts
            if ($user->id !== $campaign->creator_id) {
                $query->where(function ($q) use ($user) {
                    $q->where('access_level', HandoutAccessLevel::ALL_PLAYERS)
                      ->orWhere(function ($subQ) use ($user) {
                          $subQ->where('access_level', HandoutAccessLevel::SPECIFIC_PLAYERS)
                               ->whereHas('authorizedUsers', function ($authQ) use ($user) {
                                   $authQ->where('user_id', $user->id);
                               });
                      });
                });
            }
        } else {
            // Anonymous users see no handouts
            $query->whereRaw('1 = 0');
        }

        return $query->get()->map(fn(CampaignHandout $handout) => CampaignHandoutData::fromModel($handout));
    }

    /**
     * Find a handout by ID
     */
    public function findById(int $id): ?CampaignHandoutData
    {
        $handout = CampaignHandout::with(['campaign', 'creator', 'authorizedUsers'])->find($id);
        
        return $handout ? CampaignHandoutData::fromModel($handout) : null;
    }

    /**
     * Get handouts created by a specific user
     */
    public function getByCreator(User $user): Collection
    {
        return CampaignHandout::where('creator_id', $user->id)
            ->with(['campaign', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn(CampaignHandout $handout) => CampaignHandoutData::fromModel($handout));
    }

    /**
     * Search handouts within a campaign
     */
    public function searchInCampaign(Campaign $campaign, string $searchTerm, ?User $user = null): Collection
    {
        $query = CampaignHandout::where('campaign_id', $campaign->id)
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('original_file_name', 'LIKE', "%{$searchTerm}%");
            })
            ->with(['creator', 'authorizedUsers'])
            ->orderBy('created_at', 'desc');

        // Filter by access permissions if not the campaign creator
        if ($user && $user->id !== $campaign->creator_id) {
            $query->where(function ($q) use ($user) {
                $q->where('access_level', HandoutAccessLevel::ALL_PLAYERS)
                  ->orWhere(function ($subQ) use ($user) {
                      $subQ->where('access_level', HandoutAccessLevel::SPECIFIC_PLAYERS)
                           ->whereHas('authorizedUsers', function ($authQ) use ($user) {
                               $authQ->where('user_id', $user->id);
                           });
                  });
            });
        }

        return $query->get()->map(fn(CampaignHandout $handout) => CampaignHandoutData::fromModel($handout));
    }

    /**
     * Get handout statistics for a campaign
     */
    public function getCampaignStats(Campaign $campaign): array
    {
        $total = CampaignHandout::where('campaign_id', $campaign->id)->count();
        $published = CampaignHandout::where('campaign_id', $campaign->id)
            ->where('is_published', true)->count();
        $sidebarVisible = CampaignHandout::where('campaign_id', $campaign->id)
            ->where('is_visible_in_sidebar', true)->count();

        return [
            'total' => $total,
            'published' => $published,
            'sidebar_visible' => $sidebarVisible,
            'draft' => $total - $published,
        ];
    }

    /**
     * Reorder handouts
     */
    public function reorder(array $handoutIds): void
    {
        foreach ($handoutIds as $index => $handoutId) {
            CampaignHandout::where('id', $handoutId)->update(['display_order' => $index]);
        }
    }
}
