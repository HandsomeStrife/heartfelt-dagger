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
    
    // Campaign routes
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [App\Http\Controllers\CampaignController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\CampaignController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CampaignController::class, 'store'])->name('store');
        Route::get('/{campaign}', [App\Http\Controllers\CampaignController::class, 'show'])->name('show');
        Route::post('/{campaign}/join', [App\Http\Controllers\CampaignController::class, 'join'])->name('join');
        Route::delete('/{campaign}/leave', [App\Http\Controllers\CampaignController::class, 'leave'])->name('leave');
    });
    
    // Campaign invite routes (separate from auth to allow invite sharing)
    Route::get('/join/{invite_code}', [App\Http\Controllers\CampaignController::class, 'showJoin'])->name('campaigns.invite');
    
    // Room routes
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [App\Http\Controllers\RoomController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\RoomController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\RoomController::class, 'store'])->name('store');
        Route::get('/{room}', [App\Http\Controllers\RoomController::class, 'show'])->name('show');
        Route::post('/{room}/join', [App\Http\Controllers\RoomController::class, 'join'])->name('join');
        Route::delete('/{room}/leave', [App\Http\Controllers\RoomController::class, 'leave'])->name('leave');
        Route::get('/{room}/session', [App\Http\Controllers\RoomController::class, 'session'])->name('session');
    });
    
    // Room invite routes (separate to allow invite sharing)
    Route::get('/rooms/join/{invite_code}', [App\Http\Controllers\RoomController::class, 'showJoin'])->name('rooms.invite');
    
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