<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'caption' => ['required', 'string', 'max:2000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
