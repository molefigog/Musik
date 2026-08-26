<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MusicUpdateRequest extends FormRequest
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
            'title' => ['string'],
            'file_src' => ['nullable', 'string'],
            'is_sold' => ['boolean'],
            'file_src' => ['nullable', 'file', 'max:15024'],
            'price' => ['nullable'],
        ];
    }
}
