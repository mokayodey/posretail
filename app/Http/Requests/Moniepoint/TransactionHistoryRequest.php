<?php

namespace App\Http\Requests\Moniepoint;

use Illuminate\Foundation\Http\FormRequest;

class TransactionHistoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'status' => 'sometimes|string|in:pending,successful,failed',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'reference' => 'sometimes|string|max:255',
            'customer_email' => 'sometimes|email',
        ];
    }

    public function messages()
    {
        return [
            'start_date.date' => 'The start date must be a valid date',
            'end_date.date' => 'The end date must be a valid date',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date',
            'status.in' => 'The status must be one of: pending, successful, failed',
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'per_page.integer' => 'The per page must be an integer',
            'per_page.min' => 'The per page must be at least 1',
            'per_page.max' => 'The per page must not exceed 100',
            'reference.max' => 'The reference must not exceed 255 characters',
            'customer_email.email' => 'The customer email must be a valid email address',
        ];
    }
} 