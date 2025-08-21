<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\User\Actions\AuthenticateUserAction;
use Domain\User\Data\LoginUserData;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private AuthenticateUserAction $authenticate_user_action
    ) {}

    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'character_keys' => 'sometimes|array',
            'character_keys.*' => 'string',
        ]);

        $login_data = LoginUserData::from([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'remember' => $request->boolean('remember'),
        ]);

        $character_keys = $validated['character_keys'] ?? [];

        $user_data = $this->authenticate_user_action->execute($login_data, $character_keys);

        if (! $user_data) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        return redirect()->intended('/dashboard');
    }
}
