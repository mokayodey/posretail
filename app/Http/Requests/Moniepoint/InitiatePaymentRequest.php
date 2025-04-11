<?php

namespace App\Http\Requests\Moniepoint;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_name' => 'required|string|max:255',
            'callback_url' => 'required|url',
            'metadata' => 'sometimes|array',
            'metadata.*' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'The payment amount is required',
            'amount.numeric' => 'The payment amount must be a number',
            'amount.min' => 'The payment amount must be greater than 0',
            'currency.required' => 'The currency is required',
            'currency.size' => 'The currency must be 3 characters',
            'description.required' => 'The payment description is required',
            'customer_email.required' => 'The customer email is required',
            'customer_email.email' => 'The customer email must be a valid email address',
            'customer_name.required' => 'The customer name is required',
            'callback_url.required' => 'The callback URL is required',
            'callback_url.url' => 'The callback URL must be a valid URL',
        ];
    }
} 