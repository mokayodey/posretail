<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Contact;
use App\Models\Product;
use App\Models\BusinessLocation;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseRequisitionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of purchase requisitions
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_requisition')
            ->with(['location', 'purchase_lines', 'purchase_lines.product', 'sales_person']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
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

        if ($request->has('required_by_start') && $request->has('required_by_end')) {
            $query->whereBetween('delivery_date', [
                $request->required_by_start,
                $request->required_by_end
            ]);
        }

        // Filter by user permissions
        if (!auth()->user()->can('purchase_requisition.view_all') && auth()->user()->can('purchase_requisition.view_own')) {
            $query->where('created_by', auth()->id());
        }

        $purchase_requisitions = $query->latest()->paginate(10);

        $locations = BusinessLocation::forDropdown($business_id);

        return response()->json([
            'purchase_requisitions' => $purchase_requisitions,
            'locations' => $locations
        ]);
    }

    /**
     * Store a newly created purchase requisition
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $transaction_data = [
                'business_id' => $business_id,
                'type' => 'purchase_requisition',
                'status' => 'ordered',
                'location_id' => $request->location_id,
                'transaction_date' => now(),
                'created_by' => $user_id,
                'delivery_date' => $request->delivery_date,
                'ref_no' => $request->ref_no ?? $this->transactionService->generateReferenceNumber('purchase_requisition', $business_id)
            ];

            // Create purchase requisition
            $purchase_requisition = Transaction::create($transaction_data);

            // Add purchase lines
            foreach ($request->purchases as $line) {
                if (!empty($line['quantity']) || !empty($line['secondary_unit_quantity'])) {
                    $purchase_requisition->purchase_lines()->create([
                        'product_id' => $line['product_id'],
                        'variation_id' => $line['variation_id'],
                        'quantity' => $line['quantity'] ?? 0,
                        'secondary_unit_quantity' => $line['secondary_unit_quantity'] ?? 0,
                        'purchase_price' => 0,
                        'item_tax' => 0
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase requisition created successfully',
                'data' => $purchase_requisition->load('purchase_lines', 'location', 'sales_person')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase requisition creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase requisition'
            ], 500);
        }
    }

    /**
     * Display the specified purchase requisition
     */
    public function show($id)
    {
        $purchase_requisition = Transaction::with([
            'purchase_lines',
            'purchase_lines.product',
            'purchase_lines.product.unit',
            'purchase_lines.product.second_unit',
            'purchase_lines.variations',
            'purchase_lines.variations.product_variation',
            'location',
            'sales_person'
        ])->findOrFail($id);

        return response()->json($purchase_requisition);
    }

    /**
     * Update the specified purchase requisition
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $purchase_requisition = Transaction::findOrFail($id);
            $business_id = $request->session()->get('user.business_id');

            $update_data = [
                'location_id' => $request->location_id,
                'delivery_date' => $request->delivery_date,
                'status' => $request->status
            ];

            // Update purchase requisition
            $purchase_requisition->update($update_data);

            // Update purchase lines
            $purchase_requisition->purchase_lines()->delete();
            foreach ($request->purchases as $line) {
                if (!empty($line['quantity']) || !empty($line['secondary_unit_quantity'])) {
                    $purchase_requisition->purchase_lines()->create([
                        'product_id' => $line['product_id'],
                        'variation_id' => $line['variation_id'],
                        'quantity' => $line['quantity'] ?? 0,
                        'secondary_unit_quantity' => $line['secondary_unit_quantity'] ?? 0,
                        'purchase_price' => 0,
                        'item_tax' => 0
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase requisition updated successfully',
                'data' => $purchase_requisition->load('purchase_lines', 'location', 'sales_person')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase requisition update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase requisition'
            ], 500);
        }
    }

    /**
     * Remove the specified purchase requisition
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase_requisition = Transaction::findOrFail($id);
            $purchase_requisition->purchase_lines()->delete();
            $purchase_requisition->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase requisition deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase requisition deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase requisition'
            ], 500);
        }
    }

    /**
     * Get purchase requisition statuses
     */
    public function getStatuses()
    {
        return response()->json([
            'statuses' => [
                'ordered' => 'Ordered',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ]
        ]);
    }
} 