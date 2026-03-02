<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'tags' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'image_path' => 'required|string|max:1000',
            'type' => 'required|in:normal,manset,surmanset,top_manset,spor_manset,ekonomi_manset,gizli',
            'is_breaking' => 'nullable|boolean',
            'photo_gallery' => 'nullable|array|max:50',
            'photo_gallery.*' => 'nullable|url|max:2048',
            'video_gallery' => 'nullable|array|max:30',
            'video_gallery.*' => 'nullable|url|max:2048',
            'city' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ];
    }
}
