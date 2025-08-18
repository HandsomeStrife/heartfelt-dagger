<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoRoomController extends Controller
{
    public function index()
    {
        return view('video-rooms');
    }
}
