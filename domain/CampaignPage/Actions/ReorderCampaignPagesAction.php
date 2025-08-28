<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Actions;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Models\CampaignPage;
use Illuminate\Support\Facades\DB;

class ReorderCampaignPagesAction
{
    /**
     * Reorder pages within a campaign and optional parent
     * 
     * @param Campaign $campaign
     * @param array $pageIds Array of page IDs in desired order
     * @param int|null $parentId Parent page ID or null for root level
     */
    public function execute(Campaign $campaign, array $pageIds, ?int $parentId = null): void
    {
        DB::transaction(function () use ($campaign, $pageIds, $parentId) {
            foreach ($pageIds as $index => $pageId) {
                CampaignPage::where('id', $pageId)
                    ->where('campaign_id', $campaign->id)
                    ->where('parent_id', $parentId)
                    ->update(['display_order' => $index + 1]);
            }
        });
    }
}
