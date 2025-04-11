<?php

namespace App\Http\Requests\Moniepoint;

use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'metadata' => 'sometimes|array',
            'metadata.*' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'The refund amount is required',
            'amount.numeric' => 'The refund amount must be a number',
            'amount.min' => 'The refund amount must be greater than 0',
            'reason.required' => 'The refund reason is required',
            'reason.max' => 'The refund reason must not exceed 255 characters',
        ];
    }
} 