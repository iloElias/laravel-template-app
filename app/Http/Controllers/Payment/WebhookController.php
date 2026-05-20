<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Jobs\Payment\ProcessStripeWebhook;
use App\Services\Payment\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function __construct(private readonly StripeService $stripeService)
    {
    }

    /**
     * Processa webhooks da conta principal da plataforma.
     * Eventos: assinaturas, pagamentos diretos.
     *
     * Configurar no Stripe Dashboard:
     *   Endpoint: POST /webhook/stripe
     *   Secret:   STRIPE_WEBHOOK_SECRET
     */
    public function handlePlatform(Request $request): JsonResponse
    {
        return $this->processWebhook(
            request: $request,
            secret: config('services.stripe.webhook_secret'),
            isConnect: false,
        );
    }

    /**
     * Processa webhooks das contas Connect (hosts).
     * Eventos: account.updated (onboarding), payment_intent de bookings.
     *
     * Configurar no Stripe Dashboard → Connect → Webhooks:
     *   Endpoint: POST /webhook/stripe/connect
     *   Secret:   STRIPE_CONNECT_WEBHOOK_SECRET
     */
    public function handleConnect(Request $request): JsonResponse
    {
        return $this->processWebhook(
            request: $request,
            secret: config('services.stripe.connect_webhook_secret'),
            isConnect: true,
        );
    }

    private function processWebhook(Request $request, string $secret, bool $isConnect): JsonResponse
    {
        // O body deve ser lido como raw string para verificação de assinatura
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        if (!$sigHeader) {
            return response()->json(['error' => 'Missing Stripe-Signature header'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException) {
            // Assinatura inválida — possível requisição forjada
            return response()->json(['error' => 'Invalid webhook signature'], Response::HTTP_UNAUTHORIZED);
        }

        // Despacha processamento assíncrono — retorna 200 imediatamente para o Stripe
        // (Stripe considera timeout qualquer resposta > 30s e retenta o webhook)
        ProcessStripeWebhook::dispatch(
            $event->type,
            $event->data->object->toArray(),
            $isConnect,
        );

        return response()->json(['received' => true]);
    }
}
