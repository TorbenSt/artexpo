<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'intro_text' => 'nullable|string',
            'text' => 'nullable|string',
            'artist' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'program_booklet' => 'nullable|url',
            'program_booklet_cover' => 'nullable|url',
            'flyer' => 'nullable|url',
            'flyer_cover' => 'nullable|url',
            'creative_booklet' => 'nullable|url',
            'creative_booklet_cover' => 'nullable|url',
            'ticket_link' => 'nullable|url',
        ];
    }
}
