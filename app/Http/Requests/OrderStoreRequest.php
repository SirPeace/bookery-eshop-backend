<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id()
        ]);
    }

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
            'customer_name' => 'required|string',
            'customer_email' => 'required|string|email',
            'customer_phone' => [
                'required',
                'string',
                'regex:/^\s*(?:\+?(\d{1,3}))?[-. (]*(\d{3})[-. )]*(\d{3})[-. ]*(\d{4})(?: *x(\d+))?\s*$/',
            ],
            'address' => 'required|string',
            'city' => 'required|string',
            'postcode' => 'required',
            'customer_note' => 'string',
        ];
    }
}
