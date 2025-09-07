<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;
use Livewire\Wireable;

class RoomTranscriptData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $room_id,
        public ?int $user_id,
        public ?int $character_id,
        public ?string $character_name,
        public ?string $character_class,
        public int $started_at_ms,
        public int $ended_at_ms,
        public string $text,
        public string $language,
        public ?float $confidence,
        public string $provider,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}
}
