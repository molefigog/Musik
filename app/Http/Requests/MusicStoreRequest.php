<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MusicStoreRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'file_src' => ['nullable', 'string'],
            'is_sold' => ['required', 'boolean'],
            'file_src' => ['nullable', 'file', 'max:15024'],
            'price' => ['required'],
            'release_id' => ['required', 'integer', 'exists:releases,id'],
        ];
    }
}
