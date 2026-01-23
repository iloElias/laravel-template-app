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
        Schema::create('hr.document', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->date('emission_date')->nullable();
            $table->foreignId('document_type')->constrained('hr.document_type')->onDelete('cascade');
            $table->string('number')->unique();
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
        Schema::dropIfExists('document');
    }
};
