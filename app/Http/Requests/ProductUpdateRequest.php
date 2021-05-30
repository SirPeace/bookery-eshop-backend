<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'string|min:10',
            'category_id' => 'integer|exists:product_categories,id',
            'price' => 'numeric|min:0',
            'discount' => 'numeric|min:0|max:75',
            'description' => 'string|min:30',
            'keywords' => 'json',
        ];
    }
}
