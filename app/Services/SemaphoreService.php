<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SemaphoreService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.key');
    }

    public static function send(string $number, string $message, string $sender = 'KEYFLEET')
    {
        $response = Http::asForm()->post('https://semaphore.co/api/v4/messages', [
            'apikey'     => config('services.semaphore.key'),
            'number'     => $number,
            'message'    => $message,
            'sendername' => $sender,
        ]);

        return $response->json();
    }
}