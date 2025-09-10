<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\User\Actions\ResetPasswordAction;
use Domain\User\Data\ResetPasswordData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    public function __construct(
        private ResetPasswordAction $reset_password_action
    ) {}

    public function index(Request $request, string $token)
    {
        $email = $request->query('email');

        if (! $email) {
            return redirect('/login')->withErrors(['email' => 'Invalid reset link.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset_data = ResetPasswordData::from([
            'token' => $validated['token'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'password_confirmation' => $request->input('password_confirmation'),
        ]);

        $user_data = $this->reset_password_action->execute($reset_data);

        if (! $user_data) {
            return back()->withErrors(['token' => 'This password reset token is invalid or has expired.']);
        }

        // Log the user in automatically after password reset
        $user = Auth::getProvider()->retrieveById($user_data->id);
        Auth::login($user);
        session()->regenerate();

        return redirect('/dashboard')->with('status', 'Your password has been reset successfully!');
    }
}
