<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'inventory', 'supplier']);

        // Apply filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('branch_id')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        if ($request->has('low_stock')) {
            $query->whereHas('inventory', function ($q) {
                $q->whereRaw('quantity <= low_stock_threshold');
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            $sort = $request->sort;
            $direction = $request->direction ?? 'asc';
            $query->orderBy($sort, $direction);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'low_stock_threshold' => 'required|integer|min:0',
            'branch_id' => 'required|exists:branches,id',
            'location' => 'nullable|string',
            'pricing' => 'nullable|array',
            'pricing.base_price' => 'nullable|numeric|min:0',
            'pricing.sale_price' => 'nullable|numeric|min:0',
            'pricing.wholesale_price' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*.url' => 'nullable|url',
            'images.*.is_primary' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
            'tax_category' => 'nullable|string',
            'barcode' => 'nullable|string|unique:products',
            'unit_of_measure' => 'required|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reorder_point' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Create product
            $product = Product::create([
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'cost_price' => $validated['cost_price'],
                'category_id' => $validated['category_id'],
                'low_stock_threshold' => $validated['low_stock_threshold'],
                'pricing' => $validated['pricing'] ?? null,
                'attributes' => $validated['attributes'] ?? null,
                'images' => $validated['images'] ?? null,
                'status' => $validated['status'],
                'tax_category' => $validated['tax_category'] ?? null,
                'barcode' => $validated['barcode'] ?? null,
                'unit_of_measure' => $validated['unit_of_measure'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'reorder_point' => $validated['reorder_point'],
                'reorder_quantity' => $validated['reorder_quantity']
            ]);

            // Create initial inventory
            Inventory::create([
                'product_id' => $product->id,
                'branch_id' => $validated['branch_id'],
                'location' => $validated['location'] ?? null,
                'quantity' => 0,
                'low_stock_threshold' => $validated['low_stock_threshold']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'inventory', 'supplier'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'inventory', 'supplier'])
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'sometimes|required|string|max:50|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'low_stock_threshold' => 'sometimes|required|integer|min:0',
            'pricing' => 'nullable|array',
            'pricing.base_price' => 'nullable|numeric|min:0',
            'pricing.sale_price' => 'nullable|numeric|min:0',
            'pricing.wholesale_price' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*.url' => 'nullable|url',
            'images.*.is_primary' => 'nullable|boolean',
            'status' => 'sometimes|required|in:active,inactive',
            'tax_category' => 'nullable|string',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'unit_of_measure' => 'sometimes|required|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reorder_point' => 'sometimes|required|integer|min:0',
            'reorder_quantity' => 'sometimes|required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            $product->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'inventory', 'supplier'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product stock
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'branch_id' => 'required|exists:branches,id',
            'location' => 'nullable|string',
            'batch_number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'cost_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'type' => 'required|in:purchase,transfer,adjustment,return,damage,expiry',
            'reference' => 'nullable|string',
            'barcode' => 'nullable|string|exists:products,barcode'
        ]);

        try {
            DB::beginTransaction();

            // If barcode is provided, find the product
            if ($request->has('barcode')) {
                $product = Product::where('barcode', $request->barcode)->firstOrFail();
            }

            $inventory = Inventory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'branch_id' => $validated['branch_id']
                ],
                [
                    'location' => $validated['location'],
                    'quantity' => 0,
                    'low_stock_threshold' => $product->low_stock_threshold
                ]
            );

            // Update stock quantity
            $inventory->increment('quantity', $validated['quantity']);

            // Record stock movement
            $inventory->movements()->create([
                'quantity' => $validated['quantity'],
                'batch_number' => $validated['batch_number'],
                'expiry_date' => $validated['expiry_date'],
                'cost_price' => $validated['cost_price'],
                'notes' => $validated['notes'],
                'type' => $validated['type'],
                'reference' => $validated['reference'],
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => $inventory->load('movements')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock movement history
     */
    public function getStockMovements(Request $request, Product $product)
    {
        $query = $product->inventory()
            ->with('movements')
            ->where('branch_id', $request->branch_id);

        if ($request->has('start_date')) {
            $query->whereHas('movements', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->start_date);
            });
        }

        if ($request->has('end_date')) {
            $query->whereHas('movements', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->end_date);
            });
        }

        if ($request->has('type')) {
            $query->whereHas('movements', function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        if ($request->has('reference')) {
            $query->whereHas('movements', function ($q) use ($request) {
                $q->where('reference', $request->reference);
            });
        }

        $inventory = $query->first();

        return response()->json([
            'success' => true,
            'data' => $inventory
        ]);
    }
} 