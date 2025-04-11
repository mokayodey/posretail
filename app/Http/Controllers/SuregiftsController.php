<?php

namespace App\Http\Controllers;

use App\Http\Requests\Suregifts\CreateGiftCardRequest;
use App\Http\Requests\Suregifts\ListGiftCardsRequest;
use App\Http\Requests\Suregifts\RedeemGiftCardRequest;
use App\Http\Requests\Suregifts\VoidGiftCardRequest;
use App\Services\SuregiftsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuregiftsController extends Controller
{
    protected $suregiftsService;

    public function __construct(SuregiftsService $suregiftsService)
    {
        $this->suregiftsService = $suregiftsService;
    }

    public function createGiftCard(CreateGiftCardRequest $request)
    {
        $response = $this->suregiftsService->createGiftCard($request->validated());

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function getGiftCard(Request $request, string $cardId)
    {
        $response = $this->suregiftsService->getGiftCard($cardId);

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function redeemGiftCard(RedeemGiftCardRequest $request, string $cardId)
    {
        $response = $this->suregiftsService->redeemGiftCard($cardId, $request->validated());

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function listGiftCards(ListGiftCardsRequest $request)
    {
        $response = $this->suregiftsService->listGiftCards($request->validated());

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function getGiftCardBalance(Request $request, string $cardId)
    {
        $response = $this->suregiftsService->getGiftCardBalance($cardId);

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function voidGiftCard(VoidGiftCardRequest $request, string $cardId)
    {
        $response = $this->suregiftsService->voidGiftCard($cardId, $request->validated());

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }
} 