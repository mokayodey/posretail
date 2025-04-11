<?php

namespace App\Http\Controllers;

use App\Services\MoniepointService;
use App\Services\SuregiftsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $moniepointService;
    protected $suregiftsService;

    public function __construct(
        MoniepointService $moniepointService,
        SuregiftsService $suregiftsService
    ) {
        $this->moniepointService = $moniepointService;
        $this->suregiftsService = $suregiftsService;
    }

    /**
     * Process Moniepoint payment
     */
    public function processMoniepoint(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'reference' => 'required|string',
            'description' => 'required|string'
        ]);

        try {
            $response = $this->moniepointService->initiatePayment([
                'amount' => $request->amount,
                'reference' => $request->reference,
                'description' => $request->description
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Moniepoint payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the payment'
            ], 500);
        }
    }

    /**
     * Verify Moniepoint payment
     */
    public function verifyMoniepoint(Request $request)
    {
        $request->validate([
            'reference' => 'required|string'
        ]);

        try {
            $response = $this->moniepointService->verifyPayment($request->reference);
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Moniepoint verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying the payment'
            ], 500);
        }
    }

    /**
     * Verify Suregifts gift card
     */
    public function verifyGiftCard(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
            'pin' => 'required|string'
        ]);

        try {
            $response = $this->suregiftsService->verifyGiftCard(
                $request->card_number,
                $request->pin
            );
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Suregifts verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying the gift card'
            ], 500);
        }
    }

    /**
     * Redeem Suregifts gift card
     */
    public function redeemGiftCard(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string',
            'pin' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            $response = $this->suregiftsService->redeemGiftCard(
                $request->card_number,
                $request->pin,
                $request->amount
            );
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Suregifts redemption error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while redeeming the gift card'
            ], 500);
        }
    }
} 