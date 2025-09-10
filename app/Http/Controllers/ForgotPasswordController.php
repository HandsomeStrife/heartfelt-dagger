<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\User\Actions\SendPasswordResetAction;
use Domain\User\Data\PasswordResetRequestData;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private SendPasswordResetAction $send_password_reset_action
    ) {}

    public function index()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $request_data = PasswordResetRequestData::from([
            'email' => $validated['email'],
        ]);

        $this->send_password_reset_action->execute($request_data);

        return back()->with('status', 'We have emailed your password reset link!');
    }
}
