<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr.suspicious_browser_agent', function (Blueprint $table) {
            $table->id()->primary();
            $table->foreignId('browser_agent_id')->constrained('hr.browser_agent');
            $table->string('reason');
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });

        Schema::create('hr.banned_browser_agent', function (Blueprint $table) {
            $table->id()->primary();
            $table->foreignId('browser_agent_id')->constrained('hr.browser_agent');
            $table->string('reason');
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.suspicious_browser_agent');
        Schema::dropIfExists('hr.banned_browser_agent');
    }
};
