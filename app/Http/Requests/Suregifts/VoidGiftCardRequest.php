<?php

namespace App\Http\Requests\Suregifts;

use Illuminate\Foundation\Http\FormRequest;

class VoidGiftCardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reason' => 'required|string|max:255',
            'metadata' => 'sometimes|array',
            'metadata.*' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => 'The void reason is required',
            'reason.max' => 'The void reason must not exceed 255 characters',
        ];
    }
} 