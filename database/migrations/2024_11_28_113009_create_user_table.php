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
        Schema::create('hr.user', function (Blueprint $table) {
            $table->id()->unique()->primary();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('email')->unique();
            $table->string('number')->unique()->nullable();
            $table->string('password');
            $table->string('language', 10)->default('pt-BR');
            $table->boolean('email_two_factor_auth')->default(false);
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('number_two_factor_auth')->default(false);
            $table->boolean('number_verified')->default(false);
            $table->timestamp('number_verified_at')->nullable();
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr.user');
        Schema::dropIfExists('hr.password_reset_token');
    }
};
