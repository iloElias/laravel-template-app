<?php

namespace App\Services\Payment;

use App\Models\Hr\User;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    // ─── Customer ────────────────────────────────────────────────────────────────

    /**
     * Cria um Customer no Stripe e salva o ID no usuário.
     */
    public function createCustomer(User $user): Customer
    {
        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => trim($user->name . ' ' . $user->surname),
            'metadata' => [
                'user_id' => $user->id,
                'user_uuid' => $user->uuid,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Retorna o Customer existente ou cria um novo.
     */
    public function getOrCreateCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            return $this->stripe->customers->retrieve($user->stripe_customer_id);
        }

        return $this->createCustomer($user);
    }

    // ─── Payment Intent (cartão + Pix) ───────────────────────────────────────────

    /**
     * Cria um PaymentIntent simples (sem marketplace) para cartão e/ou Pix.
     * O valor ($amountCents) deve estar em centavos de BRL (ex: R$100,00 = 10000).
     *
     * @param array<string, mixed> $metadata
     */
    public function createPaymentIntent(
        int $amountCents,
        string $customerId,
        array $metadata = [],
        bool $acceptPix = true,
    ): PaymentIntent {
        $paymentMethods = ['card'];
        if ($acceptPix) {
            $paymentMethods[] = 'pix';
        }

        return $this->stripe->paymentIntents->create([
            'amount' => $amountCents,
            'currency' => 'brl',
            'customer' => $customerId,
            'payment_method_types' => $paymentMethods,
            'metadata' => $metadata,
            'payment_method_options' => $acceptPix ? [
                // Pix expira em 24 horas (máximo permitido pelo Banco Central)
                'pix' => ['expires_after_seconds' => 86400],
            ] : [],
        ]);
    }

    /**
     * Cria um PaymentIntent para marketplace (Stripe Connect — destination charge).
     *
     * O cliente é cobrado no valor total; o host recebe (valor - taxa_plataforma);
     * a plataforma recebe a taxa automaticamente.
     *
     * @param array<string, mixed> $metadata
     */
    public function createMarketplacePaymentIntent(
        int $amountCents,
        string $customerId,
        string $connectedAccountId,
        int $platformFeeAmountCents,
        array $metadata = [],
        bool $acceptPix = true,
    ): PaymentIntent {
        $paymentMethods = ['card'];
        if ($acceptPix) {
            $paymentMethods[] = 'pix';
        }

        return $this->stripe->paymentIntents->create([
            'amount' => $amountCents,
            'currency' => 'brl',
            'customer' => $customerId,
            'payment_method_types' => $paymentMethods,
            // Taxa que fica para a plataforma antes de repassar ao host
            'application_fee_amount' => $platformFeeAmountCents,
            'transfer_data' => [
                'destination' => $connectedAccountId,
            ],
            'metadata' => $metadata,
            'payment_method_options' => $acceptPix ? [
                'pix' => ['expires_after_seconds' => 86400],
            ] : [],
        ]);
    }

    // ─── Stripe Connect (onboarding de Hosts) ────────────────────────────────────

    /**
     * Cria uma conta Express no Stripe Connect para um host.
     * O host poderá receber repasses após completar o onboarding.
     */
    public function createConnectAccount(string $email, string $country = 'BR'): Account
    {
        return $this->stripe->accounts->create([
            'type' => 'express',
            'country' => $country,
            'email' => $email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'settings' => [
                'payouts' => [
                    'schedule' => [
                        // Repasse toda sexta-feira
                        'interval' => 'weekly',
                        'weekly_anchor' => 'friday',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Gera o link de onboarding Express para o host completar cadastro no Stripe.
     */
    public function createAccountLink(string $accountId, string $refreshUrl, string $returnUrl): AccountLink
    {
        return $this->stripe->accountLinks->create([
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    /**
     * Recupera dados da conta Connect de um host.
     */
    public function retrieveAccount(string $accountId): Account
    {
        return $this->stripe->accounts->retrieve($accountId);
    }

    // ─── Webhook ─────────────────────────────────────────────────────────────────

    /**
     * Verifica a assinatura do webhook e retorna o evento.
     * Lança SignatureVerificationException se a assinatura for inválida.
     *
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, string $sigHeader, string $secret): Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $secret);
    }

    public function getStripeClient(): StripeClient
    {
        return $this->stripe;
    }
}
