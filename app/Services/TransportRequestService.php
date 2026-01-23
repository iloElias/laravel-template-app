<?php

// app/Services/TransportRequestService.php

namespace App\Services;

use App\Models\System\ErrorLog;
use App\Models\Transport\Request;
use App\Services\Google\Contracts\DistanceMatrixClientInterface;
use App\Services\Google\Contracts\PlacesClientInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class TransportRequestService
{
    public function __construct(
        protected PlacesClientInterface $placesClient,
        protected DistanceMatrixClientInterface $distanceClient,
    ) {
    }

    public function validateTransportRequest(int $requestId): void
    {
        $request = Request::findOrFail($requestId);

        DB::transaction(function () use ($request) {
            $data = [];

            try {
                $origin = $this->placesClient->getPlaceData($request->origin_place_id);
                $data['origin_place_name'] = $origin->name;
                $data['origin_latitude'] = $origin->latitude;
                $data['origin_longitude'] = $origin->longitude;

                $destination = $this->placesClient->getPlaceData($request->destination_place_id);
                $data['destination_place_name'] = $destination->name;
                $data['destination_latitude'] = $destination->latitude;
                $data['destination_longitude'] = $destination->longitude;

                $matrix = $this->distanceClient->getDistance($origin->place_id, $destination->place_id);
                $data['distance'] = $matrix['distance']['value'];
                $data['estimated_time'] = $matrix['duration']['value'];
                $data['estimated_cost'] = Request::getEstimatedCost($matrix['distance']['value']);
                $data['state'] = Request::STATE_WAITING_FOR_OFFER;
            } catch (\Throwable $th) {
                $this->rejected($request, $data, $th);
            }

            $request->update($data);
        });
    }

    public function updatePaymentStatus(string $uuid): Request
    {
        $transportRequest = Request::where('uuid', $uuid)->firstOrFail();

        $client = new Client();

        try {
            $response = $client->get("https://api.mercadopago.com/v1/payments/{$transportRequest->pix_payment_id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                ],
            ]);

            $paymentData = json_decode($response->getBody(), true);

            if (isset($paymentData['status'])) {
                if ($paymentData['status'] === 'approved') {
                    $transportRequest->state = Request::STATE_APPROVED;
                } elseif ($paymentData['status'] === 'pending') {
                    $transportRequest->state = Request::STATE_PENDING;
                } else {
                    $transportRequest->state = Request::STATE_REJECTED;
                }
            } else {
                $transportRequest->state = Request::STATE_REJECTED;
            }
        } catch (\Exception $e) {
            $this->rejected($transportRequest, [], $e);
        }

        $transportRequest->state = Request::STATE_APPROVED; // or any other state based on payment status
        $transportRequest->save();

        return $transportRequest;
    }

    private function rejected(Request $request, array $data, \Throwable $throwable): void
    {
        ErrorLog::create([
            'url' => 'N/A',
            'error_message' => $throwable->getMessage(),
            'stack_trace' => $throwable->getTraceAsString(),
            'request_data' => $data,
        ]);
        $data['state'] = Request::STATE_REJECTED;
        $request->update($data);
    }
}
