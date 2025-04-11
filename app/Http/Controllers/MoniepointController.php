<?php

namespace App\Http\Controllers;

use App\Http\Requests\Moniepoint\InitiatePaymentRequest;
use App\Http\Requests\Moniepoint\RefundPaymentRequest;
use App\Http\Requests\Moniepoint\TransactionHistoryRequest;
use App\Services\MoniepointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MoniepointController extends Controller
{
    protected $moniepointService;

    public function __construct(MoniepointService $moniepointService)
    {
        $this->moniepointService = $moniepointService;
    }

    public function initiatePayment(InitiatePaymentRequest $request)
    {
        $response = $this->moniepointService->initiatePayment($request->validated());

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function verifyPayment(Request $request, string $reference)
    {
        $response = $this->moniepointService->verifyPayment($reference);

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function getTransactionHistory(TransactionHistoryRequest $request)
    {
        $response = $this->moniepointService->getTransactionHistory($request->validated());

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }

    public function refundPayment(RefundPaymentRequest $request, string $reference)
    {
        $response = $this->moniepointService->refundPayment($reference, $request->validated());

        if (!$response['success']) {
            return response()->json($response, 400);
        }

        return response()->json($response);
    }
} 