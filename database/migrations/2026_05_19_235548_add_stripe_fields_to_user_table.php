<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $connection = 'pgsql_direct';

    public function up(): void
    {
        Schema::table('hr.user', function (Blueprint $table) {
            // ── Customer (quem aluga) ─────────────────────────────────────────
            // ID do Customer criado no Stripe — usado em PaymentIntents e assinaturas
            $table->string('stripe_customer_id')->nullable()->unique()->after('remember_token');

            // ── Host (quem disponibiliza o espaço) ───────────────────────────
            // ID da conta Express Stripe Connect do host
            $table->string('stripe_account_id')->nullable()->unique()->after('stripe_customer_id');
            // true quando o host completou o onboarding e pode receber repasses
            $table->boolean('stripe_charges_enabled')->default(false)->after('stripe_account_id');
            $table->boolean('stripe_onboarding_complete')->default(false)->after('stripe_charges_enabled');

            // ── Modelo de cobrança do host ────────────────────────────────────
            // 'subscription' → paga mensalidade fixa, taxa por booking é mínima/zero
            // 'percentage'   → paga percentual sobre cada aluguel realizado
            $table->enum('billing_model', ['subscription', 'percentage'])->nullable()->after('stripe_onboarding_complete');
            // Taxa percentual customizada (sobrescreve o padrão em config/services.stripe)
            $table->decimal('platform_fee_percent', 5, 2)->nullable()->after('billing_model');

            // ── Assinatura do host ────────────────────────────────────────────
            $table->string('stripe_subscription_id')->nullable()->after('platform_fee_percent');
            // Status reflete o estado no Stripe: active, trialing, past_due, canceled, etc.
            $table->string('subscription_status')->nullable()->after('stripe_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('hr.user', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_account_id',
                'stripe_charges_enabled',
                'stripe_onboarding_complete',
                'billing_model',
                'platform_fee_percent',
                'stripe_subscription_id',
                'subscription_status',
            ]);
        });
    }
};