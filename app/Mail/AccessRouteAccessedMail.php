<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessRouteAccessedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo acesso na rota /access',
        );
    }

    public function content(): Content
    {
        $geo = $this->data['geo'] ?? [];
        $latitude = $geo['latitude'] ?? null;
        $longitude = $geo['longitude'] ?? null;

        return new Content(
            view: 'emails.access-route-accessed',
            with: [
                'access' => $this->data['access'] ?? [],
                'geo' => $geo,
                'mapImageUrl' => $this->buildMapImageUrl($latitude, $longitude),
                'mapOpenUrl' => $this->buildOpenMapUrl($latitude, $longitude),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function buildMapImageUrl(null|float|int|string $latitude, null|float|int|string $longitude): ?string
    {
        if (!$this->hasCoordinates($latitude, $longitude)) {
            return null;
        }

        return sprintf(
            'https://static-maps.yandex.ru/1.x/?lang=en_US&ll=%s,%s&z=12&size=450,250&l=map&pt=%s,%s,pm2rdm',
            rawurlencode((string) $longitude),
            rawurlencode((string) $latitude),
            rawurlencode((string) $longitude),
            rawurlencode((string) $latitude),
        );
    }

    private function buildOpenMapUrl(null|float|int|string $latitude, null|float|int|string $longitude): ?string
    {
        if (!$this->hasCoordinates($latitude, $longitude)) {
            return null;
        }

        return sprintf(
            'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=12/%s/%s',
            rawurlencode((string) $latitude),
            rawurlencode((string) $longitude),
            rawurlencode((string) $latitude),
            rawurlencode((string) $longitude),
        );
    }

    private function hasCoordinates(null|float|int|string $latitude, null|float|int|string $longitude): bool
    {
        if ($latitude === null || $longitude === null) {
            return false;
        }

        return is_numeric((string) $latitude) && is_numeric((string) $longitude);
    }
}
