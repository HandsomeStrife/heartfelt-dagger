<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Domain\Character\Data\CharacterData;
use Domain\User\Data\UserData;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class RoomParticipantData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $room_id,
        public ?int $user_id,
        public ?int $character_id,
        public ?string $character_name,
        public ?string $character_class,
        public ?string $joined_at,
        public ?string $left_at,
        public ?string $created_at,
        public ?string $updated_at,
        public ?UserData $user = null,
        public ?CharacterData $character = null,
    ) {}
}
