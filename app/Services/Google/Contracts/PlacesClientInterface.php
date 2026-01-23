<?php

namespace App\Services\Google\Contracts;

use App\Models\Transport\Place;

interface PlacesClientInterface
{
    /**
     * Busca dados de um place pelo ID.
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function getPlaceData(string $placeId): Place;
}
