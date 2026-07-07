<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system.access_request_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->string('source_id', 64)->index();
            $table->string('ip', 45)->nullable()->index();
            $table->string('client_ip', 45)->nullable()->index();
            $table->string('proxy_ip', 45)->nullable()->index();
            $table->string('cf_ray', 64)->nullable()->index();
            $table->string('forwarded_ip', 45)->nullable();
            $table->string('cf_connecting_ip', 45)->nullable();
            $table->string('method', 10);
            $table->text('full_url')->nullable();
            $table->string('path')->nullable();
            $table->string('host')->nullable();
            $table->string('scheme', 10)->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->text('referer')->nullable();
            $table->string('origin')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('accept_language')->nullable();
            $table->string('route_name')->nullable();
            $table->text('route_action')->nullable();
            $table->json('query_params')->nullable();
            $table->json('payload')->nullable();
            $table->json('headers')->nullable();
            $table->json('cookies')->nullable();
            $table->json('geo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system.access_request_log');
    }
};
