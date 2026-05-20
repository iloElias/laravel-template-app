<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    public function api()
    {
        return response()->json([
            'message' => 'OK',
            'data' => [
                'status' => 'healthy',
                'timestamp' => now(),
            ],
        ]);
    }

    public function database()
    {
        try {
            DB::connection()->getPdo();

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Database connection failed',
                'data' => [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 500);
        }
    }

    public function cache()
    {
        try {
            $key = 'health_check:' . uniqid();
            Cache::put($key, 'ok', 5);

            $value = Cache::get($key);
            Cache::forget($key);

            if ($value !== 'ok') {
                throw new \RuntimeException('Cache read/write mismatch');
            }

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => 'healthy',
                    'driver' => config('cache.default'),
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cache connection failed',
                'data' => [
                    'status' => 'unhealthy',
                    'driver' => config('cache.default'),
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 500);
        }
    }

    public function queue()
    {
        try {
            $queue = Queue::getDefaultDriver();

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => 'healthy',
                    'driver' => $queue,
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Queue connection failed',
                'data' => [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 500);
        }
    }

    public function redis()
    {
        try {
            Redis::ping();

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Redis connection failed',
                'data' => [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 500);
        }
    }
}
