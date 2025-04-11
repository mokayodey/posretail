<?php

namespace App\Http\Requests\Suregifts;

use Illuminate\Foundation\Http\FormRequest;

class ListGiftCardsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'sometimes|string|in:active,redeemed,expired,voided',
            'type' => 'sometimes|string|in:physical,virtual',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'recipient_email' => 'sometimes|email',
            'amount_min' => 'sometimes|numeric|min:0',
            'amount_max' => 'sometimes|numeric|min:0|gte:amount_min',
        ];
    }

    public function messages()
    {
        return [
            'status.in' => 'The status must be one of: active, redeemed, expired, voided',
            'type.in' => 'The type must be one of: physical, virtual',
            'start_date.date' => 'The start date must be a valid date',
            'end_date.date' => 'The end date must be a valid date',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date',
            'page.integer' => 'The page must be an integer',
            'page.min' => 'The page must be at least 1',
            'per_page.integer' => 'The per page must be an integer',
            'per_page.min' => 'The per page must be at least 1',
            'per_page.max' => 'The per page must not exceed 100',
            'recipient_email.email' => 'The recipient email must be a valid email address',
            'amount_min.numeric' => 'The minimum amount must be a number',
            'amount_min.min' => 'The minimum amount must be greater than 0',
            'amount_max.numeric' => 'The maximum amount must be a number',
            'amount_max.min' => 'The maximum amount must be greater than 0',
            'amount_max.gte' => 'The maximum amount must be greater than or equal to the minimum amount',
        ];
    }
} 