<?php

declare(strict_types=1);

namespace Domain\Campaign\Data;

use Domain\Character\Data\CharacterData;
use Domain\User\Data\UserData;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CampaignMemberData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public int $id,
        public int $campaign_id,
        public int $user_id,
        public ?int $character_id,
        public string $joined_at,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?UserData $user = null,
        public ?CharacterData $character = null,
        public ?CampaignData $campaign = null,
    ) {}
}
