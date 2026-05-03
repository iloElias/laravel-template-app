<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("SET TIME ZONE 'America/Sao_Paulo'");

        DB::statement('CREATE SCHEMA IF NOT EXISTS hr');
        DB::statement('CREATE SCHEMA IF NOT EXISTS system');
        DB::statement('CREATE SCHEMA IF NOT EXISTS file');
        DB::statement('CREATE SCHEMA IF NOT EXISTS framework');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("SET TIME ZONE 'UTC'");

        DB::statement('DROP SCHEMA IF EXISTS hr CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS system CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS file CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS framework CASCADE');
    }
};
