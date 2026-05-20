# Stripe — Documentação de Integração

Documentação completa da integração Stripe para pagamentos de aluguel de espaços esportivos, com suporte a Pix, cartão de crédito, repasses automáticos via Stripe Connect e assinaturas mensais para hosts.

---

## Índice

- [Visão Geral](#visão-geral)
- [Arquitetura de Pagamentos](#arquitetura-de-pagamentos)
- [Configuração](#configuração)
- [Modelos de Cobrança](#modelos-de-cobrança)
- [Onboarding de Hosts (Stripe Connect)](#onboarding-de-hosts-stripe-connect)
- [Pagamento de Booking](#pagamento-de-booking)
- [Assinaturas Mensais](#assinaturas-mensais)
- [Webhooks](#webhooks)
- [Exemplos de Uso](#exemplos-de-uso)
- [Testes Locais](#testes-locais)
- [Troubleshooting](#troubleshooting)

---

## Visão Geral

A plataforma usa Stripe como gateway de pagamento principal, com três pilares fundamentais:

| Funcionalidade      | Descrição                                                                           |
| ------------------- | ----------------------------------------------------------------------------------- |
| **Payment Intents** | Pagamentos de bookings via cartão ou Pix (QR code com expiração de 24h)             |
| **Stripe Connect**  | Marketplace com repasses automáticos para hosts; plataforma retém taxa configurável |
| **Subscriptions**   | Assinatura mensal opcional para hosts (modelo alternativo de cobrança)              |

### Fluxo de dinheiro

```
Cliente paga R$100,00 (booking de quadra esportiva)
    │
    ├─ Plataforma retém R$10,00 (10% via application_fee_amount)
    └─ Host recebe R$90,00 (repasse automático via Stripe Connect)
```

**Stripe Connect Express** é usado para simplificar o onboarding de hosts — eles completam um formulário no próprio Stripe (compliance, KYC, dados bancários) e recebem automaticamente os repasses.

---

## Arquitetura de Pagamentos

```
┌─────────────────────────────────────────────────────────────────────┐
│ Frontend (App Cliente)                                              │
└──────────────────────┬──────────────────────────────────────────────┘
                       │ POST /api/booking/{id}/pay
                       │
┌──────────────────────▼──────────────────────────────────────────────┐
│ MarketplaceService::createBookingPayment()                          │
│  ├─ Calcula taxa: R$100 × 10% = R$10                                │
│  ├─ Cria PaymentIntent:                                             │
│  │   ├─ amount: 10000 (centavos)                                    │
│  │   ├─ application_fee_amount: 1000                                │
│  │   ├─ transfer_data.destination: host_stripe_account_id           │
│  │   └─ payment_method_types: ['card', 'pix']                       │
│  └─ Retorna client_secret                                           │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ Frontend (Stripe.js)                                                │
│  └─ Confirma pagamento com client_secret                            │
└──────────────────────┬──────────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│ Stripe processa pagamento                                           │
│  ├─ Cartão: processamento imediato                                  │
│  └─ Pix: gera QR code (expira em 24h)                               │
└──────────────────────┬──────────────────────────────────────────────┘
                       │ Webhook: payment_intent.succeeded
                       │
┌──────────────────────▼──────────────────────────────────────────────┐
│ POST /api/webhook/stripe                                            │
│  └─ ProcessStripeWebhook (fila: payments)                           │
│      └─ Marca booking como pago no banco                            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Configuração

### 1. Credenciais

Todas as credenciais são obtidas no [Stripe Dashboard](https://dashboard.stripe.com/).

| Variável                        | Onde obter                                                                                      | Exemplo            |
| ------------------------------- | ----------------------------------------------------------------------------------------------- | ------------------ |
| `STRIPE_KEY`                    | Dashboard → Developers → API Keys → Publishable key                                             | `pk_test_51ABC...` |
| `STRIPE_SECRET`                 | Dashboard → Developers → API Keys → Secret key                                                  | `sk_test_51ABC...` |
| `STRIPE_WEBHOOK_SECRET`         | Dashboard → Developers → Webhooks → Add endpoint<br>`/api/webhook/stripe` → Signing secret      | `whsec_ABC...`     |
| `STRIPE_CONNECT_WEBHOOK_SECRET` | Dashboard → Connect → Webhooks → Add endpoint<br>`/api/webhook/stripe/connect` → Signing secret | `whsec_XYZ...`     |

### 2. Criar produtos de assinatura

Acesse **Dashboard → Products → Add Product**:

**Plano Básico:**

- Nome: `Host - Plano Básico`
- Preço: R$ 49,90/mês
- Copiar `Price ID` → colar em `STRIPE_PLAN_BASIC_PRICE_ID`

**Plano Premium:**

- Nome: `Host - Plano Premium`
- Preço: R$ 99,90/mês
- Copiar `Price ID` → colar em `STRIPE_PLAN_PREMIUM_PRICE_ID`

### 3. Configurar webhooks

**Webhook principal (eventos da plataforma):**

```
URL: https://sua-api.com/api/webhook/stripe
Eventos:
  - payment_intent.succeeded
  - payment_intent.payment_failed
  - customer.subscription.created
  - customer.subscription.updated
  - customer.subscription.deleted
  - invoice.payment_failed
  - invoice.payment_succeeded
```

**Webhook Connect (eventos dos hosts):**

```
URL: https://sua-api.com/api/webhook/stripe/connect
Eventos:
  - account.updated
```

### 4. Arquivo .env

```env
# Chaves de API
STRIPE_KEY=pk_test_51ABC...
STRIPE_SECRET=sk_test_51ABC...

# Webhooks
STRIPE_WEBHOOK_SECRET=whsec_ABC...
STRIPE_CONNECT_WEBHOOK_SECRET=whsec_XYZ...

# Planos de assinatura
STRIPE_PLAN_BASIC_PRICE_ID=price_1ABC...
STRIPE_PLAN_PREMIUM_PRICE_ID=price_1XYZ...

# Taxas da plataforma
STRIPE_DEFAULT_PLATFORM_FEE_PERCENT=10        # Modelo porcentagem: 10% por booking
STRIPE_SUBSCRIPTION_BOOKING_FEE_PERCENT=0     # Modelo assinatura: 0% por booking
```

---

## Modelos de Cobrança

A plataforma suporta dois modelos de cobrança para hosts:

### Modelo 1: Porcentagem (`billing_model = 'percentage'`)

**Como funciona:**

- Host **não paga** mensalidade
- Plataforma retém **X%** de cada booking (padrão: 10%)
- Taxa é definida em `STRIPE_DEFAULT_PLATFORM_FEE_PERCENT` ou por host em `hr.user.platform_fee_percent`

**Exemplo:**

```
Booking: R$ 200,00
Taxa da plataforma: 10% = R$ 20,00
Host recebe: R$ 180,00
```

**Quando usar:** Para novos hosts ou hosts com baixo volume de reservas.

---

### Modelo 2: Assinatura (`billing_model = 'subscription'`)

**Como funciona:**

- Host **paga mensalidade fixa** (ex: R$ 99,90/mês)
- Taxa por booking é **reduzida ou zero** (padrão: 0%)
- Taxa é definida em `STRIPE_SUBSCRIPTION_BOOKING_FEE_PERCENT`

**Exemplo:**

```
Mensalidade: R$ 99,90
Booking: R$ 200,00
Taxa da plataforma: 0%
Host recebe: R$ 200,00
```

**Quando usar:** Para hosts com alto volume de reservas (mais vantajoso para o host, receita previsível para a plataforma).

---

## Onboarding de Hosts (Stripe Connect)

### Fluxo completo

```
1. Host cria conta na plataforma
   └─ Campo hr.user.stripe_account_id = null

2. Host clica em "Começar a receber pagamentos"
   └─ Backend: StripeService::createConnectAccount()
       └─ Stripe cria Account Express
       └─ Salva hr.user.stripe_account_id

3. Backend gera link de onboarding
   └─ StripeService::createAccountLink($accountId, $refreshUrl, $returnUrl)
       └─ Retorna URL do formulário Stripe

4. Host preenche dados no Stripe
   ├─ Dados pessoais (nome, CPF, endereço)
   ├─ Dados bancários (banco, agência, conta)
   └─ Documentos (RG/CNH se necessário)

5. Stripe valida dados e dispara webhook
   └─ POST /api/webhook/stripe/connect
       └─ Evento: account.updated
           ├─ charges_enabled = true
           ├─ details_submitted = true
           └─ ProcessStripeWebhook atualiza:
               ├─ hr.user.stripe_charges_enabled = true
               └─ hr.user.stripe_onboarding_complete = true

6. Host está pronto para receber repasses ✅
```

### Implementação

```php
// app/Http/Controllers/HostController.php

use App\Services\Payment\StripeService;

public function startOnboarding(Request $request, StripeService $stripe)
{
    $user = $request->user();

    // Criar conta Connect se ainda não existe
    if (!$user->stripe_account_id) {
        $account = $stripe->createConnectAccount(
            email: $user->email,
            country: 'BR'
        );

        $user->update(['stripe_account_id' => $account->id]);
    }

    // Gerar link de onboarding
    $accountLink = $stripe->createAccountLink(
        accountId: $user->stripe_account_id,
        refreshUrl: route('host.onboarding.refresh'),
        returnUrl: route('host.dashboard')
    );

    return response()->json([
        'onboarding_url' => $accountLink->url
    ]);
}
```

### Verificar status do onboarding

```php
public function checkOnboardingStatus(Request $request, StripeService $stripe)
{
    $user = $request->user();

    if (!$user->stripe_account_id) {
        return response()->json(['status' => 'not_started']);
    }

    $account = $stripe->retrieveAccount($user->stripe_account_id);

    return response()->json([
        'status' => $account->charges_enabled ? 'complete' : 'incomplete',
        'charges_enabled' => $account->charges_enabled,
        'details_submitted' => $account->details_submitted,
        'payouts_enabled' => $account->payouts_enabled ?? false
    ]);
}
```

---

## Pagamento de Booking

### Fluxo backend → frontend

**Backend (criar PaymentIntent):**

```php
// app/Http/Controllers/BookingController.php

use App\Services\Payment\MarketplaceService;
use App\Models\Booking;

public function pay(Request $request, Booking $booking, MarketplaceService $marketplace)
{
    $customer = $request->user();
    $host = $booking->space->owner; // Dono do espaço

    // Verificar se host completou onboarding
    if (!$host->stripe_charges_enabled) {
        return response()->json([
            'error' => 'Host ainda não configurou recebimento de pagamentos'
        ], 400);
    }

    // Determinar taxa conforme modelo de cobrança do host
    $feePercent = $marketplace->resolveHostFeePercent(
        billingModel: $host->billing_model ?? 'percentage',
        customFeePercent: $host->platform_fee_percent
    );

    // Criar PaymentIntent
    $paymentIntent = $marketplace->createBookingPayment(
        customer: $customer,
        hostStripeAccountId: $host->stripe_account_id,
        amountCents: $booking->total_cents, // Ex: 20000 = R$ 200,00
        platformFeePercent: $feePercent,
        metadata: [
            'booking_id' => $booking->id,
            'customer_name' => $customer->name,
            'space_name' => $booking->space->name
        ],
        acceptPix: true
    );

    // Salvar payment_intent_id no booking
    $booking->update([
        'stripe_payment_intent_id' => $paymentIntent->id,
        'status' => 'awaiting_payment'
    ]);

    return response()->json([
        'client_secret' => $paymentIntent->client_secret,
        'payment_intent_id' => $paymentIntent->id
    ]);
}
```

**Frontend (confirmar pagamento com Stripe.js):**

```javascript
// Exemplo React Native / React

import { useStripe } from "@stripe/stripe-react-native";

async function confirmPayment(clientSecret, paymentMethod) {
    const { confirmPayment } = useStripe();

    const { error, paymentIntent } = await confirmPayment(clientSecret, {
        paymentMethodType: paymentMethod, // 'Card' ou 'Pix'
    });

    if (error) {
        console.error("Erro no pagamento:", error);
    } else if (paymentIntent.status === "succeeded") {
        console.log("Pagamento confirmado!");
    }
}
```

### Pix: QR Code e expiração

Quando o usuário escolhe Pix:

1. Stripe retorna um QR code em `paymentIntent.next_action.pix_display_qr_code`
2. QR code expira em **24 horas** (configurado em `pix.expires_after_seconds: 86400`)
3. Após pagamento, webhook `payment_intent.succeeded` é disparado

---

## Assinaturas Mensais

### Criar assinatura para um host

```php
use App\Services\Payment\SubscriptionService;

public function subscribe(Request $request, SubscriptionService $subscriptions)
{
    $host = $request->user();

    $validated = $request->validate([
        'plan' => 'required|in:basic,premium'
    ]);

    $priceId = $validated['plan'] === 'basic'
        ? config('services.stripe.plan_basic_price_id')
        : config('services.stripe.plan_premium_price_id');

    // Criar assinatura
    $subscription = $subscriptions->createSubscription($host, $priceId);

    // Atualizar billing_model do host
    $host->update([
        'billing_model' => 'subscription',
        'stripe_subscription_id' => $subscription->id,
        'subscription_status' => $subscription->status // 'incomplete'
    ]);

    // Retornar client_secret para confirmação no frontend
    return response()->json([
        'client_secret' => $subscription->latest_invoice->payment_intent->client_secret,
        'subscription_id' => $subscription->id
    ]);
}
```

### Portal de autogerenciamento

Permite que o host atualize cartão, altere plano ou cancele sem passar pelo backend:

```php
public function billingPortal(Request $request, SubscriptionService $subscriptions)
{
    $host = $request->user();

    $portalSession = $subscriptions->createBillingPortalSession(
        customerId: $host->stripe_customer_id,
        returnUrl: route('host.subscription')
    );

    return response()->json([
        'portal_url' => $portalSession->url
    ]);
}
```

---

## Webhooks

### Eventos processados

| Evento                          | Handler                       | O que faz                                       |
| ------------------------------- | ----------------------------- | ----------------------------------------------- |
| `payment_intent.succeeded`      | `onPaymentSucceeded()`        | Marca booking como pago                         |
| `payment_intent.payment_failed` | `onPaymentFailed()`           | Notifica cliente sobre falha                    |
| `customer.subscription.created` | `syncSubscription()`          | Salva `subscription_id` e `status`              |
| `customer.subscription.updated` | `syncSubscription()`          | Atualiza `status` (active, past_due, canceled)  |
| `customer.subscription.deleted` | `onSubscriptionDeleted()`     | Define `status = 'canceled'`                    |
| `invoice.payment_failed`        | `onInvoicePaymentFailed()`    | Marca `subscription_status = 'past_due'`        |
| `invoice.payment_succeeded`     | `onInvoicePaymentSucceeded()` | Restaura `status = 'active'` se estava past_due |
| `account.updated`               | `onAccountUpdated()`          | Atualiza `stripe_charges_enabled` do host       |

### Estrutura do webhook handler

```php
// app/Jobs/Payment/ProcessStripeWebhook.php

public function handle(): void
{
    match ($this->eventType) {
        'payment_intent.succeeded'      => $this->onPaymentSucceeded(),
        'payment_intent.payment_failed' => $this->onPaymentFailed(),
        'customer.subscription.created' => $this->onSubscriptionCreated(),
        'customer.subscription.updated' => $this->onSubscriptionUpdated(),
        'customer.subscription.deleted' => $this->onSubscriptionDeleted(),
        'invoice.payment_failed'        => $this->onInvoicePaymentFailed(),
        'invoice.payment_succeeded'     => $this->onInvoicePaymentSucceeded(),
        'account.updated'               => $this->onAccountUpdated(),
        default => null,
    };
}
```

### Segurança: verificação de assinatura HMAC

**O webhook NÃO usa autenticação via Bearer token.** A segurança é garantida pela assinatura HMAC no header `Stripe-Signature`:

```php
// app/Http/Controllers/Payment/WebhookController.php

$payload   = $request->getContent();
$sigHeader = $request->header('Stripe-Signature');

try {
    $event = $this->stripeService->constructWebhookEvent(
        $payload,
        $sigHeader,
        config('services.stripe.webhook_secret')
    );
} catch (SignatureVerificationException) {
    return response()->json(['error' => 'Invalid signature'], 401);
}

// Despachar job
ProcessStripeWebhook::dispatch($event->type, $event->data->object->toArray(), false);

return response()->json(['received' => true]);
```

---

## Exemplos de Uso

### Exemplo 1: Booking simples (cartão)

**Cenário:** Cliente aluga quadra por R$ 150,00 (host no modelo porcentagem 10%)

```
POST /api/booking/123/pay
→ PaymentIntent criado: amount=15000, application_fee_amount=1500
→ Frontend confirma com cartão
→ Stripe processa
→ Webhook payment_intent.succeeded
→ Booking marcado como pago
→ Host recebe R$ 135,00 (Stripe repassa automaticamente)
```

### Exemplo 2: Booking com Pix

```
POST /api/booking/456/pay
→ PaymentIntent criado com payment_method_types=['pix']
→ Frontend exibe QR code (expires_after_seconds: 86400)
→ Cliente escaneia e paga no banco
→ Stripe confirma pagamento
→ Webhook payment_intent.succeeded
→ Booking marcado como pago
```

### Exemplo 3: Host com assinatura

**Cenário:** Host paga R$ 99,90/mês e tem taxa reduzida a 0%

```
POST /api/host/subscription (plan=premium)
→ Subscription criada: price_id=price_1ABC, status=incomplete
→ Frontend confirma pagamento da primeira fatura
→ Webhook customer.subscription.created
→ hr.user.subscription_status = 'active'
→ hr.user.billing_model = 'subscription'

Próximo booking do host:
→ MarketplaceService::resolveHostFeePercent() retorna 0%
→ Cliente paga R$ 200 → Host recebe R$ 200 (plataforma já cobrou mensalidade)
```

---

## Testes Locais

### Instalar Stripe CLI

```bash
# macOS
brew install stripe/stripe-cli/stripe

# Linux
wget https://github.com/stripe/stripe-cli/releases/download/v1.19.4/stripe_1.19.4_linux_x86_64.tar.gz
tar -xzf stripe_1.19.4_linux_x86_64.tar.gz
sudo mv stripe /usr/local/bin/

# Autenticar
stripe login
```

### Redirecionar webhooks para localhost

```bash
# Terminal 1: iniciar app
php artisan serve

# Terminal 2: escutar webhooks
stripe listen --forward-to localhost:8000/api/webhook/stripe

# Output:
# > Ready! Your webhook signing secret is whsec_ABC... (add to .env)
```

### Disparar eventos de teste

```bash
# Pagamento bem-sucedido
stripe trigger payment_intent.succeeded

# Pagamento falhou
stripe trigger payment_intent.payment_failed

# Assinatura criada
stripe trigger customer.subscription.created

# Fatura falhou
stripe trigger invoice.payment_failed

# Conta Connect atualizada
stripe trigger account.updated
```

### Logs de webhooks

Verificar processamento:

```bash
# Ver logs do worker
tail -f storage/logs/laravel.log | grep Stripe

# Verificar fila RabbitMQ
curl -u admin:secret http://localhost:15672/api/queues/%2F/payments
```

---

## Troubleshooting

### Problema: Webhook retorna 401 Unauthorized

**Causa:** Assinatura HMAC inválida.

**Solução:**

1. Verificar se `STRIPE_WEBHOOK_SECRET` está preenchido corretamente
2. Em localhost, usar `stripe listen` e copiar o secret exibido
3. Em produção, criar o webhook no Dashboard e copiar o secret

---

### Problema: PaymentIntent falha com "Invalid destination"

**Causa:** Host não completou onboarding ou `stripe_account_id` é inválido.

**Solução:**

```php
// Verificar status do host antes de criar PaymentIntent
$account = $stripe->retrieveAccount($host->stripe_account_id);

if (!$account->charges_enabled) {
    throw new \Exception('Host não completou onboarding');
}
```

---

### Problema: Pix não aparece como opção de pagamento

**Causa:** Pix só funciona com moeda BRL.

**Solução:**

```php
// Garantir que currency seja 'brl'
$paymentIntent = $stripe->paymentIntents->create([
    'amount' => 10000,
    'currency' => 'brl', // ← obrigatório para Pix
    'payment_method_types' => ['card', 'pix']
]);
```

---

### Problema: Assinatura não aparece no Billing Portal

**Causa:** Customer sem assinatura ativa ou `stripe_customer_id` incorreto.

**Solução:**

```php
// Verificar se customer existe
$customer = $stripe->getStripeClient()->customers->retrieve($user->stripe_customer_id);

if (!$customer->subscriptions->data) {
    throw new \Exception('Nenhuma assinatura encontrada');
}
```

---

### Problema: Webhook duplicado (processado 2×)

**Causa:** Stripe retenta webhooks que demoram >30s ou retornam erro.

**Solução:**

```php
// Usar idempotência no handler
use Illuminate\Support\Facades\Cache;

public function handle(): void
{
    $cacheKey = "stripe_event_{$this->eventData['id']}";

    if (Cache::has($cacheKey)) {
        return; // Já processado
    }

    // Processar evento...

    Cache::put($cacheKey, true, now()->addHours(24));
}
```

---

## Referências

- [Stripe API Docs](https://stripe.com/docs/api)
- [Stripe Connect](https://stripe.com/docs/connect)
- [Pix Payment Method](https://stripe.com/docs/payments/pix)
- [Testing Webhooks](https://stripe.com/docs/webhooks/test)
- [Stripe CLI](https://stripe.com/docs/stripe-cli)
