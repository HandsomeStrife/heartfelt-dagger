<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\User\Actions\RegisterUserAction;
use Domain\User\Data\RegisterUserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __construct(
        private RegisterUserAction $register_user_action
    ) {}

    public function index()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'character_keys' => 'sometimes|array',
            'character_keys.*' => 'string',
        ]);

        $register_data = RegisterUserData::from([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'password_confirmation' => $request->input('password_confirmation'),
        ]);

        $character_keys = $validated['character_keys'] ?? [];

        $user_data = $this->register_user_action->execute($register_data, $character_keys);

        // Log the user in after registration
        $user = Auth::getProvider()->retrieveById($user_data->id);
        Auth::login($user);

        return redirect('/dashboard');
    }
}
