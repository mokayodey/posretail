<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoniepointService
{
    protected $baseUrl;
    protected $apiKey;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.moniepoint.base_url');
        $this->apiKey = config('services.moniepoint.api_key');
        $this->secretKey = config('services.moniepoint.secret_key');
    }

    /**
     * Initiate a payment transaction
     */
    public function initiatePayment(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payment/initiate', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Moniepoint payment initiation failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Payment initiation failed',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Moniepoint payment initiation error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while initiating payment',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/payment/verify/' . $reference);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Moniepoint payment verification failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Moniepoint payment verification error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying payment',
                'error' => $e->getMessage()
            ];
        }
    }
} 