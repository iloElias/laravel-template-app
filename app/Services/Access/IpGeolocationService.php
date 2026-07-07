<?php

namespace App\Services\Access;

use Illuminate\Support\Facades\Http;

class IpGeolocationService
{
    public function locate(?string $ip): array
    {
        if (!$ip || !$this->isPublicIp($ip)) {
            return [];
        }

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get("https://ipwho.is/{$ip}");

            if (!$response->ok()) {
                return [];
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                return [];
            }

            return [
                'provider' => 'ipwho.is',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'city' => $data['city'] ?? null,
                'region' => $data['region'] ?? null,
                'country' => $data['country'] ?? null,
                'country_code' => $data['country_code'] ?? null,
                'timezone' => $data['timezone']['id'] ?? null,
                'isp' => $data['connection']['isp'] ?? null,
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }
}
