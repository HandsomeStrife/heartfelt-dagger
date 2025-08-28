<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Data;

use Domain\CampaignPage\Enums\PageAccessLevel;
use Livewire\Wireable;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CreateCampaignPageData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Required]
        public int $campaign_id,
        public ?int $parent_id,
        #[Required, Max(200)]
        public string $title,
        public ?string $content,
        public array $category_tags = [],
        public PageAccessLevel $access_level = PageAccessLevel::GM_ONLY,
        public int $display_order = 0,
        public bool $is_published = true,
        public array $authorized_user_ids = [],
    ) {}
}
