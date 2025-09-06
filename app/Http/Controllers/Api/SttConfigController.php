<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Room\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SttConfigController extends Controller
{
    /**
     * Get STT configuration for a room
     */
    public function getConfig(Request $request, Room $room): JsonResponse
    {
        // Check if user has access to this room
        $user = $request->user();
        if (!$room->canUserAccess($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Get room recording settings
        $settings = $room->recordingSettings;
        if (!$settings || !$settings->isSttEnabled()) {
            return response()->json(['error' => 'STT not enabled for this room'], 400);
        }

        // If using browser STT, no additional config needed
        if ($settings->isUsingBrowserStt()) {
            return response()->json([
                'provider' => 'browser',
                'config' => []
            ]);
        }

        // If using AssemblyAI, get the API key
        if ($settings->isUsingAssemblyAI()) {
            $sttAccount = $settings->sttAccount;
            if (!$sttAccount) {
                return response()->json(['error' => 'AssemblyAI account not configured'], 400);
            }

            $credentials = $sttAccount->getCredentials();
            $apiKey = $credentials['api_key'] ?? null;

            if (!$apiKey) {
                return response()->json(['error' => 'AssemblyAI API key not found'], 400);
            }

            return response()->json([
                'provider' => 'assemblyai',
                'config' => [
                    'api_key' => $apiKey
                ]
            ]);
        }

        return response()->json(['error' => 'Unknown STT provider'], 400);
    }
}
