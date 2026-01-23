<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic as VonageCredentials;
use Vonage\SMS\Message\SMS as VonageSMS;

class SmsService
{
    protected $client;

    protected $from;

    public function __construct()
    {
        $key = config('services.vonage.key');
        $secret = config('services.vonage.secret');
        $this->from = config('services.vonage.from', 'Agrofast');

        $credentials = new VonageCredentials($key, $secret);
        $this->client = new VonageClient($credentials);
    }

    /**
     * Sends an SMS message using Vonage.
     *
     * @param string $phoneNumber Destination phone number
     * @param string $message     Message to be sent
     */
    public function to(string $phoneNumber, string $message): void
    {
        try {
            $response = $this->client->sms()->send(
                new VonageSMS($phoneNumber, $this->from, $message)
            );

            $messageResponse = $response->current();

            if ($messageResponse->getStatus() == 0) {
                Log::info("Message successfully sent to {$phoneNumber}");
            } else {
                Log::error("Failed to send message to {$phoneNumber}: " . $messageResponse->getStatus());
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS: ' . $e->getMessage());
        }
    }
}
