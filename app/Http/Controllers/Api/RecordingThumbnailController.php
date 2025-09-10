<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\WasabiS3Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecordingThumbnailController extends Controller
{
    /**
     * Upload a thumbnail for a recording
     *
     * POST /api/recordings/thumbnail
     * Body: { recording_id, thumbnail }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'recording_id' => 'required|integer|exists:room_recordings,id',
                'thumbnail' => 'required|string', // Base64 encoded image
            ]);

            $user = Auth::user();
            $recording = RoomRecording::findOrFail($validated['recording_id']);

            // Verify user owns this recording
            if ($recording->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Decode base64 thumbnail
            $thumbnailData = $this->decodeBase64Image($validated['thumbnail']);
            if (! $thumbnailData) {
                return response()->json(['error' => 'Invalid thumbnail data'], 400);
            }

            // Upload thumbnail to storage
            $thumbnailUrl = $this->uploadThumbnailToStorage($recording, $thumbnailData);

            // Update recording with thumbnail URL
            $recording->update(['thumbnail_url' => $thumbnailUrl]);

            Log::info('Thumbnail uploaded successfully', [
                'recording_id' => $recording->id,
                'user_id' => $user->id,
                'thumbnail_url' => $thumbnailUrl,
            ]);

            return response()->json([
                'success' => true,
                'thumbnail_url' => $thumbnailUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('Thumbnail upload failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->only(['recording_id']),
            ]);

            return response()->json([
                'error' => 'Failed to upload thumbnail',
            ], 500);
        }
    }

    /**
     * Generate a stream URL for a recording
     *
     * GET /api/recordings/{recording}/stream
     */
    public function generateStreamUrl(RoomRecording $recording): JsonResponse
    {
        try {
            $user = Auth::user();

            // Verify user owns this recording
            if ($recording->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($recording->provider !== 'wasabi') {
                return response()->json(['error' => 'Streaming only supported for Wasabi provider'], 400);
            }

            $storageAccount = $user->storageAccounts()
                ->where('provider', 'wasabi')
                ->where('is_active', true)
                ->first();

            if (! $storageAccount) {
                return response()->json(['error' => 'No active Wasabi storage account found'], 400);
            }

            $wasabiService = new WasabiS3Service($storageAccount);

            // Generate a presigned URL for streaming (4 hours)
            $streamResult = $wasabiService->generatePresignedDownloadUrl($recording->provider_file_id, 60 * 4);

            // Update recording with stream URL
            $recording->update(['stream_url' => $streamResult['download_url']]);

            return response()->json([
                'success' => true,
                'stream_url' => $streamResult['download_url'],
            ]);

        } catch (\Exception $e) {
            Log::error('Stream URL generation failed', [
                'recording_id' => $recording->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to generate stream URL',
            ], 500);
        }
    }

    private function decodeBase64Image(string $base64String): ?array
    {
        // Remove data URL prefix if present
        if (strpos($base64String, 'data:image/') === 0) {
            $parts = explode(',', $base64String, 2);
            if (count($parts) !== 2) {
                return null;
            }

            // Extract mime type
            preg_match('/data:image\/([a-zA-Z]+);base64/', $parts[0], $matches);
            $mimeType = isset($matches[1]) ? "image/{$matches[1]}" : 'image/jpeg';

            $base64Data = $parts[1];
        } else {
            $base64Data = $base64String;
            $mimeType = 'image/jpeg';
        }

        $imageData = base64_decode($base64Data);
        if ($imageData === false) {
            return null;
        }

        return [
            'data' => $imageData,
            'mime_type' => $mimeType,
            'extension' => $mimeType === 'image/png' ? 'png' : 'jpg',
        ];
    }

    private function uploadThumbnailToStorage(RoomRecording $recording, array $thumbnailData): string
    {
        if ($recording->provider === 'wasabi') {
            return $this->uploadThumbnailToWasabi($recording, $thumbnailData);
        }

        // Fallback to local storage
        return $this->uploadThumbnailToLocal($recording, $thumbnailData);
    }

    private function uploadThumbnailToWasabi(RoomRecording $recording, array $thumbnailData): string
    {
        $storageAccount = $recording->user->storageAccounts()
            ->where('provider', 'wasabi')
            ->where('is_active', true)
            ->first();

        if (! $storageAccount) {
            throw new \Exception('No active Wasabi storage account found');
        }

        $wasabiService = new WasabiS3Service($storageAccount);
        $credentials = $storageAccount->encrypted_credentials;
        $bucket = $credentials['bucket_name'];

        // Generate thumbnail key
        $thumbnailKey = str_replace('.webm', '_thumbnail.'.$thumbnailData['extension'], $recording->provider_file_id);

        // Create temporary file
        $tempPath = tempnam(sys_get_temp_dir(), 'thumbnail_');
        file_put_contents($tempPath, $thumbnailData['data']);

        try {
            // Upload to Wasabi
            $wasabiService->getS3Client()->putObject([
                'Bucket' => $bucket,
                'Key' => $thumbnailKey,
                'SourceFile' => $tempPath,
                'ContentType' => $thumbnailData['mime_type'],
                'ACL' => 'private',
            ]);

            // Generate presigned URL for thumbnail (1 week)
            $thumbnailResult = $wasabiService->generatePresignedDownloadUrl($thumbnailKey, 60 * 24 * 7);

            return $thumbnailResult['download_url'];

        } finally {
            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    private function uploadThumbnailToLocal(RoomRecording $recording, array $thumbnailData): string
    {
        $filename = "thumbnail_{$recording->id}.".$thumbnailData['extension'];
        $path = "thumbnails/{$filename}";

        // Store in public disk
        \Storage::disk('public')->put($path, $thumbnailData['data']);

        return \Storage::disk('public')->url($path);
    }
}
