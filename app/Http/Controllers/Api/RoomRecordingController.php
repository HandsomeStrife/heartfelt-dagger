<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Actions\CreateWasabiRecording;
use Domain\Room\Actions\GenerateGoogleDriveDownloadUrl;
use Domain\Room\Actions\GenerateWasabiDownloadUrl;
use Domain\Room\Actions\GenerateWasabiPresignedUrl;
use Domain\Room\Actions\UploadToGoogleDrive;
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
        private readonly UploadToGoogleDrive $uploadToGoogleDrive,
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
            $validated = $request->validate([
                'filename' => 'required|string|max:255',
                'content_type' => 'required|string|in:video/webm,video/mp4,video/quicktime',
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

            // Validate recording consent for the user
            $participant = $room->participants()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->first();

            if (!$participant || !$participant->hasSttConsent()) { // Using same consent field for now
                return response()->json([
                    'error' => 'Video recording consent required',
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

            if (!$participant || !$participant->hasSttConsent()) { // Using same consent field for now
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
     * Upload a recording to Google Drive
     */
    public function uploadGoogleDrive(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'video' => 'required|file|mimes:webm,mp4|max:102400', // 100MB max
                'metadata' => 'nullable|json',
            ]);

            $metadata = [];
            if (isset($validated['metadata'])) {
                $metadata = json_decode($validated['metadata'], true);
            }

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

            if (!$participant || !$participant->hasSttConsent()) { // Using same consent field for now
                return response()->json([
                    'error' => 'Video recording consent required',
                    'requires_consent' => true
                ], 403);
            }

            // Upload to Google Drive
            $result = $this->uploadToGoogleDrive->execute(
                $room,
                $user,
                $validated['video'],
                $metadata
            );

            // Create recording record in database
            $recording = RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider' => 'google_drive',
                'provider_file_id' => $result['provider_file_id'],
                'filename' => $result['filename'],
                'size_bytes' => $result['size_bytes'],
                'started_at_ms' => $metadata['started_at_ms'] ?? 0,
                'ended_at_ms' => $metadata['ended_at_ms'] ?? 0,
                'mime_type' => $validated['video']->getMimeType(),
                'status' => 'uploaded',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recording uploaded to Google Drive successfully',
                'recording_id' => $recording->id,
                'web_view_link' => $result['web_view_link'],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to upload to Google Drive', [
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
                'content_type' => 'required|string|in:video/webm,video/mp4,video/quicktime',
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

            if (!$participant || !$participant->hasSttConsent()) {
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

            if (!$participant || !$participant->hasSttConsent()) {
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
                $validated['metadata'] ?? []
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
     * Store a new recording chunk for a room
     */
    public function store(Request $request, Room $room): JsonResponse
    {
        try {
            $validated = $request->validate([
                'video' => 'required|file|mimes:webm,mp4|max:102400', // 100MB max
                'metadata' => 'required|json',
            ]);

            $metadata = json_decode($validated['metadata'], true);
            
            $metadataValidated = validator($metadata, [
                'user_id' => 'nullable|integer|exists:users,id',
                'started_at_ms' => 'required|integer|min:0',
                'ended_at_ms' => 'required|integer|min:0|gt:started_at_ms',
                'size_bytes' => 'required|integer|min:0',
                'mime_type' => 'required|string|max:100',
                'filename' => 'required|string|max:255',
            ])->validate();

            // Check if user has access to this room
            $user = $request->user();
            if (!$room->canUserAccess($user)) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            // Additional check: user must be the room creator or an active participant
            if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
                return response()->json(['error' => 'Only room participants can upload recordings'], 403);
            }

            // Check if recording is enabled for this room
            $room->load('recordingSettings');
            if (!$room->recordingSettings || !$room->recordingSettings->isRecordingEnabled()) {
                return response()->json(['error' => 'Video recording is not enabled for this room'], 403);
            }

            // Validate recording consent for the user
            $userId = $metadataValidated['user_id'] ?? $user?->id;
            if ($userId) {
                $participant = $room->participants()
                    ->where('user_id', $userId)
                    ->whereNull('left_at')
                    ->first();

                if (!$participant) {
                    return response()->json(['error' => 'User is not an active participant in this room'], 403);
                }

                if (!$participant->hasSttConsent()) { // Using same consent field for now
                    return response()->json([
                        'error' => 'Video recording consent required',
                        'requires_consent' => true
                    ], 403);
                }
            }

            $videoFile = $validated['video'];
            
            // For now, store locally as placeholder
            // TODO: Implement Wasabi/Google Drive upload based on room settings
            $path = $videoFile->store('recordings/' . $room->id, 'local');

            // Create recording record
            $recording = RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $userId,
                'provider' => 'local', // TODO: Change based on room settings
                'provider_file_id' => $path,
                'filename' => $metadataValidated['filename'],
                'size_bytes' => $metadataValidated['size_bytes'],
                'started_at_ms' => $metadataValidated['started_at_ms'],
                'ended_at_ms' => $metadataValidated['ended_at_ms'],
                'mime_type' => $metadataValidated['mime_type'],
                'status' => 'uploaded',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recording uploaded successfully',
                'recording_id' => $recording->id
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to save room recording', [
                'room_id' => $room->id,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to save recording'
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
}
