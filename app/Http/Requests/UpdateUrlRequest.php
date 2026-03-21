<?php

namespace App\Http\Requests;

use App\Models\Url;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUrlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = $this->attributes->get('user_details')['id'];

        $urlId = $this->route('url');

        return Url::where('id', $urlId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => 'sometimes|url|max:2048',
            'title' => 'sometimes|string|max:50',
            'description' => 'sometimes|string|max:500',
            'is_active' => 'sometimes|boolean',
            'expires_at' => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }
}
