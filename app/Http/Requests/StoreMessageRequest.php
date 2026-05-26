<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_slug' => is_string($this->input('category_slug'))
                ? trim($this->input('category_slug'))
                : $this->input('category_slug'),
            'body' => is_string($this->input('body'))
                ? trim($this->input('body'))
                : $this->input('body'),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_slug' => ['required', 'string', 'exists:categories,slug'],
            'body'          => ['required', 'string', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required'         => 'The message body cannot be empty.',
            'body.min'              => 'The message body cannot be empty.',
            'category_slug.exists'  => 'The selected category is invalid.',
        ];
    }
}
