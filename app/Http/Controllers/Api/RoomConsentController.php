<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Actions\UpdateRecordingConsent;
use Domain\Room\Actions\UpdateSttConsent;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoomConsentController extends Controller
{
    public function __construct(
        private readonly UpdateSttConsent $updateSttConsent,
        private readonly UpdateRecordingConsent $updateRecordingConsent
    ) {}

    /**
     * Update STT consent for a user in a room
     */
    public function updateSttConsent(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'consent_given' => 'required|boolean',
            ]);

            // Check if user has access to this room
            $user = $request->user();
            if (! $room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Check if STT is enabled for this room
            $room->load('recordingSettings');
            if (! $room->recordingSettings || ! $room->recordingSettings->isSttEnabled()) {
                return response()->json(['error' => 'Speech-to-text is not enabled for this room'], 403);
            }

            // Update consent - handle room creators who might not have participant records
            try {
                $participant = $this->updateSttConsent->execute(
                    $room,
                    $user,
                    $validated['consent_given']
                );
            } catch (\Exception $e) {
                // If participant not found but user is room creator, create one
                if ($room->isCreator($user) && str_contains($e->getMessage(), 'Participant not found')) {
                    // Create a participant record for the room creator
                    $participant = \Domain\Room\Models\RoomParticipant::create([
                        'room_id' => $room->id,
                        'user_id' => $user->id,
                        'character_name' => $user->username ?? 'Room Creator',
                        'character_class' => 'Host',
                        'joined_at' => now(),
                    ]);

                    // Now update consent
                    if ($validated['consent_given']) {
                        $participant->grantSttConsent();
                    } else {
                        $participant->denySttConsent();
                    }
                } else {
                    throw $e;
                }
            }

            // If consent was denied, they should be redirected out
            $shouldRedirect = ! $validated['consent_given'];

            return response()->json([
                'success' => true,
                'consent_given' => $participant->hasSttConsent(),
                'should_redirect' => $shouldRedirect,
                'message' => $validated['consent_given']
                    ? 'Speech-to-text consent granted'
                    : 'Speech-to-text consent denied',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to update STT consent', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update consent',
            ], 500);
        }
    }

    /**
     * Get the current STT consent status for a user in a room
     */
    public function getSttConsentStatus(Request $request, Room $room): JsonResponse
    {
        try {
            // Check if user has access to this room
            $user = $request->user();
            if (! $room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Check if STT is enabled for this room
            $room->load('recordingSettings');
            $sttEnabled = $room->recordingSettings && $room->recordingSettings->isSttEnabled();

            if (! $sttEnabled) {
                return response()->json([
                    'stt_enabled' => false,
                    'requires_consent' => false,
                    'consent_given' => null,
                ]);
            }

            // Find participant record or check if user is room creator
            $participant = $room->participants()
                ->where('user_id', $user?->id)
                ->whereNull('left_at')
                ->first();

            // If no participant record but user is room creator, they still need consent
            if (! $participant && $room->isCreator($user)) {
                // Room creators should always have consent requirements apply to them
                $consentRequired = $room->recordingSettings->isSttConsentRequired();

                return response()->json([
                    'stt_enabled' => true,
                    'consent_required' => $consentRequired,
                    'requires_consent' => true, // Always require consent decision for creators
                    'consent_given' => false,
                    'consent_denied' => false,
                ]);
            } elseif (! $participant) {
                return response()->json(['error' => 'User is not an active participant in this room'], 403);
            }

            // Check if consent is required based on room settings
            $consentRequired = $room->recordingSettings->isSttConsentRequired();
            $requiresConsentDialog = $participant->hasNoSttConsentDecision();

            return response()->json([
                'stt_enabled' => true,
                'consent_required' => $consentRequired,
                'requires_consent' => $requiresConsentDialog,
                'consent_given' => $participant->hasSttConsent(),
                'consent_denied' => $participant->hasDeniedSttConsent(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get STT consent status', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get consent status',
            ], 500);
        }
    }

    /**
     * Update recording consent for a user in a room
     */
    public function updateRecordingConsent(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'consent_given' => 'required|boolean',
            ]);

            // Check if user has access to this room
            $user = $request->user();
            if (! $room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Check if recording is enabled for this room
            $room->load('recordingSettings');
            if (! $room->recordingSettings || ! $room->recordingSettings->isRecordingEnabled()) {
                return response()->json(['error' => 'Video recording is not enabled for this room'], 403);
            }

            // Update consent - handle room creators who might not have participant records
            try {
                $participant = $this->updateRecordingConsent->execute(
                    $room,
                    $user,
                    $validated['consent_given']
                );
            } catch (\Exception $e) {
                // If participant not found but user is room creator, create one
                if ($room->isCreator($user)) {
                    $participant = RoomParticipant::create([
                        'room_id' => $room->id,
                        'user_id' => $user->id,
                        'character_name' => $user->username,
                        'character_class' => null,
                        'joined_at' => now(),
                    ]);

                    // Now apply the consent decision
                    if ($validated['consent_given']) {
                        $participant->giveRecordingConsent();
                    } else {
                        $participant->denyRecordingConsent();
                    }
                } else {
                    throw $e;
                }
            }

            return response()->json([
                'success' => true,
                'consent_given' => $validated['consent_given'],
                'participant_id' => $participant->id,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to update recording consent', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update consent',
            ], 500);
        }
    }

    /**
     * Get the current recording consent status for a user in a room
     */
    public function getRecordingConsentStatus(Request $request, Room $room): JsonResponse
    {
        try {
            // Check if user has access to this room
            $user = $request->user();
            if (! $room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Check if recording is enabled for this room
            $room->load('recordingSettings');
            $recordingEnabled = $room->recordingSettings && $room->recordingSettings->isRecordingEnabled();

            if (! $recordingEnabled) {
                return response()->json([
                    'recording_enabled' => false,
                    'requires_consent' => false,
                    'consent_given' => null,
                ]);
            }

            // Find participant record or check if user is room creator
            $participant = $room->participants()
                ->where('user_id', $user?->id)
                ->whereNull('left_at')
                ->first();

            // If no participant record but user is room creator, they still need consent
            if (! $participant && $room->isCreator($user)) {
                // Room creators should always have consent requirements apply to them
                $consentRequired = $room->recordingSettings->isRecordingConsentRequired();

                return response()->json([
                    'recording_enabled' => true,
                    'consent_required' => $consentRequired,
                    'requires_consent' => true, // Always require consent decision for creators
                    'consent_given' => false,
                    'consent_denied' => false,
                ]);
            } elseif (! $participant) {
                return response()->json(['error' => 'User is not an active participant in this room'], 403);
            }

            // Check if consent is required based on room settings
            $consentRequired = $room->recordingSettings->isRecordingConsentRequired();
            $requiresConsentDialog = $participant->hasNoRecordingConsentDecision();

            return response()->json([
                'recording_enabled' => true,
                'consent_required' => $consentRequired,
                'requires_consent' => $requiresConsentDialog,
                'consent_given' => $participant->hasRecordingConsent(),
                'consent_denied' => $participant->hasRecordingConsentDenied(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get recording consent status', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get consent status',
            ], 500);
        }
    }
}
