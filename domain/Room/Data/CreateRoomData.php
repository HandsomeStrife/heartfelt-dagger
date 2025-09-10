<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CreateRoomData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Required, Max(100)]
        public string $name,

        #[Required, Max(500)]
        public string $description,

        #[Max(255)]
        public ?string $password,

        #[Required, Min(2), Max(6)]
        public int $guest_count,

        public ?int $campaign_id = null,
    ) {}
}
