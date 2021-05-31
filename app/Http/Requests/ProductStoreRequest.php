<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|min:10',
            'category_id' => 'required|integer|exists:product_categories,id',
            'price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:75',
            'description' => 'required|string|min:30',
            'keywords' => 'required|json',
        ];
    }
}
