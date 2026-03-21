<?php

namespace App\Http\Requests;

use App\Rules\NotReservedSlug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUrlRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => 'required|url|max:2048',
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'short_code' => [
                'nullable',
                'string',
                'min:3',
                'max:20',
                'alpha_dash',
                'unique:urls,short_code',
                new NotReservedSlug,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'The URL field is required.',
            'url.url' => 'The URL must be a valid URL.',
            'url.max' => 'The URL may not be greater than 2048 characters.',
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 50 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 500 characters.',
            'is_active.boolean' => 'The is_active field must be true or false.',
            'short_code.string' => 'The shortcode must be a string.',
            'short_code.min' => 'The short code must be at least 3 characters.',
            'short_code.max' => 'The short code may not be greater than 20 characters.',
            'short_code.alpha_dash' => 'The short code may only contain letters, numbers, dashes, and underscores.',
            'short_code.unique' => 'This short code is already taken.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
