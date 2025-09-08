<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Actions\CreateWasabiRecording;
use Domain\Room\Actions\GenerateGoogleDriveDownloadUrl;
use Domain\Room\Actions\GenerateWasabiDownloadUrl;
use Domain\Room\Actions\GenerateWasabiPresignedUrl;
use Domain\Room\Actions\GenerateGoogleDriveUploadUrl;
use Domain\Room\Actions\ConfirmGoogleDriveUpload;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RoomRecordingController extends Controller
{
    public function __construct(
        private readonly GenerateWasabiPresignedUrl $generateWasabiPresignedUrl,
        private readonly GenerateWasabiDownloadUrl $generateWasabiDownloadUrl,
        private readonly CreateWasabiRecording $createWasabiRecording,
        private readonly GenerateGoogleDriveUploadUrl $generateGoogleDriveUploadUrl,
        private readonly ConfirmGoogleDriveUpload $confirmGoogleDriveUpload,
        private readonly GenerateGoogleDriveDownloadUrl $generateGoogleDriveDownloadUrl
    ) {}

    /**
     * Generate a presigned URL for Wasabi upload
     */
    public function presignWasabi(Request $request, Room $room): JsonResponse
    {
        try {
            // Log the incoming request data for debugging
            \Log::info('Wasabi presign request received', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'filename' => 'required|string|max:255',
                'content_type' => 'required|string|starts_with:video/webm,video/mp4,video/quicktime',
                'size' => 'required|integer|min:1|max:104857600', // 100MB max
                'metadata' => 'nullable|array',
            ]);

            // Check if user has access to this room
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can upload recordings'], 403);
            }

            // Validate recording consent for the user (only check STT consent if STT is enabled)
            $participant = $room->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (!$participant) {
                return response()->json([
                    'error' => 'User is not an active participant in this room',
                    'requires_consent' => false
                ], 403);
            }

            // Only require STT consent if STT is actually enabled for this room
            $room->load('recordingSettings');
            if ($room->recordingSettings && $room->recordingSettings->isSttEnabled() && !$participant->hasSttConsent()) {
                return response()->json([
                    'error' => 'Speech-to-text consent required for recording',
                    'requires_consent' => true
                ], 403);
            }

            // Generate presigned URL
            $result = $this->generateWasabiPresignedUrl->execute(
                $room,
                $user,
                $validated['filename'],
                $validated['content_type'],
                $validated['size'],
                $validated['metadata'] ?? []
            );

            return response()->json($result, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to generate Wasabi presigned URL', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm successful Wasabi upload and create recording record
     */
    public function confirmWasabiUpload(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'provider_file_id' => 'required|string|max:500',
                'filename' => 'required|string|max:255',
                'size_bytes' => 'required|integer|min:1',
                'started_at_ms' => 'required|integer|min:0',
                'ended_at_ms' => 'required|integer|min:0|gt:started_at_ms',
                'mime_type' => 'nullable|string|max:100',
            ]);

            // Check if user has access to this room
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can confirm recordings'], 403);
            }

            // Validate recording consent for the user
            $participant = $room->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (!$participant || !$participant->hasRecordingConsent()) {
                return response()->json([
                    'error' => 'Video recording consent required',
                    'requires_consent' => true
                ], 403);
            }

            // Create recording record
            $recording = $this->createWasabiRecording->execute(
                $room,
                $user,
                $validated['provider_file_id'],
                $validated['filename'],
                $validated['size_bytes'],
                $validated['started_at_ms'],
                $validated['ended_at_ms'],
                $validated['mime_type'] ?? 'video/webm'
            );

            return response()->json([
                'success' => true,
                'message' => 'Recording confirmed and saved',
                'recording_id' => $recording->id
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to confirm Wasabi upload', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Generate a direct upload URL for Google Drive (recommended approach)
     */
    public function generateGoogleDriveUploadUrl(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filename' => 'required|string|max:255',
                'content_type' => 'required|string|starts_with:video/webm,video/mp4,video/quicktime',
                'size' => 'required|integer|min:1|max:2147483648', // 2GB max
                'metadata' => 'nullable|array',
            ]);

            // Check if user has access to this room
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can upload recordings'], 403);
            }

            // Validate recording consent for the user
            $participant = $room->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (!$participant || !$participant->hasRecordingConsent()) {
                return response()->json([
                    'error' => 'Video recording consent required',
                    'requires_consent' => true
                ], 403);
            }

            // Generate direct upload URL
            $result = $this->generateGoogleDriveUploadUrl->execute(
                $room,
                $user,
                $validated['filename'],
                $validated['content_type'],
                $validated['size'],
                $validated['metadata'] ?? []
            );

            return response()->json($result, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to generate Google Drive upload URL', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm successful Google Drive upload and create database record
     */
    public function confirmGoogleDriveUpload(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'session_uri' => 'required|string|url',
                'file_id' => 'nullable|string', // Optional file ID if upload is already complete
                'metadata' => 'nullable|array',
            ]);

            // Check if user has access to this room
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can confirm uploads'], 403);
            }

            // Validate recording consent for the user
            $participant = $room->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (!$participant || !$participant->hasRecordingConsent()) {
                return response()->json([
                    'error' => 'Video recording consent required',
                    'requires_consent' => true
                ], 403);
            }

            // Confirm upload and create database record
            $result = $this->confirmGoogleDriveUpload->execute(
                $room,
                $user,
                $validated['session_uri'],
                $validated['metadata'] ?? [],
                $validated['file_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Google Drive upload confirmed successfully',
                'recording_id' => $result['recording_id'],
                'provider_file_id' => $result['provider_file_id'],
                'web_view_link' => $result['web_view_link'],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to confirm Google Drive upload', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get recordings for a room
     */
    public function index(Request $request, Room $room): JsonResponse
    {
        try {
            // Check if user has access to this room and is an active participant
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant to view recordings
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can view recordings'], 403);
            }

            $validated = $request->validate([
                'start_ms' => 'nullable|integer|min:0',
                'end_ms' => 'nullable|integer|min:0|gt:start_ms',
                'user_id' => 'nullable|integer|exists:users,id',
                'status' => 'nullable|string|in:uploaded,processing,ready,failed',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $query = $room->recordings()->orderBy('started_at_ms');

            // Apply filters
            if (isset($validated['start_ms']) && isset($validated['end_ms'])) {
                $query->whereBetween('started_at_ms', [$validated['start_ms'], $validated['end_ms']]);
            }

            if (isset($validated['user_id'])) {
                $query->where('user_id', $validated['user_id']);
            }

            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            $limit = $validated['limit'] ?? 50;
            $recordings = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'recordings' => $recordings,
                'count' => $recordings->count()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to get room recordings', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get recordings'
            ], 500);
        }
    }

    /**
     * Download a specific recording
     */
    public function download(Request $request, Room $room, RoomRecording $recording): JsonResponse
    {
        try {
            // Check if user has access to this room and recording
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can download recordings'], 403);
            }

            // Ensure recording belongs to this room
            if ($recording->room_id !== $room->id) {
                return response()->json(['error' => 'Recording not found in this room'], 404);
            }

            // Handle different storage providers
            if ($recording->provider === 'wasabi') {
                // Generate Wasabi download URL
                $result = $this->generateWasabiDownloadUrl->execute($room, $recording, $user);
                return response()->json($result);
                
            } elseif ($recording->provider === 'google_drive') {
                // Generate Google Drive download URL
                $result = $this->generateGoogleDriveDownloadUrl->execute($room, $recording, $user);
                return response()->json($result);
                
            } elseif ($recording->provider === 'local') {
                // Local file download
                if (Storage::disk('local')->exists($recording->provider_file_id)) {
                    return response()->json([
                        'success' => true,
                        'download_url' => Storage::disk('local')->url($recording->provider_file_id),
                        'filename' => $recording->filename,
                        'size_bytes' => $recording->size_bytes,
                        'content_type' => $recording->mime_type,
                        'provider' => 'local',
                    ]);
                } else {
                    return response()->json(['error' => 'Recording file not found'], 404);
                }
            }

            return response()->json(['error' => 'Unsupported storage provider'], 501);

        } catch (\Exception $e) {
            \Log::error('Failed to download recording', [
                'room_id' => $room->id,
                'recording_id' => $recording->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to prepare download'
            ], 500);
        }
    }

    /**
     * Start a new recording session
     */
    public function startSession(Request $request, Room $room)
    {
        try {
            $validated = $request->validate([
                'filename' => 'required|string|max:255',
                'multipart_upload_id' => 'required|string|max:2000', // Increased for Google Drive session URIs
                'provider_file_id' => 'nullable|string|max:2000',    // Nullable for Google Drive (set after finalization)
                'started_at_ms' => 'required|integer|min:0',
                'mime_type' => 'required|string|max:100'
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            // Validate recording permissions (same as existing methods)
            $this->validateRecordingPermissions($room, $user);

            $startAction = new \Domain\Room\Actions\StartRecordingSession();
            $recording = $startAction->execute(
                $room,
                $user,
                $validated['filename'],
                $validated['multipart_upload_id'],
                $validated['provider_file_id'] ?? null,
                $validated['started_at_ms'],
                $validated['mime_type']
            );

            \Log::info('Recording session started', [
                'recording_id' => $recording->id,
                'room_id' => $room->id,
                'user_id' => $user->id,
                'multipart_upload_id' => $validated['multipart_upload_id']
            ]);

            return response()->json([
                'recording_id' => $recording->id,
                'status' => $recording->status->value,
                'message' => 'Recording session started successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to start recording session', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to start recording session'
            ], 500);
        }
    }

    /**
     * Update recording progress with a new uploaded part
     */
    public function updateProgress(Request $request, Room $room, RoomRecording $recording)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            // Verify the recording belongs to this room and user
            if ($recording->room_id !== $room->id || $recording->user_id !== $user->id) {
                return response()->json(['error' => 'Recording not found'], 404);
            }

            // Only update if recording is still active
            if (!$recording->isRecording()) {
                return response()->json(['error' => 'Recording is not active'], 400);
            }

            // Different validation rules based on provider
            if ($recording->provider === 'wasabi') {
                $validated = $request->validate([
                    'part_number' => 'required|integer|min:1',
                    'etag' => 'required|string|max:255',
                    'part_size_bytes' => 'required|integer|min:1',
                    'ended_at_ms' => 'required|integer|min:0'
                ]);

                $updateAction = new \Domain\Room\Actions\UpdateRecordingProgress();
                $updatedRecording = $updateAction->execute(
                    $recording,
                    $validated['part_number'],
                    $validated['etag'],
                    $validated['part_size_bytes'],
                    $validated['ended_at_ms']
                );

                \Log::info('Wasabi recording progress updated', [
                    'recording_id' => $recording->id,
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'part_number' => $validated['part_number'],
                    'total_size' => $updatedRecording->size_bytes
                ]);

                return response()->json([
                    'recording_id' => $updatedRecording->id,
                    'status' => $updatedRecording->status->value,
                    'total_size_bytes' => $updatedRecording->size_bytes,
                    'parts_count' => count($updatedRecording->uploaded_parts ?? []),
                    'message' => 'Wasabi recording progress updated successfully'
                ]);

            } elseif ($recording->provider === 'google_drive') {
                $validated = $request->validate([
                    'chunk_size_bytes' => 'required|integer|min:1',
                    'total_uploaded_bytes' => 'required|integer|min:1',
                    'ended_at_ms' => 'required|integer|min:0'
                ]);

                // For Google Drive, just update the total size and timestamp
                $recording->update([
                    'size_bytes' => $validated['total_uploaded_bytes'],
                    'ended_at_ms' => $validated['ended_at_ms'],
                ]);

                \Log::info('Google Drive recording progress updated', [
                    'recording_id' => $recording->id,
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'chunk_size' => $validated['chunk_size_bytes'],
                    'total_uploaded' => $validated['total_uploaded_bytes']
                ]);

                return response()->json([
                    'recording_id' => $recording->id,
                    'status' => $recording->status->value,
                    'total_size_bytes' => $recording->size_bytes,
                    'message' => 'Google Drive recording progress updated successfully'
                ]);

            } else {
                return response()->json([
                    'error' => 'Unsupported provider for progress updates: ' . $recording->provider
                ], 400);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to update recording progress', [
                'recording_id' => $recording->id,
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to update recording progress'
            ], 500);
        }
    }

    /**
     * Validate recording permissions for a user in a room
     */
    private function validateRecordingPermissions(Room $room, $user): void
    {
        // Check if recording is enabled
        $room->load('recordingSettings');
        if (!$room->recordingSettings || !$room->recordingSettings->isRecordingEnabled()) {
            throw new \Exception('Video recording is not enabled for this room');
        }

        // Check consent (only require STT consent if STT is enabled)
        $participant = $room->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            throw new \Exception('User is not an active participant in this room');
        }

        // Only require STT consent if STT is actually enabled for this room
        if ($room->recordingSettings && $room->recordingSettings->isSttEnabled() && !$participant->hasSttConsent()) {
            throw new \Exception('Speech-to-text consent required for recording');
        }
    }
}
