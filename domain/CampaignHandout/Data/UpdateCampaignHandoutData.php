<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Data;

use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Spatie\LaravelData\Data;

class UpdateCampaignHandoutData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public HandoutAccessLevel $access_level,
        public bool $is_visible_in_sidebar,
        public bool $is_published,
        public array $authorized_user_ids = [],
    ) {}
}
