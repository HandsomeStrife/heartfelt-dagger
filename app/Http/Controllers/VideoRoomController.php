<?php

namespace App\Http\Controllers;

use Domain\Character\Data\CharacterData;
use Domain\Character\Models\Character;

class VideoRoomController extends Controller
{
    public function index()
    {
        // Create a test character for the video rooms demo
        $testCharacterModel = Character::factory()->create([
            'name' => 'John Doe',
            'class' => 'bard',
            'subclass' => 'troubadour',
            'ancestry' => 'human',
            'community' => 'highborne',
        ]);

        return view('video-rooms', [
            'character' => CharacterData::fromModel($testCharacterModel),
        ]);
    }
}
