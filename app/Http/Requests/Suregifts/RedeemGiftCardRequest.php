<?php

namespace App\Http\Requests\Suregifts;

use Illuminate\Foundation\Http\FormRequest;

class RedeemGiftCardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0',
            'pin' => 'required|string|size:4',
            'description' => 'required|string|max:255',
            'metadata' => 'sometimes|array',
            'metadata.*' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'The redemption amount is required',
            'amount.numeric' => 'The redemption amount must be a number',
            'amount.min' => 'The redemption amount must be greater than 0',
            'pin.required' => 'The PIN is required',
            'pin.size' => 'The PIN must be exactly 4 characters',
            'description.required' => 'The redemption description is required',
            'description.max' => 'The redemption description must not exceed 255 characters',
        ];
    }
} 