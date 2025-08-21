<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\VideoRoomController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }

    return view('index');
});

// Authentication routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Discord redirect
Route::get('/discord', function () {
    return redirect('https://discord.gg/dNAkDYevGx');
})->name('discord');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/campaigns', function () {
        return view('campaigns');
    })->name('campaigns');
    Route::get('/video-rooms', [VideoRoomController::class, 'index'])->name('video-rooms');
});

// Character routes (public)
Route::get('/characters', [App\Http\Controllers\CharacterBuilderController::class, 'index'])->name('characters');
Route::get('/character-builder', [App\Http\Controllers\CharacterBuilderController::class, 'create'])->name('character-builder');
Route::get('/character-builder/{character_key}', [App\Http\Controllers\CharacterBuilderController::class, 'edit'])->name('character-builder.edit');
Route::get('/character/{character_key}', [App\Http\Controllers\CharacterBuilderController::class, 'show'])->name('character.show');

// API routes for character data
Route::get('/api/character/{character_key}', [App\Http\Controllers\CharacterBuilderController::class, 'apiShow'])->name('api.character.show');
Route::delete('/api/character/{character_key}', [App\Http\Controllers\CharacterBuilderController::class, 'apiDestroy'])->name('api.character.destroy');

// Simple test route for debugging Livewire
Route::get('/simple-test', function () {
    return view('simple-test-page');
});