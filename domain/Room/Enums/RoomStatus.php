<?php

declare(strict_types=1);

namespace Domain\Room\Enums;

enum RoomStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
}
