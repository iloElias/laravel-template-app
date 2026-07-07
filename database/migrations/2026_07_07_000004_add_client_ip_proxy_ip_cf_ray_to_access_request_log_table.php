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
        if (!Schema::hasTable('system.access_request_log')) {
            return;
        }

        Schema::table('system.access_request_log', function (Blueprint $table) {
            if (!Schema::hasColumn('system.access_request_log', 'client_ip')) {
                $table->string('client_ip', 45)->nullable()->index();
            }

            if (!Schema::hasColumn('system.access_request_log', 'proxy_ip')) {
                $table->string('proxy_ip', 45)->nullable()->index();
            }

            if (!Schema::hasColumn('system.access_request_log', 'cf_ray')) {
                $table->string('cf_ray', 64)->nullable()->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('system.access_request_log')) {
            return;
        }

        Schema::table('system.access_request_log', function (Blueprint $table) {
            if (Schema::hasColumn('system.access_request_log', 'cf_ray')) {
                $table->dropColumn('cf_ray');
            }

            if (Schema::hasColumn('system.access_request_log', 'proxy_ip')) {
                $table->dropColumn('proxy_ip');
            }

            if (Schema::hasColumn('system.access_request_log', 'client_ip')) {
                $table->dropColumn('client_ip');
            }
        });
    }
};
