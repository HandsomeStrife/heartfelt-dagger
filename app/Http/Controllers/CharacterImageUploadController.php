<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Character\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CharacterImageUploadController extends Controller
{
    /**
     * Upload a character profile image via Uppy
     */
    public function upload(Request $request, string $character_key): JsonResponse
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'profile_image' => 'required|image|max:2048', // 2MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 400);
            }

            // Find the character
            $character = Character::where('character_key', $character_key)->first();
            if (!$character) {
                return response()->json([
                    'success' => false,
                    'error' => 'Character not found'
                ], 404);
            }

            $file = $request->file('profile_image');
            
            // Check if S3 is configured, if so use S3, otherwise fall back to local
            if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
                return $this->uploadToS3($file, $character, $character_key);
            } else {
                return $this->uploadToLocalStorage($file, $character, $character_key);
            }

        } catch (\Exception $e) {
            Log::error('Character image upload failed', [
                'character_key' => $character_key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload to local storage for development
     */
    private function uploadToLocalStorage($file, Character $character, string $character_key): JsonResponse
    {
        // Generate organized path: character-portraits/character-key/filename
        $date = now();
        $extension = $file->getClientOriginalExtension();
        $sanitized_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp = $date->format('His');
        $final_filename = "{$sanitized_filename}_{$timestamp}.{$extension}";
        
        $directory = "character-portraits/{$character_key}";
        $relative_path = "{$directory}/{$final_filename}";

        // Store to local public disk
        $path = $file->storeAs($directory, $final_filename, 'public');

        if (!$path) {
            throw new \Exception('Failed to store file locally');
        }

        // Update character record
        $character->update([
            'profile_image_path' => $path
        ]);

        Log::info('Character image uploaded to local storage', [
            'character_key' => $character_key,
            'path' => $path,
            'filename' => $final_filename
        ]);

        return response()->json([
            'success' => true,
            'image_path' => $path,
            'image_url' => Storage::disk('public')->url($path),
            'filename' => $final_filename,
            'message' => 'Image uploaded successfully to local storage'
        ]);
    }

    /**
     * Upload to S3 for production
     */
    private function uploadToS3($file, Character $character, string $character_key): JsonResponse
    {
        // Generate organized path: year/month/day/character-key/filename
        $date = now();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        
        $extension = $file->getClientOriginalExtension();
        $sanitized_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp = $date->format('His');
        $final_filename = "{$sanitized_filename}_{$timestamp}.{$extension}";
        
        $directory = "character-portraits/{$year}/{$month}/{$day}/{$character_key}";
        $s3_path = "{$directory}/{$final_filename}";

        // Store to S3
        $path = $file->storeAs($directory, $final_filename, 's3');

        if (!$path) {
            throw new \Exception('Failed to store file to S3');
        }

        // Verify file exists
        if (!Storage::disk('s3')->exists($path)) {
            throw new \Exception('File upload to S3 failed - file does not exist after upload');
        }

        // Update character record
        $character->update([
            'profile_image_path' => $path
        ]);

        Log::info('Character image uploaded to S3', [
            'character_key' => $character_key,
            'path' => $path,
            'filename' => $final_filename
        ]);

        return response()->json([
            'success' => true,
            'image_path' => $path,
            'image_url' => Storage::disk('s3')->url($path),
            'filename' => $final_filename,
            'message' => 'Image uploaded successfully to S3'
        ]);
    }
}
