<?php

namespace App\Http\Controllers;

use App\Services\ClickhouseService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

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

    public function clickhouse(ClickhouseService $clickhouse)
    {
        try {
            $result = $clickhouse->select('SELECT 1 AS ping');

            if (empty($result) || !isset($result[0]['ping']) || $result[0]['ping'] !== 1) {
                throw new \RuntimeException('ClickHouse ping failed');
            }

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'ClickHouse connection failed',
                'data' => [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 500);
        }
    }

    public function s3()
    {
        try {
            $disk = Storage::disk('s3');

            // Test write, read, and delete operations
            $testFile = 'health-check-' . time() . '.txt';
            $testContent = 'health-check-test';

            $disk->put($testFile, $testContent);

            if (!$disk->exists($testFile)) {
                throw new \RuntimeException('S3 file write verification failed');
            }

            $content = $disk->get($testFile);

            if ($content !== $testContent) {
                throw new \RuntimeException('S3 file read verification failed');
            }

            $disk->delete($testFile);

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'S3 connection failed',
                'data' => [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 500);
        }
    }

    public function smtp()
    {
        try {
            $mailer = config('mail.default');
            $transport = Mail::mailer($mailer)->getSymfonyTransport();

            // For SMTP, verify the configuration is valid
            if (method_exists($transport, 'start')) {
                $transport->start();
            }

            return response()->json([
                'message' => 'OK',
                'data' => [
                    'status' => 'healthy',
                    'mailer' => $mailer,
                    'host' => config('mail.mailers.smtp.host'),
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'SMTP connection failed',
                'data' => [
                    'status' => 'unhealthy',
                    'mailer' => config('mail.default'),
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 500);
        }
    }

    /**
     * Aggregated health check - checks all services
     * Returns overall application health status
     */
    public function status(ClickhouseService $clickhouse)
    {
        $services = [];
        $overallHealthy = true;

        // Check Database
        try {
            DB::connection()->getPdo();
            $services['database'] = 'healthy';
        } catch (\Exception $e) {
            $services['database'] = 'unhealthy';
            $overallHealthy = false;
        }

        // Check Redis
        try {
            Redis::ping();
            $services['redis'] = 'healthy';
        } catch (\Exception $e) {
            $services['redis'] = 'unhealthy';
            $overallHealthy = false;
        }

        // Check Cache
        try {
            $key = 'health_check:' . uniqid();
            Cache::put($key, 'ok', 5);
            Cache::get($key);
            Cache::forget($key);
            $services['cache'] = 'healthy';
        } catch (\Exception $e) {
            $services['cache'] = 'unhealthy';
            $overallHealthy = false;
        }

        // Check Queue
        try {
            Queue::getDefaultDriver();
            $services['queue'] = 'healthy';
        } catch (\Exception $e) {
            $services['queue'] = 'unhealthy';
            $overallHealthy = false;
        }

        // Check ClickHouse
        try {
            $result = $clickhouse->select('SELECT 1 AS ping');
            $services['clickhouse'] = (!empty($result) && isset($result[0]['ping']) && $result[0]['ping'] === 1) ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            $services['clickhouse'] = 'unhealthy';
            $overallHealthy = false;
        }

        // Check S3
        try {
            $disk = Storage::disk('s3');
            $testFile = 'health-check-' . time() . '.txt';
            $disk->put($testFile, 'test');
            $disk->delete($testFile);
            $services['s3'] = 'healthy';
        } catch (\Exception $e) {
            $services['s3'] = 'unhealthy';
            $overallHealthy = false;
        }

        // Check SMTP (non-critical, doesn't affect overall status)
        try {
            $mailer = config('mail.default');
            $transport = Mail::mailer($mailer)->getSymfonyTransport();
            if (method_exists($transport, 'start')) {
                $transport->start();
            }
            $services['smtp'] = 'healthy';
        } catch (\Exception $e) {
            $services['smtp'] = 'unhealthy';
            // SMTP failure doesn't affect overall health
        }

        return response()->json([
            'message' => $overallHealthy ? 'All services healthy' : 'Some services are unhealthy',
            'data' => [
                'status' => $overallHealthy ? 'healthy' : 'degraded',
                'services' => $services,
                'timestamp' => now(),
            ],
        ], $overallHealthy ? 200 : 503);
    }

    /**
     * Readiness probe - checks if application is ready to receive traffic
     * Tests critical dependencies: Database, Cache, Queue, Redis
     */
    public function ready()
    {
        try {
            // Check critical services
            DB::connection()->getPdo();
            Redis::ping();

            $key = 'health_check:ready:' . uniqid();
            Cache::put($key, 'ok', 5);
            Cache::get($key);
            Cache::forget($key);

            Queue::getDefaultDriver();

            return response()->json([
                'message' => 'Application is ready',
                'data' => [
                    'status' => 'ready',
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Application is not ready',
                'data' => [
                    'status' => 'not-ready',
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ],
            ], 503);
        }
    }

    /**
     * Liveness probe - checks if application process is alive
     * Simple and fast - just confirms the process is responding
     */
    public function live()
    {
        return response()->json([
            'message' => 'Application is alive',
            'data' => [
                'status' => 'alive',
                'timestamp' => now(),
            ],
        ]);
    }
}
