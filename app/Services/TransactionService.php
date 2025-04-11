<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class TransactionService
{
    /**
     * Upload a file and return the file name
     */
    public function uploadFile(Request $request, $field_name, $folder)
    {
        if ($request->hasFile($field_name)) {
            $file = $request->file($field_name);
            $file_name = time() . '_' . $file->getClientOriginalName();
            
            // Store the file
            $path = $file->storeAs($folder, $file_name, 'public');
            
            return $file_name;
        }
        
        return null;
    }

    /**
     * Calculate transaction totals
     */
    public function calculateTotals($purchase_lines, $tax_id = null, $tax_rate = 0)
    {
        $total_before_tax = 0;
        $tax_amount = 0;
        $final_total = 0;

        foreach ($purchase_lines as $line) {
            $line_total = $line['quantity'] * $line['purchase_price'];
            $total_before_tax += $line_total;

            if ($tax_id && $tax_rate > 0) {
                $line_tax = ($line_total * $tax_rate) / 100;
                $tax_amount += $line_tax;
            }
        }

        $final_total = $total_before_tax + $tax_amount;

        return [
            'total_before_tax' => $total_before_tax,
            'tax_amount' => $tax_amount,
            'final_total' => $final_total
        ];
    }

    /**
     * Generate reference number for a transaction
     */
    public function generateReferenceNumber($type, $business_id)
    {
        $prefix = '';
        switch ($type) {
            case 'purchase_order':
                $prefix = 'PO';
                break;
            case 'purchase':
                $prefix = 'PUR';
                break;
            case 'purchase_return':
                $prefix = 'PR';
                break;
            case 'sell':
                $prefix = 'SL';
                break;
            case 'sell_return':
                $prefix = 'SR';
                break;
            default:
                $prefix = 'TR';
        }

        $count = Transaction::where('business_id', $business_id)
            ->where('type', $type)
            ->count();

        return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update product stock
     */
    public function updateProductStock($product_id, $variation_id, $quantity, $type = 'add')
    {
        $product = Product::findOrFail($product_id);
        $variation = $product->variations()->findOrFail($variation_id);

        if ($type === 'add') {
            $variation->increment('qty_available', $quantity);
        } else {
            $variation->decrement('qty_available', $quantity);
        }

        return true;
    }

    /**
     * Validate transaction data
     */
    public function validateTransactionData(Request $request, $type)
    {
        $rules = [
            'contact_id' => 'required|exists:contacts,id',
            'transaction_date' => 'required|date',
            'location_id' => 'required|exists:business_locations,id',
            'purchase_lines' => 'required|array|min:1',
            'purchase_lines.*.product_id' => 'required|exists:products,id',
            'purchase_lines.*.variation_id' => 'required|exists:variations,id',
            'purchase_lines.*.quantity' => 'required|numeric|min:0.01',
            'purchase_lines.*.purchase_price' => 'required|numeric|min:0',
            'document' => 'nullable|file|max:'.(config('constants.document_size_limit') / 1000)
        ];

        if ($type === 'purchase_order') {
            $rules['delivery_date'] = 'required|date|after_or_equal:transaction_date';
        }

        return $request->validate($rules);
    }
} 