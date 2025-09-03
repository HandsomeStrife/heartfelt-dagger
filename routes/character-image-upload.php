<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CharacterImageUploadController;

// Character image upload routes
Route::middleware(['web'])->group(function () {
    Route::post('/character-builder/{character_key}/upload-image', [CharacterImageUploadController::class, 'upload'])
        ->name('character.image.upload');
});
