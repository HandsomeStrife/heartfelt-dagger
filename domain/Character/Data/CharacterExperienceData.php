<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterExperienceData extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public string $name,
        public string $description,
        public int $modifier,
        public string $category,
        public bool $is_clank_bonus = false,
    ) {}
}
