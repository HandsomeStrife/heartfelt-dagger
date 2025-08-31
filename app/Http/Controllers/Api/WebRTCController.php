<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebRTCController extends Controller
{
    /**
     * Get ICE configuration for WebRTC with Cloudflare STUN/TURN servers
     */
    public function iceConfig(): JsonResponse
    {
        // Check if TURN is disabled via environment flag
        if (Config::get('CF_TURN_DISABLED', false)) {
            return response()->json($this->getFallbackIceConfig());
        }

        // Use cache to avoid hitting Cloudflare API too frequently
        $cacheKey = 'cf:ice:' . (int) (time() / 300); // 5-minute cache buckets
        
        $iceConfig = Cache::remember($cacheKey, 300, function () {
            return $this->fetchCloudflareIceConfig();
        });

        return response()->json($iceConfig);
    }

    /**
     * Fetch ICE configuration from Cloudflare TURN service
     */
    private function fetchCloudflareIceConfig(): array
    {
        try {
            $keyId = Config::get('CF_TURN_KEY_ID');
            $token = Config::get('CF_TURN_API_TOKEN');
            $ttl = (int) Config::get('CF_TURN_TTL', 7200);

            if (!$keyId || !$token) {
                Log::warning('Cloudflare TURN credentials not configured, falling back to STUN-only');
                return $this->getFallbackIceConfig();
            }

            $response = Http::withToken($token)
                ->timeout(10)
                ->post(
                    "https://rtc.live.cloudflare.com/v1/turn/keys/{$keyId}/credentials/generate-ice-servers",
                    ['ttl' => $ttl]
                );

            if ($response->successful()) {
                $config = $response->json();
                
                // Validate response structure
                if (is_array($config) && isset($config['iceServers']) && is_array($config['iceServers'])) {
                    // Optional: Remove :53 candidates if unwanted
                    foreach ($config['iceServers'] as &$server) {
                        if (!empty($server['urls'])) {
                            $server['urls'] = array_values(
                                array_filter(
                                    (array) $server['urls'], 
                                    fn($url) => !preg_match('/:\s*53(\b|\?)/', $url)
                                )
                            );
                        }
                    }
                    
                    Log::info('Successfully fetched Cloudflare ICE configuration', [
                        'servers_count' => count($config['iceServers']),
                        'ttl' => $ttl
                    ]);
                    
                    return $config;
                }
            }

            Log::warning('Invalid response from Cloudflare TURN API', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch Cloudflare ICE configuration', [
                'error' => $e->getMessage()
            ]);
        }

        // Fall back to STUN-only configuration
        return $this->getFallbackIceConfig();
    }

    /**
     * Get fallback STUN-only ICE configuration
     */
    private function getFallbackIceConfig(): array
    {
        return [
            'iceServers' => [
                [
                    'urls' => [
                        'stun:stun.cloudflare.com:3478',
                        'stun:stun.l.google.com:19302'
                    ]
                ]
            ]
        ];
    }
}
