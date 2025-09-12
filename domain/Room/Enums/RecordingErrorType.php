<?php

declare(strict_types=1);

namespace Domain\Room\Enums;

enum RecordingErrorType: string
{
    case FinalizationFailed = 'finalization_failed';
    case UploadFailed = 'upload_failed';
    case ValidationFailed = 'validation_failed';
    case ConsentError = 'consent_error';
    case MultipartFailed = 'multipart_failed';
    case ProviderError = 'provider_error';

    /**
     * Get a human-readable description of the error type
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::FinalizationFailed => 'Recording finalization failed',
            self::UploadFailed => 'Upload to storage provider failed',
            self::ValidationFailed => 'Recording validation failed',
            self::ConsentError => 'Recording consent error',
            self::MultipartFailed => 'Multipart upload failed',
            self::ProviderError => 'Storage provider error',
        };
    }

    /**
     * Get all available error types as an array
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
