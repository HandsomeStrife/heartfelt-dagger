<?php

declare(strict_types=1);

namespace Domain\User\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class PasswordResetRequestData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public string $email,
    ) {}
}
