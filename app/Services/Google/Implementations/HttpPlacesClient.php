<?php

namespace App\Services\Google\Implementations;

use App\Factories\PlaceFactory;
use App\Models\Transport\Place;
use App\Services\Google\Contracts\PlacesClientInterface;
use Illuminate\Support\Facades\Http;

class HttpPlacesClient implements PlacesClientInterface
{
    protected const GOOGLE_PLACES_FIELDS = [
        'id',
        'short_formatted_address',
        'formatted_address',
        'icon_background_color',
        'location',
        'google_maps_uri',
    ];
    // protected const GOOGLE_PLACES_FIELDS = ['*'];

    public function getPlaceData(string $placeId): Place
    {
        $place = Place::where('place_id', $placeId)->first();
        if ($place) {
            return $place;
        }

        $response = Http::withHeaders([
            'Referer' => env('API_URL'),
        ])
            ->timeout(5)
            ->retry(2, 100)
            ->get("https://places.googleapis.com/v1/places/{$placeId}", [
                'fields' => implode(',', self::GOOGLE_PLACES_FIELDS),
                'key' => config('services.google.places_key'),
            ])
        ;

        if ($response->failed()) {
            throw new \RuntimeException("Erro ao buscar Place ID {$placeId}");
        }

        $data = $response->json();

        if (empty($data['formattedAddress']) || empty($data['location'])) {
            throw new \InvalidArgumentException("Dados incompletos para Place ID {$placeId}");
        }

        return PlaceFactory::create($data);
    }
}
