<?php

declare(strict_types=1);

namespace Domain\User\Data;

use Livewire\Wireable;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class RegisterUserData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Required]
        #[Max(255)]
        #[Unique('users', 'username')]
        public string $username,

        #[Required]
        #[Email]
        #[Max(255)]
        #[Unique('users', 'email')]
        public string $email,

        #[Required]
        #[Min(6)]
        #[Confirmed]
        public string $password,

        #[Required]
        public string $password_confirmation,
    ) {}
}
