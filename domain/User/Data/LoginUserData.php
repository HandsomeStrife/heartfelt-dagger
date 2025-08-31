<?php

declare(strict_types=1);

namespace Domain\User\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class LoginUserData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Required]
        #[Email]
        public string $email,

        #[Required]
        #[Min(6)]
        public string $password,

        public bool $remember = false,
    ) {}
}
