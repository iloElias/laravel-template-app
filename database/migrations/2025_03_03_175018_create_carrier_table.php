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
        Schema::create('transport.carrier', function (Blueprint $table) {
            $table->id()->primary();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->string('name');
            $table->string('plate')->unique();
            $table->string('model');
            $table->date('manufacturer_date')->after('manufacturer');
            $table->string('renavam');
            $table->string('chassi');
            $table->string('manufacturer');
            $table->string('licensing_uf', 2);
            $table->string('vehicle_type');
            $table->string('body_type');
            $table->decimal('plank_length', 8, 2);
            $table->decimal('tare', 8, 2);
            $table->decimal('pbtc', 8, 2);
            $table->unsignedTinyInteger('axles');
            $table->unsignedTinyInteger('tires_per_axle');
            $table->string('traction');
            $table->string('rntrc');
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
        Schema::dropIfExists('transport.carrier');
    }
};
