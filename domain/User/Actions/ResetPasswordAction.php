<?php

declare(strict_types=1);

namespace Domain\User\Actions;

use Domain\User\Data\ResetPasswordData;
use Domain\User\Data\UserData;
use Domain\User\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordAction
{
    public function __construct(
        private UserRepository $user_repository
    ) {}

    public function execute(ResetPasswordData $reset_data): ?UserData
    {
        // Verify token exists and is not expired (60 minutes)
        $token_record = DB::table('password_reset_tokens')
            ->where('email', $reset_data->email)
            ->where('token', hash('sha256', $reset_data->token))
            ->where('created_at', '>', now()->subMinutes(60))
            ->first();

        if (! $token_record) {
            return null;
        }

        // Get user data
        $user_data = $this->user_repository->findByEmail($reset_data->email);

        if (! $user_data) {
            return null;
        }

        // Update password
        $user = \Domain\User\Models\User::find($user_data->id);
        $user->password = Hash::make($reset_data->password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')
            ->where('email', $reset_data->email)
            ->delete();

        return UserData::from($user);
    }
}
