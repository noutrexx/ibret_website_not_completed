<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'page_title' => 'nullable|string|max:255',
            'page_description' => 'nullable|string',
            'page_keywords' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ];
    }
}
