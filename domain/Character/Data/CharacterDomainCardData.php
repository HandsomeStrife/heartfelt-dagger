<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterDomainCardData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public string $ability_key,
        public string $domain,
        public int $level,
        public string $name,
        public string $type,
        public int $recall_cost,
        public array $descriptions,
    ) {}
}
