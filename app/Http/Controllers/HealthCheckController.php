<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

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
            $cache = cache()->get('health_check', 'OK');

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => $cache,
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cache connection failed',
                'data' => [
                    'status' => 'unhealthy',
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
                    'status' => $queue,
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
}
