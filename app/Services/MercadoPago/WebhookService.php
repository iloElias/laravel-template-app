<?php

namespace App\Services\MercadoPago;

use App\Exception\InvalidRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function validateSignature(Request $request)
    {
        $rawBody = $request->getContent();
        $signatureHeader = $request->header('X-Hub-Signature') ?? $request->header('x-hub-signature') ?? $request->header('X-Meli-Signature') ?? null;
        $webhookSecret = config('services.mercadopago.webhook_secret') ?? env('MP_WEBHOOK_SECRET');

        if ($signatureHeader && $webhookSecret) {
            $expectedSha1 = hash_hmac('sha1', $rawBody, $webhookSecret);
            $expectedHeader = "sha1={$expectedSha1}";

            if (!hash_equals($expectedHeader, $signatureHeader) && !hash_equals($expectedSha1, $signatureHeader)) {
                Log::warning('[MP] Webhook signature inválida', [
                    'provided' => $signatureHeader,
                    'expected' => $expectedHeader,
                ]);

                throw new InvalidRequestException('Invalid webhook signature', [], 401);
            }
        }
    }

    public function processWebhook($payload)
    {
        // Implement webhook processing logic
    }
}
