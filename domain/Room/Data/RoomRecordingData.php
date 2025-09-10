<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class RoomRecordingData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $room_id,
        public ?int $user_id,
        public string $provider,
        public string $provider_file_id,
        public string $filename,
        public int $size_bytes,
        public int $started_at_ms,
        public int $ended_at_ms,
        public string $mime_type,
        public string $status,
        public ?string $stream_url,
        public ?string $thumbnail_url,
        public ?string $created_at,
        public ?string $updated_at,
        public ?array $room = null,
        public ?array $user = null,
        public ?string $format = null,
        public ?string $quality = null,
    ) {}
}
