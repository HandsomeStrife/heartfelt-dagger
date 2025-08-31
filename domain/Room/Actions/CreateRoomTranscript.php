<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Data\RoomTranscriptData;
use Domain\Room\Models\RoomTranscript;

class CreateRoomTranscript
{
    public function execute(
        int $room_id,
        ?int $user_id,
        int $started_at_ms,
        int $ended_at_ms,
        string $text,
        string $language = 'en-US',
        ?float $confidence = null
    ): RoomTranscriptData {
        $transcript = RoomTranscript::create([
            'room_id' => $room_id,
            'user_id' => $user_id,
            'started_at_ms' => $started_at_ms,
            'ended_at_ms' => $ended_at_ms,
            'text' => trim($text),
            'language' => $language,
            'confidence' => $confidence,
        ]);

        return RoomTranscriptData::from($transcript);
    }
}
