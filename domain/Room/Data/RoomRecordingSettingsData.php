<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;
use Livewire\Wireable;

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
        public string $stt_consent_requirement = 'optional',
        public string $recording_consent_requirement = 'optional',
        public ?string $created_at,
        public ?string $updated_at,
    ) {}
}
