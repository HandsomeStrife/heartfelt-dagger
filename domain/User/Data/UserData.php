<?php

declare(strict_types=1);

namespace Domain\User\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class UserData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public ?string $email_verified_at = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}
}
