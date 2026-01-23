<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('system.error_logs', function (Blueprint $table) {
            $table->id()->primary();
            $table->text('json');
            $table->text('params');
            $table->timestamp('created_in')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system.error_logs');
    }
};
