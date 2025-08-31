<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Domain\Room\Enums\RoomStatus;
use Domain\User\Data\UserData;
use Illuminate\Support\Collection;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class RoomData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public string $name,
        public string $description,
        public ?string $password,
        public int $guest_count,
        public int $creator_id,
        public ?int $campaign_id,
        public string $invite_code,
        public string $viewer_code,
        public RoomStatus $status,
        public ?string $created_at,
        public ?string $updated_at,
        public ?UserData $creator = null,
        public ?Collection $participants = null,
        public ?int $active_participant_count = null,
    ) {}
}
