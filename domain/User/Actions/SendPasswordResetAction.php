<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\User\Data\PasswordResetRequestData;
use Domain\User\Notifications\PasswordResetNotification;
use Domain\User\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendPasswordResetAction
{
    public function __construct(
        private UserRepository $user_repository
    ) {}

    public function execute(PasswordResetRequestData $request_data): bool
    {
        $user_data = $this->user_repository->findByEmail($request_data->email);

        if (! $user_data) {
            // Always return true to prevent email enumeration attacks
            return true;
        }

        // Generate token
        $token = Str::random(64);

        // Delete existing tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request_data->email)
            ->delete();

        // Create new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request_data->email,
            'token' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        // Get the user model to send notification
        $user = \Domain\User\Models\User::where('email', $request_data->email)->first();

        if ($user) {
            $user->notify(new PasswordResetNotification($token));
        }

        return true;
    }
}
