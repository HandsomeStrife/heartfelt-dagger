<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Actions;

use Domain\CampaignPage\Data\CreateCampaignPageData;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCampaignPageAction
{
    public function execute(CreateCampaignPageData $data, User $creator): CampaignPage
    {
        return DB::transaction(function () use ($data, $creator) {
            // If no display order specified, put at end
            if ($data->display_order === 0) {
                $maxOrder = CampaignPage::where('campaign_id', $data->campaign_id)
                    ->where('parent_id', $data->parent_id)
                    ->max('display_order') ?? 0;
                $data->display_order = $maxOrder + 1;
            }

            $page = CampaignPage::create([
                'campaign_id' => $data->campaign_id,
                'parent_id' => $data->parent_id,
                'creator_id' => $creator->id,
                'title' => $data->title,
                'content' => $data->content,
                'category_tags' => $data->category_tags,
                'access_level' => $data->access_level,
                'display_order' => $data->display_order,
                'is_published' => $data->is_published,
            ]);

            // Handle specific player access
            if ($data->access_level === PageAccessLevel::SPECIFIC_PLAYERS && ! empty($data->authorized_user_ids)) {
                $page->authorizedUsers()->attach($data->authorized_user_ids);
            }

            return $page->fresh();
        });
    }
}
