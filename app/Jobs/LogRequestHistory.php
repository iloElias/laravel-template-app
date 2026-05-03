<?php

namespace App\Jobs;

use App\Services\ClickhouseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogRequestHistory implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    /**
     * @param array{
     *     session_id: int,
     *     route: string,
     *     method: string,
     *     payload: string|null,
     *     created_at: string,
     * } $data
     */
    public function __construct(private readonly array $data)
    {
        $this->onQueue('analytics');
    }

    public function handle(ClickhouseService $clickhouse): void
    {
        $clickhouse->insert('request_history', [$this->data]);
    }
}
