<?php

namespace App\Http\Requests\Suregifts;

use Illuminate\Foundation\Http\FormRequest;

class CreateGiftCardRequest extends FormRequest
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
            'quantity' => 'required|integer|min:1|max:100',
            'type' => 'required|string|in:physical,virtual',
            'expiry_date' => 'required|date|after:today',
            'recipient_email' => 'required|email',
            'recipient_name' => 'required|string|max:255',
            'message' => 'sometimes|string|max:500',
            'metadata' => 'sometimes|array',
            'metadata.*' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'The gift card amount is required',
            'amount.numeric' => 'The gift card amount must be a number',
            'amount.min' => 'The gift card amount must be greater than 0',
            'currency.required' => 'The currency is required',
            'currency.size' => 'The currency must be 3 characters',
            'quantity.required' => 'The quantity is required',
            'quantity.integer' => 'The quantity must be an integer',
            'quantity.min' => 'The quantity must be at least 1',
            'quantity.max' => 'The quantity must not exceed 100',
            'type.required' => 'The gift card type is required',
            'type.in' => 'The gift card type must be either physical or virtual',
            'expiry_date.required' => 'The expiry date is required',
            'expiry_date.date' => 'The expiry date must be a valid date',
            'expiry_date.after' => 'The expiry date must be in the future',
            'recipient_email.required' => 'The recipient email is required',
            'recipient_email.email' => 'The recipient email must be a valid email address',
            'recipient_name.required' => 'The recipient name is required',
            'message.max' => 'The message must not exceed 500 characters',
        ];
    }
} 