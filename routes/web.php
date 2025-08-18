<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoRoomController;


Route::get('/', function () {
    return view('index');
});

Route::get('/video-rooms', [VideoRoomController::class, 'index'])->name('video-rooms');


