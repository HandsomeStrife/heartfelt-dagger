<?php

namespace App\Http\Controllers;

use Domain\Character\Data\CharacterData;

class VideoRoomController extends Controller
{
    public function index()
    {
        return view('video-rooms', [
            'character' => new CharacterData(
                name: 'John Doe',
                class: 'Bard',
                subclass: 'Grace & Codex',
                health: 5,
                stress: 0,
                hope: 2,
            ),
        ]);
    }
}
