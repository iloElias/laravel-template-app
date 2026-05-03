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
        Schema::create('hr.device_agent', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint')->unique();
            $table->string('user_agent');
            $table->string('ip_address');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr.session', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('device_agent_id')->constrained('hr.device_agent')->nullable();
            $table->foreignId('auth_code_id')->nullable()->constrained('hr.auth_code')->onDelete('cascade');
            $table->boolean('authenticated')->default(false);
            $table->timestamp('last_activity')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->longText('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr.remember_device', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->foreignId('device_agent_id')->constrained('hr.device_agent')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr.request_history', function (Blueprint $table) {
            $table->id();
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
        Schema::dropIfExists('hr.device_agent');
        Schema::dropIfExists('hr.session');
        Schema::dropIfExists('hr.remember_device');
        Schema::dropIfExists('hr.request_history');
    }
};
