<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterConnectionData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public string $character_name,
        public string $connection_type,
        public string $description,
    ) {}
}
