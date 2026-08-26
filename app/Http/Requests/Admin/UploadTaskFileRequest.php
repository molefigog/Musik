<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadTaskFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust to however you gate admin access elsewhere (middleware,
        // gate, role column, etc). This assumes an `is_admin` boolean on User.
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $serviceType = $this->route('task')?->service_type;

        $audioRule = ['file', 'mimes:mp3,wav', 'max:51200']; // 50MB
        $imageRule = ['file', 'mimes:jpg,jpeg,png,webp', 'max:15360']; // 15MB

        $fileRule = in_array($serviceType, ['beat', 'recording'])
            ? $audioRule
            : $imageRule;

        return [
            'file' => array_merge(['sometimes', 'required_without:preview'], $fileRule),
            'preview' => array_merge(['sometimes', 'nullable'], $fileRule),
        ];
    }
}
