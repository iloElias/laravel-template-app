<?php

namespace App\Jobs;

use App\Services\ClickhouseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogErrorEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    /**
     * @param array{
     *     url: string|null,
     *     error_message: string,
     *     stack_trace: string|null,
     *     request_data: string|null,
     *     created_at: string,
     * } $data
     */
    public function __construct(private readonly array $data)
    {
        $this->onQueue('analytics');
    }

    public function handle(ClickhouseService $clickhouse): void
    {
        $clickhouse->insert('error_log', [$this->data]);
    }
}
