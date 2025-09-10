<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Actions;

use Domain\CampaignPage\Data\UpdateCampaignPageData;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Illuminate\Support\Facades\DB;

class UpdateCampaignPageAction
{
    public function execute(CampaignPage $page, UpdateCampaignPageData $data): CampaignPage
    {
        return DB::transaction(function () use ($page, $data) {
            // Prevent circular references in hierarchy
            if ($data->parent_id && $this->wouldCreateCircularReference($page, $data->parent_id)) {
                throw new \InvalidArgumentException('Cannot set parent: would create circular reference');
            }

            $page->update([
                'parent_id' => $data->parent_id,
                'title' => $data->title,
                'content' => $data->content,
                'category_tags' => $data->category_tags,
                'access_level' => $data->access_level,
                'display_order' => $data->display_order,
                'is_published' => $data->is_published,
            ]);

            // Handle specific player access
            if ($data->access_level === PageAccessLevel::SPECIFIC_PLAYERS) {
                $page->authorizedUsers()->sync($data->authorized_user_ids);
            } else {
                // Clear specific access if not using specific players
                $page->authorizedUsers()->detach();
            }

            return $page->fresh();
        });
    }

    private function wouldCreateCircularReference(CampaignPage $page, int $proposedParentId): bool
    {
        // Can't be parent of itself
        if ($page->id === $proposedParentId) {
            return true;
        }

        // Check if proposed parent is a descendant
        $proposedParent = CampaignPage::find($proposedParentId);

        return $proposedParent && $proposedParent->isDescendantOf($page);
    }
}
