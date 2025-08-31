<?php

declare(strict_types=1);

namespace Domain\User\Data;

use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;
use Livewire\Wireable;

class UserStorageAccountData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $user_id,
        public string $provider,
        public array $encrypted_credentials,
        public string $display_name,
        public bool $is_active,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}
}
