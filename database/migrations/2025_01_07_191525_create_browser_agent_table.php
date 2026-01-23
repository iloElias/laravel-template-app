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
        Schema::create('hr.browser_agent', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('fingerprint')->unique();
            $table->string('user_agent');
            $table->string('ip_address');
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });

        Schema::create('hr.session', function (Blueprint $table) {
            $table->id()->primary();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('browser_agent_id')->constrained('hr.browser_agent')->nullable();
            $table->foreignId('auth_code_id')->nullable()->constrained('hr.auth_code')->onDelete('cascade');
            $table->boolean('authenticated')->default(false);
            $table->timestamp('last_activity')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->longText('payload')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });

        Schema::create('hr.remember_browser', function (Blueprint $table) {
            $table->id()->primary();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->foreignId('browser_agent_id')->constrained('hr.browser_agent')->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inactivated_at')->nullable();
        });

        Schema::create('hr.request_history', function (Blueprint $table) {
            $table->id()->primary();
            $table->foreignId('session_id')->constrained('hr.session')->onDelete('cascade');
            $table->string('route');
            $table->string('method');
            $table->text('payload')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.browser_agent');
        Schema::dropIfExists('hr.session');
        Schema::dropIfExists('hr.remember_browser');
        Schema::dropIfExists('hr.request_history');
    }
};
