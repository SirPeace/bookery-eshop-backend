<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $this->merge([
            'slug' => Str::slug($this->slug)
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
            'parent_id' => 'integer|exists:categories,id',
            'title' => 'string|min:5',
            'slug' => 'string|min:5|unique:categories,slug',
            // JSON array: len(3+), item.len(3+), item.chars(a-zA-Z1-9-_\s)
            'keywords' => 'string|regex:/\[(("[a-zA-Z1-9-_\s]{3,}"),?){3,}\]/',
            'description' => 'string|min:10'
        ];
    }
}
