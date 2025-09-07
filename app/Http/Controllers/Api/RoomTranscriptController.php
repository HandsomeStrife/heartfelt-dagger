<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Actions\CreateRoomTranscript;
use Domain\Room\Models\Room;
use Domain\Room\Repositories\RoomTranscriptRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RoomTranscriptController extends Controller
{
    public function __construct(
        private readonly CreateRoomTranscript $createRoomTranscript,
        private readonly RoomTranscriptRepository $transcriptRepository
    ) {}

    /**
     * Store a new transcript chunk for a room
     */
    public function store(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|integer|exists:users,id',
                'character_id' => 'nullable|integer|exists:characters,id',
                'character_name' => 'nullable|string|max:100',
                'character_class' => 'nullable|string|max:50',
                'started_at_ms' => 'required|integer|min:0',
                'ended_at_ms' => 'required|integer|min:0|gt:started_at_ms',
                'text' => 'required|string|max:5000',
                'language' => 'nullable|string|max:10',
                'confidence' => 'nullable|numeric|min:0|max:1',
                'provider' => 'nullable|string|max:20|in:browser,assemblyai',
            ]);

            // Additional validation
            if ($validated['ended_at_ms'] <= $validated['started_at_ms']) {
                throw ValidationException::withMessages([
                    'ended_at_ms' => 'End time must be after start time.'
                ]);
            }

            // Check if user has access to this room
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Check if STT is enabled for this room
            $room->load('recordingSettings');
            if (!$room->recordingSettings || !$room->recordingSettings->isSttEnabled()) {
                return response()->json(['error' => 'Speech-to-text is not enabled for this room'], 403);
            }

            // Validate STT consent for the user
            $userId = $validated['user_id'] ?? $user?->id;
            if ($userId) {
                $participant = $room->participants()
                    ->where('user_id', $userId)
                    ->whereNull('left_at')
                    ->first();

                if (!$participant) {
                    return response()->json(['error' => 'User is not an active participant in this room'], 403);
                }

                if (!$participant->hasSttConsent()) {
                    return response()->json([
                        'error' => 'Speech-to-text consent required',
                        'requires_consent' => true
                    ], 403);
                }
            }

            // Create transcript
            $transcriptData = (new CreateRoomTranscript())->execute(
                room_id: $room->id,
                user_id: $validated['user_id'] ?? $user?->id,
                character_id: $validated['character_id'] ?? null,
                character_name: $validated['character_name'] ?? null,
                character_class: $validated['character_class'] ?? null,
                started_at_ms: $validated['started_at_ms'],
                ended_at_ms: $validated['ended_at_ms'],
                text: $validated['text'],
                language: $validated['language'] ?? 'en-US',
                confidence: $validated['confidence'],
                provider: $validated['provider'] ?? 'browser'
            );

            return response()->json([
                'success' => true,
                'transcript' => $transcriptData,
                'message' => 'Transcript saved successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to save room transcript', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to save transcript'
            ], 500);
        }
    }

    /**
     * Get transcripts for a room
     */
    public function index(Request $request, Room $room): JsonResponse
    {
        try {
            // Check if user has access to this room and is an active participant
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant to view transcripts
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can view transcripts'], 403);
            }

            $validated = $request->validate([
                'start_ms' => 'nullable|integer|min:0',
                'end_ms' => 'nullable|integer|min:0|gt:start_ms',
                'search' => 'nullable|string|max:100',
                'user_id' => 'nullable|integer|exists:users,id',
                'min_confidence' => 'nullable|numeric|min:0|max:1',
                'limit' => 'nullable|integer|min:1|max:500',
            ]);

            // Get transcripts based on filters
            if (isset($validated['start_ms']) && isset($validated['end_ms'])) {
                $transcripts = $this->transcriptRepository->getByRoomInTimeRange(
                    $room, 
                    (int) $validated['start_ms'], 
                    (int) $validated['end_ms']
                );
            } elseif (isset($validated['search'])) {
                $transcripts = $this->transcriptRepository->searchInRoom($room, $validated['search']);
            } elseif (isset($validated['min_confidence'])) {
                $transcripts = $this->transcriptRepository->getHighConfidenceByRoom($room, (float) $validated['min_confidence']);
            } else {
                $limit = (int) ($validated['limit'] ?? 50);
                $transcripts = $this->transcriptRepository->getRecentByRoom($room, $limit);
            }

            // Filter by user if specified
            if (isset($validated['user_id'])) {
                $transcripts = $transcripts->where('user_id', $validated['user_id']);
            }

            return response()->json([
                'success' => true,
                'transcripts' => $transcripts->values(),
                'count' => $transcripts->count()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to get room transcripts', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get transcripts'
            ], 500);
        }
    }
}
