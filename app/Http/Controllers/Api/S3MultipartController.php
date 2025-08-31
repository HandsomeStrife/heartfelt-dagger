<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * S3 Multipart Upload Controller
 * 
 * Provides endpoints for Uppy @uppy/aws-s3-multipart integration
 * Supports Wasabi, AWS S3, and other S3-compatible storage providers
 */
class S3MultipartController extends Controller
{
    /**
     * Create a new multipart upload
     * 
     * POST /api/uploads/s3/multipart/create
     * Body: { filename, type, size, room_id, started_at_ms, ended_at_ms }
     * Returns: { uploadId, key }
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filename' => 'required|string|max:255',
                'type' => 'nullable|string|max:255',
                'size' => 'nullable|integer|min:1',
                'room_id' => 'required|integer|exists:rooms,id',
                'started_at_ms' => 'nullable|integer',
                'ended_at_ms' => 'nullable|integer',
            ]);

            $room = Room::findOrFail($validated['room_id']);
            $user = Auth::user();

            // Validate room access and recording permissions
            $this->validateRoomAccess($room, $user);
            $this->validateRecordingPermissions($room, $user);

            // Get storage configuration
            $storageAccount = $this->getStorageAccount($room);
            $wasabiService = new WasabiS3Service($storageAccount);

            // Generate server-controlled object key
            $key = $this->buildKey($room, $user->id, $validated['filename']);

            // Create multipart upload
            $credentials = $storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            $s3Client = $wasabiService->createS3ClientWithCredentials($credentials);
            $result = $s3Client->createMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $key,
                'ACL' => 'private',
                'ContentType' => $validated['type'] ?? 'application/octet-stream',
                'Metadata' => [
                    'room_id' => (string) $validated['room_id'],
                    'user_id' => (string) $user->id,
                    'started_at' => (string) ($validated['started_at_ms'] ?? ''),
                    'ended_at' => (string) ($validated['ended_at_ms'] ?? ''),
                ],
            ]);

            Log::info('S3 Multipart upload created', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'upload_id' => $result['UploadId'],
                'key' => $key,
            ]);

            return response()->json([
                'uploadId' => $result['UploadId'],
                'key' => $key,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('S3 CreateMultipartUpload failed', [
                'room_id' => $request->input('room_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Unable to start multipart upload'
            ], 502);
        }
    }

    /**
     * Sign a part for upload
     * 
     * POST /api/uploads/s3/multipart/sign
     * Body: { uploadId, key, partNumber, room_id }
     * Returns: { url, headers }
     */
    public function signPart(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'uploadId' => 'required|string',
                'key' => 'required|string',
                'partNumber' => 'required|integer|min:1|max:10000',
                'room_id' => 'required|integer|exists:rooms,id',
            ]);

            $room = Room::findOrFail($validated['room_id']);
            $user = Auth::user();

            // Validate room access and key authorization
            $this->validateRoomAccess($room, $user);
            $this->validateKeyAuthorization($validated['key'], $room, $user);

            // Get storage configuration
            $storageAccount = $this->getStorageAccount($room);
            $credentials = $storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            $wasabiService = new WasabiS3Service($storageAccount);
            $s3Client = $wasabiService->createS3ClientWithCredentials($credentials);

            // Create presigned URL for part upload
            $command = $s3Client->getCommand('UploadPart', [
                'Bucket' => $bucket,
                'Key' => $validated['key'],
                'UploadId' => $validated['uploadId'],
                'PartNumber' => $validated['partNumber'],
            ]);

            $request = $s3Client->createPresignedRequest($command, '+15 minutes');

            return response()->json([
                'url' => (string) $request->getUri(),
                'headers' => [
                    // Content-Length will be set by Uppy automatically
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('S3 UploadPart sign failed', [
                'room_id' => $request->input('room_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Unable to sign part'
            ], 502);
        }
    }

    /**
     * Complete multipart upload
     * 
     * POST /api/uploads/s3/multipart/complete
     * Body: { uploadId, key, parts: [{ PartNumber, ETag }], room_id, started_at_ms, ended_at_ms, filename, mime }
     * Returns: { location, key, bucket, etag, size }
     */
    public function complete(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'uploadId' => 'required|string',
                'key' => 'required|string',
                'parts' => 'required|array|min:1',
                'parts.*.PartNumber' => 'required|integer|min:1',
                'parts.*.ETag' => 'required|string',
                'room_id' => 'required|integer|exists:rooms,id',
                'started_at_ms' => 'nullable|integer',
                'ended_at_ms' => 'nullable|integer',
                'filename' => 'nullable|string|max:255',
                'mime' => 'nullable|string|max:255',
            ]);

            $room = Room::findOrFail($validated['room_id']);
            $user = Auth::user();

            // Validate room access and key authorization
            $this->validateRoomAccess($room, $user);
            $this->validateKeyAuthorization($validated['key'], $room, $user);

            // Get storage configuration
            $storageAccount = $this->getStorageAccount($room);
            $credentials = $storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            $wasabiService = new WasabiS3Service($storageAccount);
            $s3Client = $wasabiService->createS3ClientWithCredentials($credentials);

            // Sort parts by PartNumber (AWS requirement)
            usort($validated['parts'], fn($a, $b) => $a['PartNumber'] <=> $b['PartNumber']);

            // Complete multipart upload
            $result = $s3Client->completeMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $validated['key'],
                'UploadId' => $validated['uploadId'],
                'MultipartUpload' => ['Parts' => $validated['parts']],
            ]);

            // Get canonical size and metadata from object
            $head = $s3Client->headObject([
                'Bucket' => $bucket,
                'Key' => $validated['key'],
            ]);

            // Create recording record in database
            $recording = RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider' => 'wasabi',
                'provider_file_id' => $validated['key'],
                'filename' => $validated['filename'] ?? basename($validated['key']),
                'size_bytes' => (int) $head['ContentLength'],
                'started_at_ms' => $validated['started_at_ms'] ?? 0,
                'ended_at_ms' => $validated['ended_at_ms'] ?? 0,
                'mime_type' => $validated['mime'] ?? ($head['ContentType'] ?? 'video/webm'),
                'status' => 'uploaded',
            ]);

            Log::info('S3 Multipart upload completed', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'upload_id' => $validated['uploadId'],
                'key' => $validated['key'],
                'recording_id' => $recording->id,
                'size_bytes' => $head['ContentLength'],
            ]);

            return response()->json([
                'location' => $result['Location'] ?? null,
                'key' => $validated['key'],
                'bucket' => $bucket,
                'etag' => trim($head['ETag'], '"'),
                'size' => (int) $head['ContentLength'],
                'recording_id' => $recording->id,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('S3 CompleteMultipart failed', [
                'room_id' => $request->input('room_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Unable to complete multipart upload'
            ], 502);
        }
    }

    /**
     * Abort multipart upload
     * 
     * POST /api/uploads/s3/multipart/abort
     * Body: { uploadId, key, room_id }
     */
    public function abort(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'uploadId' => 'required|string',
                'key' => 'required|string',
                'room_id' => 'required|integer|exists:rooms,id',
            ]);

            $room = Room::findOrFail($validated['room_id']);
            $user = Auth::user();

            // Validate room access and key authorization
            $this->validateRoomAccess($room, $user);
            $this->validateKeyAuthorization($validated['key'], $room, $user);

            // Get storage configuration
            $storageAccount = $this->getStorageAccount($room);
            $credentials = $storageAccount->encrypted_credentials;
            $bucket = $credentials['bucket_name'];

            $wasabiService = new WasabiS3Service($storageAccount);
            $s3Client = $wasabiService->createS3ClientWithCredentials($credentials);

            // Abort multipart upload
            $s3Client->abortMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $validated['key'],
                'UploadId' => $validated['uploadId'],
            ]);

            Log::info('S3 Multipart upload aborted', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'upload_id' => $validated['uploadId'],
                'key' => $validated['key'],
            ]);

            return response()->json(['aborted' => true]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::warning('S3 AbortMultipart failed', [
                'room_id' => $request->input('room_id'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            // Don't hard fail UI; sometimes upload is already gone
            return response()->json(['aborted' => true]);
        }
    }

    /**
     * Build a safe server-side object key
     * Locks uploads to rooms/{room_id}/users/{user_id}/{uuid}.{ext}
     */
    protected function buildKey(Room $room, int $userId, string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)) ?: 'webm';
        
        // Clamp to allowed extensions
        $ext = in_array($ext, ['webm', 'mp4', 'mov', 'm4a', 'wav', 'mp3']) ? $ext : 'bin';

        return "rooms/{$room->id}/users/{$userId}/" . Str::uuid() . '.' . $ext;
    }

    /**
     * Validate room access for the user
     */
    protected function validateRoomAccess(Room $room, $user): void
    {
        if (!$room->canUserAccess($user)) {
            throw new \Exception('Access denied to room');
        }

        if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
            throw new \Exception('Only room participants can upload recordings');
        }
    }

    /**
     * Validate recording permissions
     */
    protected function validateRecordingPermissions(Room $room, $user): void
    {
        // Check if recording is enabled
        $room->load('recordingSettings');
        if (!$room->recordingSettings || !$room->recordingSettings->isRecordingEnabled()) {
            throw new \Exception('Video recording is not enabled for this room');
        }

        // Check consent
        $participant = $room->participants()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant || !$participant->hasSttConsent()) {
            throw new \Exception('Video recording consent required');
        }
    }

    /**
     * Get the storage account for the room
     */
    protected function getStorageAccount(Room $room): UserStorageAccount
    {
        if ($room->recordingSettings->storage_provider !== 'wasabi') {
            throw new \Exception('Room is not configured for Wasabi storage');
        }

        $storageAccount = UserStorageAccount::find($room->recordingSettings->storage_account_id);
        if (!$storageAccount || $storageAccount->provider !== 'wasabi') {
            throw new \Exception('Wasabi storage account not found or invalid');
        }

        if ($storageAccount->user_id !== $room->creator_id) {
            throw new \Exception('Storage account does not belong to room creator');
        }

        return $storageAccount;
    }

    /**
     * Ensure the object key is within the caller's allowed prefix
     */
    protected function validateKeyAuthorization(string $key, Room $room, $user): void
    {
        $allowedPrefix = "rooms/{$room->id}/users/{$user->id}/";

        if (!str_starts_with($key, $allowedPrefix)) {
            throw new \Exception('Key not allowed for this user/room');
        }
    }
}
