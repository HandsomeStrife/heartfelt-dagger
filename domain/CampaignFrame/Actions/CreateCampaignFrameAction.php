<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Actions;

use Domain\CampaignFrame\Data\CreateCampaignFrameData;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;

class CreateCampaignFrameAction
{
    public function execute(CreateCampaignFrameData $data, User $creator): CampaignFrame
    {
        return CampaignFrame::create([
            'name' => $data->name,
            'description' => $data->description,
            'complexity_rating' => $data->complexity_rating->value,
            'is_public' => $data->is_public,
            'creator_id' => $creator->id,
            'pitch' => $data->pitch,
            'tone_and_themes' => $data->tone_and_themes,
            'background_overview' => $data->background_overview,
            'setting_guidance' => $data->setting_guidance,
            'principles' => $data->principles,
            'setting_distinctions' => $data->setting_distinctions,
            'inciting_incident' => $data->inciting_incident,
            'special_mechanics' => $data->special_mechanics,
            'session_zero_questions' => $data->session_zero_questions,
        ]);
    }
}
