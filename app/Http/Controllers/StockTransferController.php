<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', StockTransfer::class);

        $query = StockTransfer::with(['sourceBranch', 'destinationBranch', 'items.product', 'creator', 'approver']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('source_branch_id', $request->branch_id)
                  ->orWhere('destination_branch_id', $request->branch_id);
            });
        }

        $transfers = $query->latest()->paginate(20);
        return response()->json($transfers);
    }

    public function store(Request $request)
    {
        $this->authorize('create', StockTransfer::class);

        $validated = $request->validate([
            'source_branch_id' => 'required|exists:branches,id',
            'destination_branch_id' => 'required|exists:branches,id|different:source_branch_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            // Check inventory availability
            foreach ($validated['items'] as $item) {
                $inventory = Inventory::where('branch_id', $validated['source_branch_id'])
                    ->where('product_id', $item['product_id'])
                    ->firstOrFail();

                if ($inventory->quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Insufficient stock for product ID: {$item['product_id']}"
                    ], 422);
                }
            }

            // Create transfer
            $transfer = StockTransfer::create([
                'source_branch_id' => $validated['source_branch_id'],
                'destination_branch_id' => $validated['destination_branch_id'],
                'transfer_code' => 'TR-' . Str::upper(Str::random(8)),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create transfer items
            foreach ($validated['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => Product::find($item['product_id'])->cost_price,
                ]);
            }

            return response()->json($transfer->load('items.product'), 201);
        });
    }

    public function show(StockTransfer $transfer)
    {
        $this->authorize('view', $transfer);

        $transfer->load(['sourceBranch', 'destinationBranch', 'items.product', 'creator', 'approver']);
        return response()->json($transfer);
    }

    public function approve(StockTransfer $transfer)
    {
        $this->authorize('approve', $transfer);

        return DB::transaction(function () use ($transfer) {
            if (!$transfer->approve(auth()->user())) {
                return response()->json([
                    'message' => 'Transfer cannot be approved in its current state'
                ], 422);
            }

            // Update source branch inventory
            foreach ($transfer->items as $item) {
                $inventory = Inventory::where('branch_id', $transfer->source_branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                $inventory->decrement('quantity', $item->quantity);
            }

            return response()->json($transfer->load('items.product'));
        });
    }

    public function complete(StockTransfer $transfer)
    {
        $this->authorize('complete', $transfer);

        return DB::transaction(function () use ($transfer) {
            if (!$transfer->complete()) {
                return response()->json([
                    'message' => 'Transfer cannot be completed in its current state'
                ], 422);
            }

            // Update destination branch inventory
            foreach ($transfer->items as $item) {
                $inventory = Inventory::firstOrCreate(
                    [
                        'branch_id' => $transfer->destination_branch_id,
                        'product_id' => $item->product_id
                    ],
                    ['quantity' => 0]
                );

                $inventory->increment('quantity', $item->quantity);
            }

            return response()->json($transfer->load('items.product'));
        });
    }

    public function cancel(StockTransfer $transfer)
    {
        $this->authorize('cancel', $transfer);

        if (!$transfer->cancel()) {
            return response()->json([
                'message' => 'Transfer cannot be cancelled in its current state'
            ], 422);
        }

        return response()->json($transfer->load('items.product'));
    }
} 