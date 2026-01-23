<?php

namespace App\Support\Traits;

use Illuminate\Support\Facades\DB;

trait CreatesPostgresEnums
{
    protected function createEnum(string $typeName, string $enumClass): void
    {
        $this->dropEnumIfExists($typeName);

        $reflection = new \ReflectionEnum($enumClass);
        $values = collect($reflection->getCases())
            ->map(function ($case) {
                if (method_exists($case, 'getValue')) {
                    $val = $case->getValue();
                    if (is_scalar($val)) {
                        return "'{$val}'";
                    }
                    if (is_object($val) && isset($val->name)) {
                        return "'{$val->name}'";
                    }

                    return "'{$case->getName()}'";
                }

                return "'{$case->getName()}'";
            })
            ->join(', ')
        ;

        DB::statement("CREATE TYPE {$typeName} AS ENUM ({$values})");
    }

    protected function dropEnumIfExists(string $typeName): void
    {
        DB::statement("DO $$
        BEGIN
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname = '{$typeName}') THEN
                DROP TYPE {$typeName};
            END IF;
        END
        $$;");
    }

    protected function addEnumColumn(string $table, string $column, string $type, ?string $default = null): void
    {
        $defaultSql = $default ? "DEFAULT '{$default}'" : '';
        DB::statement("ALTER TABLE {$table} ADD COLUMN {$column} {$type} {$defaultSql}");
    }
}
