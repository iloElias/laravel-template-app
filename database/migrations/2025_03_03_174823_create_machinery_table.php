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
        Schema::create('transport.machinery', function (Blueprint $table) {
            $table->id()->primary();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('name');
            $table->string('model');
            $table->string('plate')->nullable();
            $table->string('type');
            $table->string('manufacturer');
            $table->date('manufacturer_date');
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->unsignedTinyInteger('axles')->nullable();
            $table->string('tire_config')->nullable();
            $table->text('obs')->nullable();
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
        Schema::dropIfExists('transport.machinery');
    }
};
