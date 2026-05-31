<?php

namespace App\Services;

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Http;

class PaymongoService
{
    protected $baseUrl = 'https://api.paymongo.com/v1/';
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret');
    }

    protected function request($method, $endpoint, $data = [])
    {
        return Http::withBasicAuth($this->secretKey, '')
            // ->withHeaders([
            //     'Content-Type' => 'application/json',
            // ])
            ->$method($this->baseUrl . $endpoint, $data);
    }

    public function createPaymentIntent($amount, $currency = 'PHP')
    {
        return $this->request('post', 'payment_intents', [
            'data' => [
                'attributes' => [
                    'amount' => $amount,
                    'currency' => $currency,
                    'payment_method_allowed' => ['card'],
                    'payment_method_options' => ['card'],
                ],
            ],
        ]);
    }

    public function attachPaymentMethod($intentId, $paymentMethodId)
    {
        return $this->request('post', "payment_intents/{$intentId}/attach", [
            'data' => [
                'attributes' => [
                    'payment_method' => $paymentMethodId,
                ],
            ],
        ]);
    }

    public function createCheckoutSession($data)
    {
        return $this->request('post', 'checkout_sessions', $data);
    }
}
