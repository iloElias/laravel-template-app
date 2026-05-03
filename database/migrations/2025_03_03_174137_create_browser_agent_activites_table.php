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
        Schema::create('hr.suspicious_device_agent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_agent_id')->constrained('hr.device_agent');
            $table->string('reason');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr.banned_device_agent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_agent_id')->constrained('hr.device_agent');
            $table->string('reason');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.suspicious_device_agent');
        Schema::dropIfExists('hr.banned_device_agent');
    }
};
