<?php

namespace App\Services\Payment;

use App\Models\Hr\User;
use Stripe\PaymentIntent;

class MarketplaceService
{
    public function __construct(private readonly StripeService $stripe)
    {
    }

    /**
     * Cria um PaymentIntent de aluguel de espaço esportivo.
     *
     * O cliente paga o valor total; a plataforma retém a taxa; o host recebe o restante.
     * O host precisa ter completado o onboarding Stripe Connect (stripe_account_id preenchido).
     *
     * Modelos de cobrança suportados:
     *   - 'percentage': $platformFeePercent % sobre cada aluguel (ex: 10%)
     *   - 'subscription': host paga assinatura mensal — aqui a taxa pode ser 0%
     *
     * @param array<string, mixed> $metadata Dados extras anexados ao PaymentIntent
     *
     * @throws \InvalidArgumentException se o host não tiver conta Connect ativa
     */
    public function createBookingPayment(
        User $customer,
        string $hostStripeAccountId,
        int $amountCents,
        float $platformFeePercent,
        array $metadata = [],
        bool $acceptPix = true,
    ): PaymentIntent {
        if (empty($hostStripeAccountId)) {
            throw new \InvalidArgumentException('Host não tem conta Stripe Connect configurada.');
        }

        $customerId = $customer->stripe_customer_id
            ?? $this->stripe->createCustomer($customer)->id;

        $platformFee = $this->calculatePlatformFee($amountCents, $platformFeePercent);

        return $this->stripe->createMarketplacePaymentIntent(
            amountCents: $amountCents,
            customerId: $customerId,
            connectedAccountId: $hostStripeAccountId,
            platformFeeAmountCents: $platformFee,
            metadata: array_merge($metadata, [
                'customer_id' => $customer->id,
                'platform_fee_cents' => $platformFee,
                'fee_percent' => $platformFeePercent,
            ]),
            acceptPix: $acceptPix,
        );
    }

    /**
     * Calcula a taxa da plataforma em centavos.
     *
     * Exemplo: R$200,00 (20000 cents) a 10% → R$20,00 (2000 cents)
     */
    public function calculatePlatformFee(int $amountCents, float $feePercent): int
    {
        return (int) round($amountCents * ($feePercent / 100));
    }

    /**
     * Determina a taxa aplicável conforme o modelo de cobrança do host.
     *
     * - 'subscription': host paga mensalidade, taxa = STRIPE_SUBSCRIPTION_BOOKING_FEE_PERCENT (baixa ou zero)
     * - 'percentage':   host paga por transação, taxa = STRIPE_DEFAULT_PLATFORM_FEE_PERCENT
     */
    public function resolveHostFeePercent(string $billingModel, ?float $customFeePercent = null): float
    {
        if ($billingModel === 'subscription') {
            return (float) config('services.stripe.subscription_booking_fee_percent', 0);
        }

        return $customFeePercent
            ?? (float) config('services.stripe.default_platform_fee_percent', 10);
    }
}
