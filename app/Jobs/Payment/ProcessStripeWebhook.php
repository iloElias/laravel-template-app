<?php

namespace App\Jobs\Payment;

use App\Models\Hr\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessStripeWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * Retenta até 5 vezes com intervalo exponencial de 30s.
     */
    public int $tries = 5;

    public int $backoff = 30;

    /**
     * @param array<string, mixed> $eventData Dados do objeto do evento (event.data.object)
     */
    public function __construct(
        private readonly string $eventType,
        private readonly array $eventData,
        private readonly bool $isConnect,
    ) {
        $this->onQueue('payments');
    }

    public function handle(): void
    {
        match ($this->eventType) {
            // ── Pagamentos de bookings ──────────────────────────────────────────
            'payment_intent.succeeded' => $this->onPaymentSucceeded(),
            'payment_intent.payment_failed' => $this->onPaymentFailed(),

            // ── Ciclo de vida de assinaturas dos hosts ──────────────────────────
            'customer.subscription.created' => $this->onSubscriptionCreated(),
            'customer.subscription.updated' => $this->onSubscriptionUpdated(),
            'customer.subscription.deleted' => $this->onSubscriptionDeleted(),
            'invoice.payment_failed' => $this->onInvoicePaymentFailed(),
            'invoice.payment_succeeded' => $this->onInvoicePaymentSucceeded(),

            // ── Onboarding de hosts (Stripe Connect) ────────────────────────────
            'account.updated' => $this->onAccountUpdated(),

            // Eventos não mapeados são ignorados silenciosamente
            default => null,
        };
    }

    // ─── Pagamentos ──────────────────────────────────────────────────────────────

    private function onPaymentSucceeded(): void
    {
        $paymentIntentId = $this->eventData['id'];
        $metadata = $this->eventData['metadata'] ?? [];

        Log::info('Stripe: payment_intent.succeeded', [
            'payment_intent_id' => $paymentIntentId,
            'metadata' => $metadata,
        ]);

        // TODO: marcar booking como pago
        // Booking::where('stripe_payment_intent_id', $paymentIntentId)
        //     ->update(['status' => 'paid', 'paid_at' => now()]);
    }

    private function onPaymentFailed(): void
    {
        $paymentIntentId = $this->eventData['id'];
        $metadata = $this->eventData['metadata'] ?? [];
        $lastError = $this->eventData['last_payment_error'] ?? null;

        Log::warning('Stripe: payment_intent.payment_failed', [
            'payment_intent_id' => $paymentIntentId,
            'error' => $lastError['message'] ?? 'unknown',
            'metadata' => $metadata,
        ]);

        // TODO: notificar cliente sobre falha e liberar o slot do booking
        // Booking::where('stripe_payment_intent_id', $paymentIntentId)
        //     ->update(['status' => 'payment_failed']);
    }

    // ─── Assinaturas ─────────────────────────────────────────────────────────────

    private function onSubscriptionCreated(): void
    {
        $this->syncSubscription();
    }

    private function onSubscriptionUpdated(): void
    {
        $this->syncSubscription();
    }

    private function onSubscriptionDeleted(): void
    {
        $stripeCustomerId = $this->eventData['customer'];

        $user = User::where('stripe_customer_id', $stripeCustomerId)->first();

        if ($user) {
            $user->update([
                'stripe_subscription_id' => null,
                'subscription_status' => 'canceled',
            ]);
        }
    }

    private function onInvoicePaymentFailed(): void
    {
        $stripeCustomerId = $this->eventData['customer'];

        Log::warning('Stripe: invoice.payment_failed', [
            'customer_id' => $stripeCustomerId,
        ]);

        $user = User::where('stripe_customer_id', $stripeCustomerId)->first();

        if ($user) {
            // Marca assinatura como inadimplente
            $user->update(['subscription_status' => 'past_due']);

            // TODO: enviar email de cobrança ao host
            // SendMail::dispatch($user->email, SubscriptionPastDueMail::class, [...]);
        }
    }

    private function onInvoicePaymentSucceeded(): void
    {
        $stripeCustomerId = $this->eventData['customer'];

        $user = User::where('stripe_customer_id', $stripeCustomerId)->first();

        if ($user && $user->subscription_status === 'past_due') {
            $user->update(['subscription_status' => 'active']);
        }
    }

    private function syncSubscription(): void
    {
        $stripeCustomerId = $this->eventData['customer'];
        $subscriptionId = $this->eventData['id'];
        $status = $this->eventData['status'];

        $user = User::where('stripe_customer_id', $stripeCustomerId)->first();

        if ($user) {
            $user->update([
                'stripe_subscription_id' => $subscriptionId,
                'subscription_status' => $status,
            ]);
        }
    }

    // ─── Connect (onboarding) ────────────────────────────────────────────────────

    private function onAccountUpdated(): void
    {
        if (!$this->isConnect) {
            return;
        }

        $accountId = $this->eventData['id'];
        $chargesEnabled = $this->eventData['charges_enabled'] ?? false;
        $detailsSubmitted = $this->eventData['details_submitted'] ?? false;

        Log::info('Stripe Connect: account.updated', [
            'account_id' => $accountId,
            'charges_enabled' => $chargesEnabled,
            'details_submitted' => $detailsSubmitted,
        ]);

        // TODO: atualizar status de onboarding do host
        // User::where('stripe_account_id', $accountId)->update([
        //     'stripe_charges_enabled'       => $chargesEnabled,
        //     'stripe_onboarding_complete'   => $detailsSubmitted,
        // ]);
    }
}
