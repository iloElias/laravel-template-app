<?php

namespace App\Services\Transport;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Models\Hr\User;
use App\Models\Transport\Carrier;
use App\Models\Transport\Offer;
use App\Models\Transport\Request as TransportRequest;
use App\Services\Chat\ChatService;
use App\Services\MercadoPago\PaymentService;
use Illuminate\Support\Str;

class OfferService
{
    public function __construct(
        protected PaymentService $paymentService,
        protected ChatService $chatService,
    ) {
    }

    public function makeOffer(StoreOfferRequest $request)
    {
        $transporter = User::auth();

        $validated = $request->validated();

        $transportRequest = TransportRequest::where('uuid', $validated['request_uuid'])
            ->where('state', TransportRequest::STATE_WAITING_FOR_OFFER)
            ->first()
        ;

        $carrier = Carrier::where('uuid', $validated['carrier_uuid'])
            ->where('user_id', $transporter->id)
            ->firstOrFail()
        ;

        $this->validateOffer($transporter, $transportRequest, $carrier);

        $requestant = User::find($transportRequest->user_id);

        $offer = Offer::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $transporter->id,
            'request_id' => $transportRequest->id,
            'carrier_id' => $carrier->id,
            'price' => $validated['price'],
            'state' => Offer::STATE_PENDING,
            'active' => true,
        ]);

        return [
            'transporter' => $transporter,
            'requestant' => $requestant,
            'validated' => $validated,
            'transportRequest' => $transportRequest,
            'carrier' => $carrier,
            'offer' => $offer,
        ];
    }

    public static function getPayment($paymentId): void
    {

    }


    public function validateOfferAcception(string $uuid)
    {
        $user = User::auth();
        $offer = Offer::where('uuid', $uuid)
            ->where('active', true)
            ->first()
        ;
        if (!$offer) {
            throw new InvalidRequestException('Offer not found', [], 404);
        }

        $transportRequest = TransportRequest::where('id', $offer->request_id)
            ->where('user_id', $user->id)
            ->where('active', true)
            ->first()
        ;
        if (!$transportRequest) {
            throw new InvalidRequestException('Transport request not found', [], 404);
        }
        if ($transportRequest->state !== TransportRequest::STATE_WAITING_FOR_OFFER) {
            throw new InvalidRequestException('Transport request is not in a valid state to accept an offer');
        }

        $requestant = User::find($transportRequest->user_id);
        $transporter = User::find($offer->user_id);

        return [
            'offer' => $offer,
            'transportRequest' => $transportRequest,
            'requestant' => $requestant,
            'transporter' => $transporter,
        ];
    }

    public function acceptOffer(Offer $offer, TransportRequest $transportRequest, User $requestant, User $user, ?string $message = null)
    {
        $pixPayment = $this->paymentService->makePayment(
            $offer->price,
            $requestant,
        );

        $offer->update([
            'state' => Offer::STATE_APPROVED,
        ]);

        $transportRequest->update([
            'final_cost' => $offer->price,
            'payment_id' => $pixPayment->id,
            'state' => TransportRequest::STATE_PAYMENT_PENDING,
        ]);

        $chat = $this->chatService->createPrivateChat(
            $user->id,
            $requestant->id,
            $message,
        );

        Offer::where('request_id', $transportRequest->id)
            ->where('state', Offer::STATE_PENDING)
            ->where('id', '!=', $offer->id)
            ->update([
                'state' => Offer::STATE_REJECTED,
                'active' => false,
            ])
        ;

        return [
            'offer' => $offer,
            'transportRequest' => $transportRequest,
            'requestant' => $requestant,
            'transporter' => $user,
            'chat' => $chat,
        ];
    }

    public function getHasMinimumPrice(Offer $offer, TransportRequest $transportRequest)
    {
        return floatval($transportRequest->estimated_cost) === floatval($offer->price);
    }

    protected function validateOffer(User $transporter, TransportRequest $transportRequest, Carrier $carrier)
    {
        $hasCanceledOfferIn30Days = $this->hasCanceledOfferIn30Days($transporter);
        if ($hasCanceledOfferIn30Days) {
            throw new InvalidFormException('Você não pode criar uma nova oferta', [
                'request_uuid' => ['Você cancelou uma oferta nos últimos 30 dias e não pode criar uma nova oferta neste período.'],
            ]);
        }
        if (!$transportRequest) {
            throw new InvalidFormException('Chamado para transporte não encontrado', [
                'request_uuid' => ['O chamado para transporte especificado não existe.'],
            ]);
        }
        $hasAlreadyOffer = $this->hasAlreadyOffer($transporter, $transportRequest);
        if ($hasAlreadyOffer) {
            throw new InvalidFormException('Você já possui uma oferta para este chamado', [
                'request_uuid' => ['Você já possui uma oferta ativa para este chamado de transporte.'],
            ]);
        }
        if (!$carrier->active) {
            throw new InvalidFormException('Transportadora não encontrada', [
                'carrier_uuid' => ['A transportadora especificada não existe.'],
            ]);
        }
        if ($transportRequest->state === TransportRequest::STATE_PAYMENT_PENDING) {
            throw new InvalidFormException('Não é possível criar uma oferta para um chamado com pagamento pendente', [
                'request_uuid' => ['Não é possível criar uma oferta para um chamado com pagamento pendente.'],
            ]);
        }
    }

    protected function hasCanceledOfferIn30Days(User $transporter)
    {
        return Offer::where('user_id', $transporter->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->where('state', Offer::STATE_CANCELED)
            ->orderByDesc('created_at')
            ->exists()
        ;
    }

    protected function hasAlreadyOffer(User $transporter, TransportRequest $transportRequest)
    {
        return Offer::where('user_id', $transporter->id)
            ->where('request_id', $transportRequest->id)
            ->whereIn('state', [Offer::STATE_PENDING, Offer::STATE_APPROVED])
            ->exists()
        ;
    }
}
