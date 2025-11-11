<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization should be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exhibition_id' => 'required|exists:exhibitions,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'type' => 'required|in:public,press',
            'position' => 'nullable|string|max:50',
            'credits' => 'nullable|string',
            'visible' => 'boolean',
        ];
    }
}
