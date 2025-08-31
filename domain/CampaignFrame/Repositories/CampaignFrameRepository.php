<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Repositories;

use Domain\CampaignFrame\Data\CampaignFrameData;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class CampaignFrameRepository
{
    /**
     * @return Collection<CampaignFrameData>
     */
    public function getPublicFrames(): Collection
    {
        $frames = CampaignFrame::with('creator')
            ->public()
            ->orderBy('name')
            ->get();

        return CampaignFrameData::collectionFromModels($frames);
    }

    /**
     * @return Collection<CampaignFrameData>
     */
    public function getFramesByUser(User $user): Collection
    {
        $frames = CampaignFrame::with('creator')
            ->byUser($user)
            ->orderBy('updated_at', 'desc')
            ->get();

        return CampaignFrameData::collectionFromModels($frames);
    }

    public function findById(int $id): ?CampaignFrameData
    {
        $frame = CampaignFrame::with('creator')->find($id);
        
        return $frame ? CampaignFrameData::fromModel($frame) : null;
    }

    public function findByIdForUser(int $id, User $user): ?CampaignFrameData
    {
        $frame = CampaignFrame::with('creator')
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('is_public', true)
                    ->orWhere('creator_id', $user->id);
            })
            ->first();

        return $frame ? CampaignFrameData::fromModel($frame) : null;
    }

    /**
     * @return Collection<CampaignFrameData>
     */
    public function searchPublicFrames(string $search): Collection
    {
        $frames = CampaignFrame::with('creator')
            ->public()
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        return CampaignFrameData::collectionFromModels($frames);
    }

    /**
     * @return Collection<CampaignFrameData>
     */
    public function getFramesAvailableForCampaign(?User $user = null): Collection
    {
        $query = CampaignFrame::with('creator')->public();

        if ($user) {
            $query->orWhere('creator_id', $user->id);
        }

        $frames = $query->orderBy('name')->get();

        return CampaignFrameData::collectionFromModels($frames);
    }
}
