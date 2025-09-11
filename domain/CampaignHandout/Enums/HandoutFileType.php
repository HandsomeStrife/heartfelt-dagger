<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Enums;

enum HandoutFileType: string
{
    case IMAGE = 'image';
    case PDF = 'pdf';
    case DOCUMENT = 'document';
    case AUDIO = 'audio';
    case VIDEO = 'video';
    case OTHER = 'other';

    public static function fromMimeType(string $mimeType): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => self::IMAGE,
            $mimeType === 'application/pdf' => self::PDF,
            str_starts_with($mimeType, 'audio/') => self::AUDIO,
            str_starts_with($mimeType, 'video/') => self::VIDEO,
            in_array($mimeType, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.oasis.opendocument.text',
                'text/plain',
                'text/rtf',
            ]) => self::DOCUMENT,
            default => self::OTHER,
        };
    }

    public function isPreviewable(): bool
    {
        return match ($this) {
            self::IMAGE => true,
            self::PDF => true,
            default => false,
        };
    }

    public function isDownloadable(): bool
    {
        return true; // All file types can be downloaded
    }

    public function icon(): string
    {
        return match ($this) {
            self::IMAGE => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
            self::PDF => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            self::DOCUMENT => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            self::AUDIO => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
            self::VIDEO => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            self::OTHER => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        };
    }

    public function acceptableExtensions(): array
    {
        return match ($this) {
            self::IMAGE => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'],
            self::PDF => ['pdf'],
            self::DOCUMENT => ['doc', 'docx', 'odt', 'txt', 'rtf'],
            self::AUDIO => ['mp3', 'wav', 'ogg', 'aac', 'flac'],
            self::VIDEO => ['mp4', 'webm', 'avi', 'mov', 'mkv'],
            self::OTHER => ['*'], // Allow all other extensions
        };
    }
}
