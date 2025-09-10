<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Actions\CreateSessionMarkerForAllParticipants;
use Domain\Room\Models\Room;
use Domain\Room\Repositories\SessionMarkerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SessionMarkerController extends Controller
{
    public function __construct(
        private CreateSessionMarkerForAllParticipants $createSessionMarkerForAllParticipants,
        private SessionMarkerRepository $sessionMarkerRepository
    ) {}

    /**
     * Create a session marker for all participants in a room
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|integer|exists:rooms,id',
            'identifier' => 'nullable|string|max:255',
            'video_time' => 'nullable|integer|min:0',
            'stt_time' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            // Verify the user has access to this room (is a participant)
            $room = Room::with('participants')->findOrFail($validated['room_id']);

            $isParticipant = $room->participants()
                ->where('user_id', $user->id)
                ->exists();

            if (! $isParticipant && $room->creator_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied - you are not a participant in this room',
                ], 403);
            }

            // Create session markers for all participants
            $markers = $this->createSessionMarkerForAllParticipants->execute(
                identifier: $validated['identifier'] ?? null,
                creatorId: $user->id,
                roomId: $validated['room_id'],
                videoTime: $validated['video_time'] ?? null,
                sttTime: $validated['stt_time'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Session marker created successfully',
                'data' => [
                    'uuid' => $markers->first()->uuid,
                    'identifier' => $validated['identifier'] ?? null,
                    'markers_created' => $markers->count(),
                    'video_time' => $validated['video_time'] ?? null,
                    'stt_time' => $validated['stt_time'] ?? null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create session marker',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get session markers for a room
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|integer|exists:rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            // Verify the user has access to this room
            $room = Room::with('participants')->findOrFail($validated['room_id']);

            $isParticipant = $room->participants()
                ->where('user_id', $user->id)
                ->exists();

            if (! $isParticipant && $room->creator_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied - you are not a participant in this room',
                ], 403);
            }

            // Get session markers for the user in this room
            $markers = $this->sessionMarkerRepository->getForUserInRoom(
                userId: $user->id,
                roomId: (int) $validated['room_id']
            );

            return response()->json([
                'success' => true,
                'data' => $markers->toArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session markers',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
