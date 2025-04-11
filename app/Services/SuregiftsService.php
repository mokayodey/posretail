<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuregiftsService
{
    protected $baseUrl;
    protected $apiKey;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.suregifts.base_url');
        $this->apiKey = config('services.suregifts.api_key');
        $this->secretKey = config('services.suregifts.secret_key');
    }

    public function createGiftCard(array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/gift-cards', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Gift card created successfully'
                ];
            }

            Log::error('Suregifts gift card creation failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Gift card creation failed',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Suregifts gift card creation error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while creating gift card',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getGiftCard(string $cardId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/gift-cards/' . $cardId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Gift card retrieved successfully'
                ];
            }

            Log::error('Suregifts gift card retrieval failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Gift card retrieval failed',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Suregifts gift card retrieval error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while retrieving gift card',
                'error' => $e->getMessage()
            ];
        }
    }

    public function redeemGiftCard(string $cardId, array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/gift-cards/' . $cardId . '/redeem', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Gift card redeemed successfully'
                ];
            }

            Log::error('Suregifts gift card redemption failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Gift card redemption failed',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Suregifts gift card redemption error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while redeeming gift card',
                'error' => $e->getMessage()
            ];
        }
    }

    public function listGiftCards(array $filters = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/gift-cards', $filters);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Gift cards retrieved successfully'
                ];
            }

            Log::error('Suregifts gift cards listing failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to list gift cards',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Suregifts gift cards listing error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while listing gift cards',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getGiftCardBalance(string $cardId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/gift-cards/' . $cardId . '/balance');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Gift card balance retrieved successfully'
                ];
            }

            Log::error('Suregifts gift card balance retrieval failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to retrieve gift card balance',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Suregifts gift card balance retrieval error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while retrieving gift card balance',
                'error' => $e->getMessage()
            ];
        }
    }

    public function voidGiftCard(string $cardId, array $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/gift-cards/' . $cardId . '/void', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Gift card voided successfully'
                ];
            }

            Log::error('Suregifts gift card voiding failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to void gift card',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Suregifts gift card voiding error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while voiding gift card',
                'error' => $e->getMessage()
            ];
        }
    }
} 