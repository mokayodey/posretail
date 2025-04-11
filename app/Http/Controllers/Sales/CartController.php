<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Branch;
use App\Models\Customer;

class CartController extends Controller
{
    /**
     * Create a new cart for physical POS
     */
    public function create(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'location' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        try {
            $cart = Cart::create([
                'branch_id' => $request->branch_id,
                'cashier_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'location' => $request->location,
                'notes' => $request->notes,
                'transaction_code' => 'TRX-' . Str::upper(Str::random(8)),
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'data' => $cart->load(['branch', 'cashier', 'customer'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to cart using barcode or product ID
     */
    public function addItem(Request $request, Cart $cart)
    {
        $request->validate([
            'product_id' => 'required_without:barcode|exists:products,id',
            'barcode' => 'required_without:product_id|string',
            'quantity' => 'required|integer|min:1',
            'price_override' => 'nullable|numeric|min:0',
            'location' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Find product by ID or barcode
            $product = $request->product_id 
                ? Product::findOrFail($request->product_id)
                : Product::where('barcode', $request->barcode)->firstOrFail();

            // Check if product is in stock at the branch
            $inventory = $product->inventory()
                ->where('branch_id', $cart->branch_id)
                ->where('location', $request->location ?? $cart->location)
                ->first();

            if (!$inventory || $inventory->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock'
                ], 400);
            }

            // Check if item already exists in cart
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $request->quantity,
                    'price' => $request->price_override ?? $product->selling_price,
                    'notes' => $request->notes
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'price' => $request->price_override ?? $product->selling_price,
                    'notes' => $request->notes
                ]);
            }

            // Update cart totals
            $this->updateCartTotals($cart);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => $cart->fresh(['items.product', 'branch', 'cashier', 'customer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, Cart $cart, CartItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'price_override' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($item->product_id);

            // Check if product is in stock at the branch
            $inventory = $product->inventory()
                ->where('branch_id', $cart->branch_id)
                ->where('location', $cart->location)
                ->first();

            if (!$inventory || $inventory->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock'
                ], 400);
            }

            $item->update([
                'quantity' => $request->quantity,
                'price' => $request->price_override ?? $product->selling_price,
                'notes' => $request->notes
            ]);

            // Update cart totals
            $this->updateCartTotals($cart);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $cart->fresh(['items.product', 'branch', 'cashier', 'customer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Cart $cart, CartItem $item)
    {
        try {
            DB::beginTransaction();

            $item->delete();

            // Update cart totals
            $this->updateCartTotals($cart);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully',
                'data' => $cart->fresh(['items.product', 'branch', 'cashier', 'customer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply discount to cart
     */
    public function applyDiscount(Request $request, Cart $cart)
    {
        $request->validate([
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $cart->update([
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'notes' => $request->notes
            ]);

            // Update cart totals
            $this->updateCartTotals($cart);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Discount applied successfully',
                'data' => $cart->fresh(['items.product', 'branch', 'cashier', 'customer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply discount',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Void cart transaction
     */
    public function voidCart(Request $request, Cart $cart)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            if ($cart->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active carts can be voided'
                ], 400);
            }

            $cart->update([
                'is_void' => true,
                'voided_at' => now(),
                'voided_by' => auth()->id(),
                'notes' => $request->reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cart voided successfully',
                'data' => $cart->fresh(['items.product', 'branch', 'cashier', 'customer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to void cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cart details
     */
    public function show(Cart $cart)
    {
        try {
            $cart->load([
                'items.product',
                'branch',
                'cashier',
                'customer',
                'payments'
            ]);

            return response()->json([
                'success' => true,
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart totals
     */
    private function updateCartTotals(Cart $cart)
    {
        $subtotal = $cart->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $discount = 0;
        if ($cart->discount_type === 'percentage') {
            $discount = $subtotal * ($cart->discount_value / 100);
        } else {
            $discount = $cart->discount_value;
        }

        $taxableAmount = $subtotal - $discount;
        $taxAmount = $taxableAmount * ($cart->tax_rate / 100);

        $cart->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $taxAmount,
            'total' => $subtotal - $discount + $taxAmount
        ]);
    }
} 