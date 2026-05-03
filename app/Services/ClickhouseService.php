<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class ClickhouseService
{
    private Client $client;

    private string $database;

    public function __construct()
    {
        $this->database = config('services.clickhouse.database', 'default');

        $this->client = new Client([
            'base_uri' => sprintf(
                'http://%s:%s',
                config('services.clickhouse.host', '127.0.0.1'),
                config('services.clickhouse.port', 8123),
            ),
            'auth' => [
                config('services.clickhouse.username', 'default'),
                config('services.clickhouse.password', ''),
            ],
            'timeout' => 5,
        ]);
    }

    /**
     * Execute a SELECT query and return rows as an array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function select(string $query): array
    {
        try {
            $response = $this->client->post('/', [
                'query' => ['database' => $this->database],
                'body' => $query . ' FORMAT JSONEachRow',
            ]);

            $body = (string) $response->getBody();

            if (empty(trim($body))) {
                return [];
            }

            return array_map(
                fn(string $line) => json_decode($line, true),
                array_filter(explode("\n", trim($body))),
            );
        } catch (GuzzleException $e) {
            throw new RuntimeException('ClickHouse SELECT error: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Execute an INSERT query. Rows must be JSON-encodable arrays matching the table columns.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public function insert(string $table, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $body = implode("\n", array_map(fn($row) => json_encode($row), $rows));

        try {
            $this->client->post('/', [
                'query' => [
                    'database' => $this->database,
                    'query' => "INSERT INTO {$table} FORMAT JSONEachRow",
                ],
                'body' => $body,
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('ClickHouse INSERT error: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Execute a DDL or arbitrary statement (CREATE TABLE, etc.).
     */
    public function statement(string $query): void
    {
        try {
            $this->client->post('/', [
                'query' => ['database' => $this->database],
                'body' => $query,
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('ClickHouse statement error: ' . $e->getMessage(), previous: $e);
        }
    }
}
