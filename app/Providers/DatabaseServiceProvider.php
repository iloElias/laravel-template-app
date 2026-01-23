<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blueprint::macro('postgresEnum', function (string $column, string $enumClass) {
            $typeName = strtolower(class_basename($enumClass)) . '_enum';
            $values = array_map(fn ($case) => $case->value, $enumClass::cases());

            $valuesSql = implode("', '", $values);
            DB::statement("DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = '{$typeName}') THEN
                        CREATE TYPE {$typeName} AS ENUM ('{$valuesSql}');
                    END IF; 
                END
            $$;");

            DB::statement('ALTER TABLE ' . $this->table . " ADD COLUMN {$column} {$typeName}");
        });
    }
}
