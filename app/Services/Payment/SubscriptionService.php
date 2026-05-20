<?php

namespace App\Services\Payment;

use App\Models\Hr\User;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Subscription;

class SubscriptionService
{
    public function __construct(private readonly StripeService $stripe)
    {
    }

    /**
     * Cria uma assinatura mensal para um host.
     *
     * O $priceId é o ID de preço recorrente criado no Stripe Dashboard.
     * Use STRIPE_PLAN_BASIC_PRICE_ID ou STRIPE_PLAN_PREMIUM_PRICE_ID do .env.
     *
     * Retorna a Subscription com latest_invoice.payment_intent expandido,
     * contendo o client_secret necessário para confirmar o pagamento no frontend.
     */
    public function createSubscription(User $host, string $priceId): Subscription
    {
        $customerId = $host->stripe_customer_id
            ?? $this->stripe->createCustomer($host)->id;

        return $this->stripe->getStripeClient()->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => $priceId]],
            // default_incomplete: aguarda confirmação do pagamento antes de ativar
            'payment_behavior' => 'default_incomplete',
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => [
                'user_id' => $host->id,
                'user_uuid' => $host->uuid,
            ],
        ]);
    }

    /**
     * Cancela uma assinatura imediatamente.
     */
    public function cancelSubscription(string $subscriptionId): Subscription
    {
        return $this->stripe->getStripeClient()->subscriptions->cancel($subscriptionId);
    }

    /**
     * Gera um link para o Stripe Billing Portal, onde o host pode gerenciar
     * seu plano, atualizar cartão ou cancelar a assinatura.
     */
    public function createBillingPortalSession(string $customerId, string $returnUrl): PortalSession
    {
        return $this->stripe->getStripeClient()->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    /**
     * Lista todos os planos (preços recorrentes ativos) disponíveis no Stripe.
     * Use para exibir opções de plano para os hosts.
     *
     * @return array<int, \Stripe\Price>
     */
    public function getPlans(): array
    {
        $prices = $this->stripe->getStripeClient()->prices->all([
            'active' => true,
            'type' => 'recurring',
            'expand' => ['data.product'],
            'limit' => 10,
        ]);

        return $prices->data;
    }
}
