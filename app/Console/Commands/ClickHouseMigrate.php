<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ClickHouseMigrate extends Command
{
    protected $signature = 'clickhouse:migrate';

    protected $description = 'Run ClickHouse table migrations';

    public function handle(): int
    {
        $protocol = config('services.clickhouse.protocol');
        $host = config('services.clickhouse.host');
        $port = config('services.clickhouse.port');
        $database = config('services.clickhouse.database');
        $username = config('services.clickhouse.username', 'default');
        $password = config('services.clickhouse.password', '');

        $baseUrl = "{$protocol}://{$host}:{$port}";

        $this->info('Running ClickHouse migrations');

        // Encontrar todos os arquivos SQL no diretório clickhouse
        $migrationPath = database_path('clickhouse');

        if (!is_dir($migrationPath)) {
            $this->error("Directory {$migrationPath} does not exist");
            return 1;
        }

        $sqlFiles = glob($migrationPath . '/*.sql');

        if (empty($sqlFiles)) {
            $this->warn('No SQL migration files found in database/clickhouse/');
            return 0;
        }

        foreach ($sqlFiles as $file) {
            $filename = basename($file);
            $this->line("  → Executing {$filename}...");

            $sql = file_get_contents($file);

            if (empty($sql)) {
                $this->error("  ✗ {$filename} is empty");
                continue;
            }
            try {
                $response = Http::timeout(30)
                    ->withBasicAuth($username, $password)
                    ->post("{$baseUrl}/?database={$database}", $sql);

                if ($response->successful()) {
                    $this->info("  ✓ {$filename} executed successfully");
                } else {
                    $this->error("  ✗ Failed to execute {$filename}");
                    $this->error("  Response: {$response->body()}");
                    return 1;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error executing {$filename}: {$e->getMessage()}");
                return 1;
            }
        }

        $this->newLine();
        $this->info('✓ All ClickHouse migrations executed successfully');

        return 0;
    }
}