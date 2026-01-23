<?php

use App\Models\Transport\Request;
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
        Schema::create('transport.request', function (Blueprint $table) {
            $table->id()->primary();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->foreignId('machine_id')->constrained('transport.machinery')->onDelete('cascade');

            $table->string('origin_place_id');
            $table->string('origin_place_name')->nullable();
            $table->string('origin_latitude')->nullable();
            $table->string('origin_longitude')->nullable();
            $table->string('destination_place_id');
            $table->string('destination_place_name')->nullable();
            $table->string('destination_latitude')->nullable();
            $table->string('destination_longitude')->nullable();

            $table->integer('distance')->nullable()->comment('Distance in meters between origin and destination in meters');
            $table->integer('estimated_time')->nullable()->comment('Estimated travel time in seconds between origin and destination');
            $table->string('estimated_cost')->nullable();
            $table->string('final_cost')->nullable();

            $table->foreignId('payment_id')->nullable(); // ->constrained('payment')->onDelete('cascade');

            $table->timestamp('desired_date')->nullable();
            $table->enum('state', [
                Request::STATE_PENDING,
                Request::STATE_WAITING_FOR_OFFER,
                Request::STATE_PAYMENT_PENDING,
                Request::STATE_APPROVED,
                Request::STATE_REJECTED,
                Request::STATE_IN_PROGRESS,
                Request::STATE_CANCELED,
                Request::STATE_COMPLETED,
            ])->default('pending');
            $table->integer('rate')->nullable();
            $table->text('comment')->nullable();

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
        Schema::dropIfExists('transport.requests');
    }
};
