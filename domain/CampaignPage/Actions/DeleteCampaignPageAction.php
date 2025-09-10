<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Actions;

use Domain\CampaignPage\Models\CampaignPage;
use Illuminate\Support\Facades\DB;

class DeleteCampaignPageAction
{
    public function execute(CampaignPage $page): void
    {
        DB::transaction(function () use ($page) {
            // Move children to parent or root level
            if ($page->children()->exists()) {
                $page->children()->update([
                    'parent_id' => $page->parent_id,
                ]);
            }

            // Clean up access records (handled by cascade delete, but explicit is better)
            $page->authorizedUsers()->detach();

            $page->delete();
        });
    }
}
