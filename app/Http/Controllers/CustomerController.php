<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Apply filters
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('membership_tier')) {
            $query->where('membership_tier', $request->membership_tier);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Apply sorting
        $sortField = $request->input('sort', 'name');
        $sortDirection = $request->input('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'anniversary_date' => 'nullable|date',
            'preferences' => 'nullable|array'
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    public function show(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'data' => $customer->load(['loyaltyTransactions', 'rewards'])
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('customers')->ignore($customer->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'anniversary_date' => 'nullable|date',
            'preferences' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive'
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer
        ]);
    }

    public function addPoints(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'source' => 'required|string',
            'description' => 'nullable|string',
            'sale_id' => 'nullable|exists:sales,id'
        ]);

        DB::transaction(function () use ($customer, $validated) {
            $customer->addPoints(
                $validated['points'],
                $validated['source'],
                $validated['description']
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Points added successfully',
            'data' => $customer->fresh()
        ]);
    }

    public function redeemPoints(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1|max:' . $customer->loyalty_points,
            'description' => 'nullable|string'
        ]);

        $success = DB::transaction(function () use ($customer, $validated) {
            return $customer->redeemPoints(
                $validated['points'],
                $validated['description']
            );
        });

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Points redeemed successfully',
            'data' => $customer->fresh()
        ]);
    }

    public function getLoyaltyHistory(Customer $customer)
    {
        $transactions = $customer->loyaltyTransactions()
            ->with('sale')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function getAvailableRewards(Customer $customer)
    {
        $rewards = $customer->rewards()
            ->where('status', 'available')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }

    public function createReward(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'expires_at' => 'nullable|date|after:now'
        ]);

        $reward = $customer->rewards()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Reward created successfully',
            'data' => $reward
        ], 201);
    }

    public function redeemReward(Customer $customer, Reward $reward)
    {
        if ($reward->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not found'
            ], 404);
        }

        if ($reward->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Reward is not available'
            ], 400);
        }

        if ($reward->expires_at && $reward->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Reward has expired'
            ], 400);
        }

        DB::transaction(function () use ($customer, $reward) {
            $customer->redeemPoints($reward->points_cost, "Redeemed reward: {$reward->name}");
            $reward->update([
                'status' => 'redeemed',
                'redeemed_at' => now()
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Reward redeemed successfully',
            'data' => $reward->fresh()
        ]);
    }
} 