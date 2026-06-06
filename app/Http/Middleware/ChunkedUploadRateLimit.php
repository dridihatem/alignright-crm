<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ChunkedUploadRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication required'
            ], 401);
        }

        // Rate limiting rules
        $limits = [
            'uploads_per_minute' => 10,    // Max 10 file uploads per minute
            'chunks_per_minute' => 60,     // Max 60 chunks per minute
            'sessions_per_hour' => 30,     // Max 30 upload sessions per hour
            'total_size_per_hour' => 2 * 1024 * 1024 * 1024, // 2GB per hour
        ];

        $endpoint = $this->getEndpointType($request);
        
        try {
            switch ($endpoint) {
                case 'initialize':
                    $this->checkUploadLimits($userId, $limits);
                    $this->trackUpload($userId, $request);
                    break;
                    
                case 'upload_chunk':
                    $this->checkChunkLimits($userId, $limits);
                    $this->trackChunk($userId, $request);
                    break;
                    
                default:
                    // For status, complete, cancel - lighter limits
                    $this->checkGeneralLimits($userId);
            }

        } catch (\Exception $e) {
            Log::warning('Upload rate limit exceeded', [
                'user_id' => $userId,
                'endpoint' => $endpoint,
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'retry_after' => 60 // Suggest retry after 60 seconds
            ], 429);
        }

        return $next($request);
    }

    /**
     * Determine endpoint type from request
     */
    private function getEndpointType(Request $request): string
    {
        $path = $request->path();
        
        if (str_contains($path, '/initialize')) {
            return 'initialize';
        } elseif (str_contains($path, '/upload-chunk')) {
            return 'upload_chunk';
        } elseif (str_contains($path, '/complete')) {
            return 'complete';
        } elseif (str_contains($path, '/cancel')) {
            return 'cancel';
        } elseif (str_contains($path, '/status')) {
            return 'status';
        }
        
        return 'unknown';
    }

    /**
     * Check upload initialization limits
     */
    private function checkUploadLimits(int $userId, array $limits): void
    {
        // Check uploads per minute
        $uploadsKey = "upload_rate:{$userId}:uploads_minute";
        $uploadsCount = Cache::get($uploadsKey, 0);
        
        if ($uploadsCount >= $limits['uploads_per_minute']) {
            throw new \Exception('Too many upload attempts. Please wait before starting new uploads.');
        }

        // Check sessions per hour
        $sessionsKey = "upload_rate:{$userId}:sessions_hour";
        $sessionsCount = Cache::get($sessionsKey, 0);
        
        if ($sessionsCount >= $limits['sessions_per_hour']) {
            throw new \Exception('Hourly upload session limit reached. Please try again later.');
        }

        // Check total size per hour
        $sizeKey = "upload_rate:{$userId}:size_hour";
        $totalSize = Cache::get($sizeKey, 0);
        $requestSize = request()->get('file_size', 0);
        
        if (($totalSize + $requestSize) > $limits['total_size_per_hour']) {
            throw new \Exception('Hourly upload size limit reached. Please try again later.');
        }
    }

    /**
     * Check chunk upload limits
     */
    private function checkChunkLimits(int $userId, array $limits): void
    {
        $chunksKey = "upload_rate:{$userId}:chunks_minute";
        $chunksCount = Cache::get($chunksKey, 0);
        
        if ($chunksCount >= $limits['chunks_per_minute']) {
            throw new \Exception('Too many chunk uploads. Please slow down your upload rate.');
        }
    }

    /**
     * Check general API limits
     */
    private function checkGeneralLimits(int $userId): void
    {
        $apiKey = "upload_rate:{$userId}:api_minute";
        $apiCount = Cache::get($apiKey, 0);
        
        if ($apiCount >= 100) { // 100 API calls per minute
            throw new \Exception('API rate limit exceeded. Please wait before making more requests.');
        }
        
        // Track API call
        Cache::put($apiKey, $apiCount + 1, 60);
    }

    /**
     * Track upload initialization
     */
    private function trackUpload(int $userId, Request $request): void
    {
        // Track uploads per minute
        $uploadsKey = "upload_rate:{$userId}:uploads_minute";
        $uploadsCount = Cache::get($uploadsKey, 0);
        Cache::put($uploadsKey, $uploadsCount + 1, 60);

        // Track sessions per hour
        $sessionsKey = "upload_rate:{$userId}:sessions_hour";
        $sessionsCount = Cache::get($sessionsKey, 0);
        Cache::put($sessionsKey, $sessionsCount + 1, 3600);

        // Track total size per hour
        $sizeKey = "upload_rate:{$userId}:size_hour";
        $totalSize = Cache::get($sizeKey, 0);
        $requestSize = $request->get('file_size', 0);
        Cache::put($sizeKey, $totalSize + $requestSize, 3600);

        Log::info('Upload rate tracking', [
            'user_id' => $userId,
            'uploads_this_minute' => $uploadsCount + 1,
            'sessions_this_hour' => $sessionsCount + 1,
            'size_this_hour' => $totalSize + $requestSize
        ]);
    }

    /**
     * Track chunk upload
     */
    private function trackChunk(int $userId, Request $request): void
    {
        $chunksKey = "upload_rate:{$userId}:chunks_minute";
        $chunksCount = Cache::get($chunksKey, 0);
        Cache::put($chunksKey, $chunksCount + 1, 60);
    }

    /**
     * Get current rate limit status for user
     */
    public static function getRateLimitStatus(int $userId): array
    {
        return [
            'uploads_this_minute' => Cache::get("upload_rate:{$userId}:uploads_minute", 0),
            'chunks_this_minute' => Cache::get("upload_rate:{$userId}:chunks_minute", 0),
            'sessions_this_hour' => Cache::get("upload_rate:{$userId}:sessions_hour", 0),
            'size_this_hour' => Cache::get("upload_rate:{$userId}:size_hour", 0),
            'api_calls_this_minute' => Cache::get("upload_rate:{$userId}:api_minute", 0),
        ];
    }
}