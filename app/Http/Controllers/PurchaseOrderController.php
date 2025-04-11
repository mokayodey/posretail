<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Contact;
use App\Models\Product;
use App\Models\BusinessLocation;
use App\Models\TaxRate;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_order')
            ->with(['contact', 'location', 'purchase_lines', 'purchase_lines.product']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('contact_id', $request->supplier_id);
        }

        if ($request->has('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $purchase_orders = $query->latest()->paginate(10);

        $suppliers = Contact::where('business_id', $business_id)
            ->where('type', 'supplier')
            ->pluck('name', 'id');

        $locations = BusinessLocation::forDropdown($business_id);

        return response()->json([
            'purchase_orders' => $purchase_orders,
            'suppliers' => $suppliers,
            'locations' => $locations
        ]);
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $transaction_data = [
                'business_id' => $business_id,
                'type' => 'purchase_order',
                'status' => 'ordered',
                'contact_id' => $request->contact_id,
                'transaction_date' => $request->transaction_date,
                'total_before_tax' => $request->total_before_tax,
                'location_id' => $request->location_id,
                'final_total' => $request->final_total,
                'created_by' => $user_id,
                'tax_id' => $request->tax_id,
                'tax_amount' => $request->tax_amount,
                'shipping_details' => $request->shipping_details,
                'shipping_charges' => $request->shipping_charges,
                'additional_notes' => $request->additional_notes,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'pay_term_number' => $request->pay_term_number,
                'pay_term_type' => $request->pay_term_type,
                'delivery_date' => $request->delivery_date
            ];

            // Handle document upload
            if ($request->hasFile('document')) {
                $transaction_data['document'] = $this->transactionService->uploadFile($request, 'document', 'documents');
            }

            // Create purchase order
            $purchase_order = Transaction::create($transaction_data);

            // Add purchase lines
            foreach ($request->purchase_lines as $line) {
                $purchase_order->purchase_lines()->create([
                    'product_id' => $line['product_id'],
                    'variation_id' => $line['variation_id'],
                    'quantity' => $line['quantity'],
                    'purchase_price' => $line['purchase_price'],
                    'tax_id' => $line['tax_id'],
                    'tax_amount' => $line['tax_amount'],
                    'item_tax' => $line['item_tax'],
                    'secondary_unit_quantity' => $line['secondary_unit_quantity'] ?? 0
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'data' => $purchase_order->load('purchase_lines', 'contact', 'location')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase order creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order'
            ], 500);
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show($id)
    {
        $purchase_order = Transaction::with([
            'purchase_lines',
            'purchase_lines.product',
            'purchase_lines.variations',
            'contact',
            'location',
            'tax'
        ])->findOrFail($id);

        return response()->json($purchase_order);
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $purchase_order = Transaction::findOrFail($id);
            $business_id = $request->session()->get('user.business_id');

            $update_data = [
                'contact_id' => $request->contact_id,
                'transaction_date' => $request->transaction_date,
                'total_before_tax' => $request->total_before_tax,
                'location_id' => $request->location_id,
                'final_total' => $request->final_total,
                'tax_id' => $request->tax_id,
                'tax_amount' => $request->tax_amount,
                'shipping_details' => $request->shipping_details,
                'shipping_charges' => $request->shipping_charges,
                'additional_notes' => $request->additional_notes,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'pay_term_number' => $request->pay_term_number,
                'pay_term_type' => $request->pay_term_type,
                'delivery_date' => $request->delivery_date
            ];

            // Handle document upload
            if ($request->hasFile('document')) {
                $update_data['document'] = $this->transactionService->uploadFile($request, 'document', 'documents');
            }

            // Update purchase order
            $purchase_order->update($update_data);

            // Update purchase lines
            $purchase_order->purchase_lines()->delete();
            foreach ($request->purchase_lines as $line) {
                $purchase_order->purchase_lines()->create([
                    'product_id' => $line['product_id'],
                    'variation_id' => $line['variation_id'],
                    'quantity' => $line['quantity'],
                    'purchase_price' => $line['purchase_price'],
                    'tax_id' => $line['tax_id'],
                    'tax_amount' => $line['tax_amount'],
                    'item_tax' => $line['item_tax'],
                    'secondary_unit_quantity' => $line['secondary_unit_quantity'] ?? 0
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'data' => $purchase_order->load('purchase_lines', 'contact', 'location')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase order update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase order'
            ], 500);
        }
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase_order = Transaction::findOrFail($id);
            $purchase_order->purchase_lines()->delete();
            $purchase_order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase order deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase order'
            ], 500);
        }
    }

    /**
     * Get purchase order statuses
     */
    public function getStatuses()
    {
        return response()->json([
            'statuses' => [
                'ordered' => 'Ordered',
                'partial' => 'Partially Received',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ]
        ]);
    }
} 