<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class RoomRecordingSettingsData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $room_id,
        public bool $recording_enabled,
        public bool $stt_enabled,
        public ?string $storage_provider,
        public ?int $storage_account_id,
        public ?string $stt_provider,
        public ?int $stt_account_id,
        public string $stt_consent_requirement,
        public string $recording_consent_requirement,
        public ?string $viewer_password,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}
}
