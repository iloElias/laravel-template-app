<?php

use App\Models\Transport\Offer;
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
        Schema::create('transport.offer', function (Blueprint $table) {
            $table->id()->primary();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('hr.user')->onDelete('cascade');
            $table->foreignId('request_id')->constrained('transport.request')->onDelete('cascade');
            $table->foreignId('carrier_id')->constrained('transport.carrier')->onDelete('cascade');

            $table->decimal('price', 10, 2);
            $table->decimal('gain', 10, 2)->nullable();
            $table->enum('state', [
                Offer::STATE_PENDING,
                Offer::STATE_WAITING_FOR_OFFER,
                Offer::STATE_PAYMENT_PENDING,
                Offer::STATE_APPROVED,
                Offer::STATE_REJECTED,
                Offer::STATE_IN_PROGRESS,
                Offer::STATE_CANCELED,
                Offer::STATE_COMPLETED,
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
        Schema::dropIfExists('transport.offers');
    }
};
