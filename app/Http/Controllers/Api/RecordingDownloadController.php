<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecordingDownloadController extends Controller
{
    public function download(Request $request, Room $room, RoomRecording $recording): \Symfony\Component\HttpFoundation\Response
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(401, 'Authentication required');
        }

        // Verify user has access to this room
        if (! $this->userCanAccessRoom($room, $user)) {
            abort(403, 'Access denied');
        }

        // Verify recording belongs to this room
        if ($recording->room_id !== $room->id) {
            abort(404, 'Recording not found in this room');
        }

        // Verify recording is ready
        if (! in_array($recording->status, ['ready', 'uploaded'])) {
            abort(400, 'Recording not available for download');
        }

        try {
            if ($recording->provider === 'local') {
                return $this->downloadFromLocal($recording, $user);
            } elseif ($recording->provider === 'google_drive') {
                return $this->downloadFromGoogleDrive($recording, $user);
            } elseif ($recording->provider === 'wasabi') {
                return $this->downloadFromWasabi($recording, $user);
            } else {
                abort(400, 'Download not supported for this storage provider');
            }
        } catch (\Exception $e) {
            abort(500, 'Download failed: '.$e->getMessage());
        }
    }

    private function downloadFromLocal(RoomRecording $recording, User $user): Response
    {
        // Check if file exists in local storage
        if (! Storage::disk('local')->exists($recording->provider_file_id)) {
            abort(404, 'Recording file not found');
        }

        // Get file content
        $fileContent = Storage::disk('local')->get($recording->provider_file_id);

        // Return file as download response
        return response($fileContent)
            ->header('Content-Type', $recording->mime_type)
            ->header('Content-Disposition', 'attachment; filename="'.$recording->filename.'"')
            ->header('Content-Length', (string) $recording->size_bytes);
    }

    private function downloadFromGoogleDrive(RoomRecording $recording, User $user): Response
    {
        // Find user's active Google Drive account
        $storageAccount = $user->storageAccounts()
            ->where('provider', 'google_drive')
            ->where('is_active', true)
            ->first();

        if (! $storageAccount) {
            abort(400, 'No active Google Drive account found');
        }

        $googleDriveService = new GoogleDriveService($storageAccount);

        // Get the file content from Google Drive
        $fileContent = $googleDriveService->downloadFile($recording->provider_file_id);

        if (! $fileContent) {
            abort(404, 'File not found in Google Drive');
        }

        return response($fileContent)
            ->header('Content-Type', $recording->mime_type)
            ->header('Content-Disposition', 'attachment; filename="'.$recording->filename.'"')
            ->header('Content-Length', (string) $recording->size_bytes);
    }

    private function downloadFromWasabi(RoomRecording $recording, User $user): \Illuminate\Http\RedirectResponse
    {
        // Find user's active Wasabi account
        $storageAccount = $user->storageAccounts()
            ->where('provider', 'wasabi')
            ->where('is_active', true)
            ->first();

        if (! $storageAccount) {
            abort(400, 'No active Wasabi account found');
        }

        $wasabiService = new \Domain\Room\Services\WasabiS3Service($storageAccount);

        // Generate a presigned download URL and redirect
        $downloadData = $wasabiService->generatePresignedDownloadUrl($recording->provider_file_id, 3600);

        return redirect($downloadData['download_url']);
    }

    private function userCanAccessRoom(Room $room, User $user): bool
    {
        // User is the room creator
        if ($room->creator_id === $user->id) {
            return true;
        }

        // User is a participant in the room
        return $room->participants()->where('user_id', $user->id)->exists();
    }
}
