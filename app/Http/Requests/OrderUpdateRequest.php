<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'integer|exists:users,id',
            'status_id' => 'integer|exists:order_statuses,id',
            'customer_name' => 'string',
            'customer_email' => 'string|email',
            'customer_phone' => [
                'string',
                "regex:/^\s*(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})(?: *x(\d+))?\s*$/",
            ],
            'address' => 'string',
            'city' => 'string',
            'postcode' => 'string',
            'customer_note' => 'string',
        ];
    }
}
