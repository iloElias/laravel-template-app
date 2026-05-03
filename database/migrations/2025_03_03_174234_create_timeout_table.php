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
        Schema::create('hr.timeout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('reason')->nullable();
            $table->timestamp('timeout_start');
            $table->timestamp('timeout_end')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.timeout');
    }
};
