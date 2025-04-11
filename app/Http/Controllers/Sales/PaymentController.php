<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Payment;
use App\Services\MoniepointService;
use App\Services\SuregiftsService;
use App\Events\PaymentReceived;
use App\Events\TransferPaymentReceived;

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
     * Process cash payment
     */
    public function processCash(Request $request, Cart $cart)
    {
        $request->validate([
            'amount_received' => 'required|numeric|min:' . $cart->total,
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $change = $request->amount_received - $cart->total;

            $payment = Payment::create([
                'cart_id' => $cart->id,
                'user_id' => auth()->id(),
                'amount' => $cart->total,
                'payment_method' => 'cash',
                'status' => 'completed',
                'receipt_number' => 'RCP-' . Str::upper(Str::random(8)),
                'transaction_code' => $cart->transaction_code,
                'payment_details' => [
                    'amount_received' => $request->amount_received,
                    'change' => $change,
                    'notes' => $request->notes
                ]
            ]);

            $cart->update(['status' => 'completed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cash payment processed successfully',
                'data' => [
                    'payment' => $payment,
                    'change' => $change,
                    'receipt_number' => $payment->receipt_number
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process cash payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Moniepoint payment
     */
    public function processMoniepoint(Request $request, Cart $cart)
    {
        $request->validate([
            'terminal_id' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'cart_id' => $cart->id,
                'user_id' => auth()->id(),
                'amount' => $cart->total,
                'payment_method' => 'moniepoint',
                'status' => 'pending',
                'terminal_id' => $request->terminal_id,
                'transaction_code' => $cart->transaction_code,
                'payment_details' => [
                    'notes' => $request->notes
                ]
            ]);

            $response = $this->moniepointService->initiatePayment([
                'amount' => $cart->total,
                'reference' => $payment->id,
                'description' => 'POS Payment',
                'terminal_id' => $request->terminal_id
            ]);

            if ($response['success']) {
                $payment->update([
                    'status' => 'completed',
                    'reference' => $response['reference'],
                    'receipt_number' => 'RCP-' . Str::upper(Str::random(8)),
                    'payment_details' => array_merge($payment->payment_details, [
                        'terminal_response' => $response['details'],
                        'card_type' => $response['details']['card_type'] ?? null,
                        'last_four' => $response['details']['last_four'] ?? null
                    ])
                ]);

                $cart->update(['status' => 'completed']);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => array_merge($payment->payment_details, [
                        'error' => $response['message']
                    ])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
                'data' => [
                    'payment' => $payment,
                    'receipt_number' => $payment->receipt_number
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process Moniepoint payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Suregifts payment
     */
    public function processSuregifts(Request $request, Cart $cart)
    {
        $request->validate([
            'gift_card_code' => 'required|string',
            'amount_to_use' => 'required|numeric|min:0|max:' . $cart->total,
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'cart_id' => $cart->id,
                'user_id' => auth()->id(),
                'amount' => $request->amount_to_use,
                'payment_method' => 'suregifts',
                'status' => 'pending',
                'transaction_code' => $cart->transaction_code,
                'payment_details' => [
                    'gift_card_code' => $request->gift_card_code,
                    'notes' => $request->notes
                ]
            ]);

            $response = $this->suregiftsService->redeemGiftCard([
                'code' => $request->gift_card_code,
                'amount' => $request->amount_to_use
            ]);

            if ($response['success']) {
                $payment->update([
                    'status' => 'completed',
                    'reference' => $response['reference'],
                    'receipt_number' => 'RCP-' . Str::upper(Str::random(8)),
                    'payment_details' => array_merge($payment->payment_details, [
                        'gift_card_details' => $response['details']
                    ])
                ]);

                if ($request->amount_to_use < $cart->total) {
                    // Handle remaining balance with another payment method
                    $remaining = $cart->total - $request->amount_to_use;
                    return response()->json([
                        'success' => true,
                        'message' => 'Partial payment successful',
                        'data' => [
                            'payment' => $payment,
                            'remaining_balance' => $remaining,
                            'receipt_number' => $payment->receipt_number
                        ]
                    ]);
                }

                $cart->update(['status' => 'completed']);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'payment_details' => array_merge($payment->payment_details, [
                        'error' => $response['message']
                    ])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
                'data' => [
                    'payment' => $payment,
                    'receipt_number' => $payment->receipt_number
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process Suregifts payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Void payment
     */
    public function voidPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            if ($payment->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only completed payments can be voided'
                ], 400);
            }

            if ($payment->payment_method === 'moniepoint') {
                $response = $this->moniepointService->refundPayment($payment->reference, [
                    'amount' => $payment->amount,
                    'reason' => $request->reason
                ]);

                if (!$response['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to refund Moniepoint payment',
                        'error' => $response['message']
                    ], 400);
                }
            }

            $payment->update([
                'is_void' => true,
                'voided_at' => now(),
                'voided_by' => auth()->id(),
                'payment_details' => array_merge($payment->payment_details, [
                    'void_reason' => $request->reason
                ])
            ]);

            $payment->cart->update(['status' => 'voided']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment voided successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to void payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process bank transfer payment with immediate confirmation
     */
    public function processBankTransfer(Request $request, Cart $cart)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'reference' => 'required|string',
            'amount' => 'required|numeric|min:0|max:' . $cart->total,
            'notes' => 'nullable|string',
            'confirm' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'cart_id' => $cart->id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'payment_method' => 'bank_transfer',
                'status' => $request->confirm ? 'completed' : 'pending',
                'transaction_code' => $cart->transaction_code,
                'receipt_number' => $request->confirm ? 'RCP-' . Str::upper(Str::random(8)) : null,
                'payment_details' => [
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                    'reference' => $request->reference,
                    'notes' => $request->notes,
                    'confirmed_at' => $request->confirm ? now() : null,
                    'confirmed_by' => $request->confirm ? auth()->id() : null
                ]
            ]);

            if ($request->confirm) {
                $cart->update(['status' => 'completed']);
                event(new TransferPaymentReceived($payment));
            } else {
                event(new PaymentReceived($payment));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->confirm ? 'Bank transfer payment processed and confirmed' : 'Bank transfer payment initiated',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process bank transfer payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Moniepoint terminal transfer with immediate confirmation
     */
    public function processMoniepointTransfer(Request $request, Cart $cart)
    {
        $request->validate([
            'terminal_id' => 'required|string',
            'amount' => 'required|numeric|min:0|max:' . $cart->total,
            'notes' => 'nullable|string',
            'confirm' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'cart_id' => $cart->id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'payment_method' => 'moniepoint_transfer',
                'status' => $request->confirm ? 'completed' : 'pending',
                'terminal_id' => $request->terminal_id,
                'transaction_code' => $cart->transaction_code,
                'receipt_number' => $request->confirm ? 'RCP-' . Str::upper(Str::random(8)) : null,
                'payment_details' => [
                    'notes' => $request->notes,
                    'confirmed_at' => $request->confirm ? now() : null,
                    'confirmed_by' => $request->confirm ? auth()->id() : null
                ]
            ]);

            if ($request->confirm) {
                $cart->update(['status' => 'completed']);
                event(new TransferPaymentReceived($payment));
            } else {
                event(new PaymentReceived($payment));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->confirm ? 'Moniepoint transfer payment processed and confirmed' : 'Moniepoint transfer payment initiated',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process Moniepoint transfer payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick confirm payment (for cashiers)
     */
    public function quickConfirmPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'reference' => 'required|string',
            'amount' => 'required|numeric',
            'payment_method' => 'required|in:bank_transfer,moniepoint_transfer'
        ]);

        try {
            DB::beginTransaction();

            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending payments can be confirmed'
                ], 400);
            }

            if ($request->amount != $payment->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount does not match'
                ], 400);
            }

            $payment->update([
                'status' => 'completed',
                'reference' => $request->reference,
                'receipt_number' => 'RCP-' . Str::upper(Str::random(8)),
                'payment_details' => array_merge($payment->payment_details, [
                    'confirmed_at' => now(),
                    'confirmed_by' => auth()->id(),
                    'quick_confirm' => true
                ])
            ]);

            $payment->cart->update(['status' => 'completed']);

            event(new TransferPaymentReceived($payment));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending payments for cashier
     */
    public function getCashierPendingPayments(Request $request)
    {
        $request->validate([
            'payment_method' => 'nullable|in:bank_transfer,moniepoint_transfer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $query = Payment::whereIn('payment_method', ['bank_transfer', 'moniepoint_transfer'])
            ->where('status', 'pending')
            ->whereHas('cart', function ($q) {
                $q->where('branch_id', auth()->user()->branch_id);
            })
            ->with(['cart', 'user']);

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $payments = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get pending transfer payments
     */
    public function getPendingTransfers(Request $request)
    {
        $request->validate([
            'payment_method' => 'nullable|in:bank_transfer,moniepoint_transfer',
            'branch_id' => 'nullable|exists:branches,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $query = Payment::whereIn('payment_method', ['bank_transfer', 'moniepoint_transfer'])
            ->where('status', 'pending')
            ->with(['cart', 'user']);

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->branch_id) {
            $query->whereHas('cart', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $payments = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Source pending transfers from Moniepoint
     */
    public function sourceMoniepointTransfers(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        try {
            DB::beginTransaction();

            // Get pending Moniepoint transfers from the terminal
            $response = $this->moniepointService->getPendingTransfers([
                'terminal_id' => $request->terminal_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch pending transfers',
                    'error' => $response['message']
                ], 400);
            }

            $transfers = $response['data'];
            $matchedPayments = [];
            $unmatchedTransfers = [];

            foreach ($transfers as $transfer) {
                // Try to match with existing pending payment
                $payment = Payment::where('status', 'pending')
                    ->where('payment_method', 'moniepoint_transfer')
                    ->where('terminal_id', $request->terminal_id)
                    ->where('amount', $transfer['amount'])
                    ->whereDate('created_at', '<=', $transfer['date'])
                    ->first();

                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'reference' => $transfer['reference'],
                        'receipt_number' => 'RCP-' . Str::upper(Str::random(8)),
                        'payment_details' => array_merge($payment->payment_details, [
                            'transfer_details' => $transfer,
                            'matched_at' => now(),
                            'matched_by' => auth()->id()
                        ])
                    ]);

                    $payment->cart->update(['status' => 'completed']);
                    event(new TransferPaymentReceived($payment));
                    $matchedPayments[] = $payment;
                } else {
                    $unmatchedTransfers[] = $transfer;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfers sourced successfully',
                'data' => [
                    'matched_payments' => $matchedPayments,
                    'unmatched_transfers' => $unmatchedTransfers
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to source transfers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch confirm payments
     */
    public function batchConfirmPayments(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id',
            'confirm_all' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $query = Payment::whereIn('id', $request->payment_ids)
                ->where('status', 'pending');

            if (!$request->confirm_all) {
                $query->where('payment_method', 'bank_transfer');
            }

            $payments = $query->get();
            $confirmedPayments = [];

            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'completed',
                    'receipt_number' => 'RCP-' . Str::upper(Str::random(8)),
                    'payment_details' => array_merge($payment->payment_details, [
                        'confirmed_at' => now(),
                        'confirmed_by' => auth()->id(),
                        'batch_confirmed' => true
                    ])
                ]);

                $payment->cart->update(['status' => 'completed']);
                event(new TransferPaymentReceived($payment));
                $confirmedPayments[] = $payment;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payments confirmed successfully',
                'data' => [
                    'confirmed_payments' => $confirmedPayments,
                    'count' => count($confirmedPayments)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unmatched transfers
     */
    public function getUnmatchedTransfers(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        try {
            $response = $this->moniepointService->getPendingTransfers([
                'terminal_id' => $request->terminal_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch transfers',
                    'error' => $response['message']
                ], 400);
            }

            $transfers = $response['data'];
            $unmatchedTransfers = [];

            foreach ($transfers as $transfer) {
                $payment = Payment::where('status', 'pending')
                    ->where('payment_method', 'moniepoint_transfer')
                    ->where('terminal_id', $request->terminal_id)
                    ->where('amount', $transfer['amount'])
                    ->whereDate('created_at', '<=', $transfer['date'])
                    ->first();

                if (!$payment) {
                    $unmatchedTransfers[] = $transfer;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'unmatched_transfers' => $unmatchedTransfers,
                    'count' => count($unmatchedTransfers)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unmatched transfers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 