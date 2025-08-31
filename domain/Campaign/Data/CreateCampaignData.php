<?php

declare(strict_types=1);

namespace Domain\Campaign\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CreateCampaignData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Required, Max(100)]
        public string $name,
        #[Max(1000)]
        public ?string $description = null,
        public ?int $campaign_frame_id = null,
    ) {}
}
