<?php

namespace App\Services\Google\Contracts;

interface DistanceMatrixClientInterface
{
    /**
     * Calcula distância e duração entre origem e destino.
     *
     * @param string $origin      Place ID ou endereço de origem
     * @param string $destination Place ID ou endereço de destino
     *
     * @return array{distance: array{value: int, text: string}, duration: array{value: int, text: string}}
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function getDistance(string $origin, string $destination): array;
}
