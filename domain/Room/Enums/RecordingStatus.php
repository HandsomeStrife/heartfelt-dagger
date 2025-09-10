<?php

declare(strict_types=1);

namespace Domain\Room\Enums;

enum RecordingStatus: string
{
    case Recording = 'recording';
    case Finalizing = 'finalizing';
    case Completed = 'completed';
    case Failed = 'failed';

    // Legacy values for backward compatibility
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case Ready = 'ready';

    public function label(): string
    {
        return match ($this) {
            self::Recording => 'Recording',
            self::Finalizing => 'Finalizing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Uploaded => 'Uploaded',
            self::Processing => 'Processing',
            self::Ready => 'Ready',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Recording;
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Ready, self::Uploaded]);
    }

    public function canBeFinalized(): bool
    {
        return $this === self::Recording;
    }
}
