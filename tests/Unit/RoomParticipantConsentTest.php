<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

describe('RoomParticipant STT Consent', function () {
    test('hasSttConsent returns false when consent is null', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => null,
        ]);

        expect($participant->hasSttConsent())->toBeFalse();
        expect($participant->hasNoSttConsentDecision())->toBeTrue();
        expect($participant->hasDeniedSttConsent())->toBeFalse();
    });

    test('hasSttConsent returns true when consent is granted', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        expect($participant->hasSttConsent())->toBeTrue();
        expect($participant->hasNoSttConsentDecision())->toBeFalse();
        expect($participant->hasDeniedSttConsent())->toBeFalse();
    });

    test('hasSttConsent returns false when consent is denied', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => false,
            'stt_consent_at' => now(),
        ]);

        expect($participant->hasSttConsent())->toBeFalse();
        expect($participant->hasNoSttConsentDecision())->toBeFalse();
        expect($participant->hasDeniedSttConsent())->toBeTrue();
    });

    test('grantSttConsent updates consent fields correctly', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => null,
            'stt_consent_at' => null,
        ]);

        $participant->grantSttConsent();

        expect($participant->stt_consent_given)->toBeTrue();
        expect($participant->stt_consent_at)->not()->toBeNull();
        expect($participant->hasSttConsent())->toBeTrue();
    });

    test('denySttConsent updates consent fields correctly', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => null,
            'stt_consent_at' => null,
        ]);

        $participant->denySttConsent();

        expect($participant->stt_consent_given)->toBeFalse();
        expect($participant->stt_consent_at)->not()->toBeNull();
        expect($participant->hasDeniedSttConsent())->toBeTrue();
    });

    test('resetSttConsent clears consent fields correctly', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        $participant->resetSttConsent();

        expect($participant->stt_consent_given)->toBeNull();
        expect($participant->stt_consent_at)->toBeNull();
        expect($participant->hasNoSttConsentDecision())->toBeTrue();
    });
});

describe('RoomParticipant Recording Consent', function () {
    test('hasRecordingConsent returns false when consent is null', function () {
        $participant = RoomParticipant::factory()->create([
            'recording_consent_given' => null,
        ]);

        expect($participant->hasRecordingConsent())->toBeFalse();
        expect($participant->hasNoRecordingConsentDecision())->toBeTrue();
        expect($participant->hasRecordingConsentDenied())->toBeFalse();
    });

    test('hasRecordingConsent returns true when consent is granted', function () {
        $participant = RoomParticipant::factory()->create([
            'recording_consent_given' => true,
            'recording_consent_at' => now(),
        ]);

        expect($participant->hasRecordingConsent())->toBeTrue();
        expect($participant->hasNoRecordingConsentDecision())->toBeFalse();
        expect($participant->hasRecordingConsentDenied())->toBeFalse();
    });

    test('hasRecordingConsent returns false when consent is denied', function () {
        $participant = RoomParticipant::factory()->create([
            'recording_consent_given' => false,
            'recording_consent_at' => now(),
        ]);

        expect($participant->hasRecordingConsent())->toBeFalse();
        expect($participant->hasNoRecordingConsentDecision())->toBeFalse();
        expect($participant->hasRecordingConsentDenied())->toBeTrue();
    });

    test('giveRecordingConsent updates consent fields correctly', function () {
        $participant = RoomParticipant::factory()->create([
            'recording_consent_given' => null,
            'recording_consent_at' => null,
        ]);

        $participant->giveRecordingConsent();

        expect($participant->recording_consent_given)->toBeTrue();
        expect($participant->recording_consent_at)->not()->toBeNull();
        expect($participant->hasRecordingConsent())->toBeTrue();
    });

    test('denyRecordingConsent updates consent fields correctly', function () {
        $participant = RoomParticipant::factory()->create([
            'recording_consent_given' => null,
            'recording_consent_at' => null,
        ]);

        $participant->denyRecordingConsent();

        expect($participant->recording_consent_given)->toBeFalse();
        expect($participant->recording_consent_at)->not()->toBeNull();
        expect($participant->hasRecordingConsentDenied())->toBeTrue();
    });

    test('resetRecordingConsent clears consent fields correctly', function () {
        $participant = RoomParticipant::factory()->create([
            'recording_consent_given' => true,
            'recording_consent_at' => now(),
        ]);

        $participant->resetRecordingConsent();

        expect($participant->recording_consent_given)->toBeNull();
        expect($participant->recording_consent_at)->toBeNull();
        expect($participant->hasNoRecordingConsentDecision())->toBeTrue();
    });
});

describe('RoomParticipant Independent Consent Fields', function () {
    test('STT and recording consent are independent', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
            'recording_consent_given' => false,
            'recording_consent_at' => now(),
        ]);

        // STT consent granted, recording consent denied
        expect($participant->hasSttConsent())->toBeTrue();
        expect($participant->hasRecordingConsent())->toBeFalse();
        expect($participant->hasRecordingConsentDenied())->toBeTrue();
    });

    test('can grant recording consent independently of STT consent', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => false,  // STT denied
            'stt_consent_at' => now(),
            'recording_consent_given' => null, // No recording decision
            'recording_consent_at' => null,
        ]);

        // Grant recording consent
        $participant->giveRecordingConsent();

        // Recording consent should be granted, STT still denied
        expect($participant->hasRecordingConsent())->toBeTrue();
        expect($participant->hasSttConsent())->toBeFalse();
        expect($participant->hasDeniedSttConsent())->toBeTrue();
    });

    test('can grant STT consent independently of recording consent', function () {
        $participant = RoomParticipant::factory()->create([
            'stt_consent_given' => null,  // No STT decision
            'stt_consent_at' => null,
            'recording_consent_given' => false, // Recording denied
            'recording_consent_at' => now(),
        ]);

        // Grant STT consent
        $participant->grantSttConsent();

        // STT consent should be granted, recording still denied
        expect($participant->hasSttConsent())->toBeTrue();
        expect($participant->hasRecordingConsent())->toBeFalse();
        expect($participant->hasRecordingConsentDenied())->toBeTrue();
    });
});

