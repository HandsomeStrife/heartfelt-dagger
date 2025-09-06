<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\User\Models\UserStorageAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssemblyAIController extends Controller
{
    /**
     * Generate a temporary token for AssemblyAI streaming
     */
    public function generateToken(Request $request): JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string'
        ]);

        try {
            $apiKey = $request->input('api_key');
            
            // Make request to AssemblyAI to create temporary token
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json'
            ])->get('https://streaming.assemblyai.com/v3/token', [
                'expires_in_seconds' => 600 // 10 minutes (max allowed)
            ]);

            if (!$response->successful()) {
                Log::error('AssemblyAI token generation failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'error' => 'Failed to generate AssemblyAI token'
                ], 400);
            }

            $data = $response->json();
            
            return response()->json([
                'token' => $data['token']
            ]);

        } catch (\Exception $e) {
            Log::error('AssemblyAI token generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
