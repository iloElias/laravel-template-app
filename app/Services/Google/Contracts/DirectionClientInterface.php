<?php

namespace App\Services\Google\Contracts;

use App\Models\Transport\Place;

interface DirectionClientInterface
{
    /**
     * Busca dados de um place pelo ID.
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function getDirections(string $originPlaceId, string $destinationPlaceId): array;
}
