<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            'phone' => ['nullable', 'string', 'max:50'],
            'socials' => ['nullable', 'array'],
            'socials.reddit' => ['nullable', 'url', 'max:255'],
            'socials.linkedin' => ['nullable', 'url', 'max:255'],
            'socials.facebook' => ['nullable', 'url', 'max:255'],
            'socials.instagram' => ['nullable', 'url', 'max:255'],
            'socials.website' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (!$validator->errors()->has('avatar')) {
                return;
            }

            $file = $this->file('avatar');

            Log::warning('Profile update avatar validation failed', [
                'user_id' => $this->user()?->id,
                'method' => $this->method(),
                'content_type' => $this->header('content-type'),
                'has_avatar_key' => $this->has('avatar'),
                'has_file_avatar' => $this->hasFile('avatar'),
                'avatar_is_valid' => $file?->isValid(),
                'avatar_upload_error' => $file?->getError(),
                'avatar_upload_error_message' => $file?->getErrorMessage(),
                'avatar_original_name' => $file?->getClientOriginalName(),
                'avatar_mime' => $file?->getClientMimeType(),
                'avatar_size' => $file?->getSize(),
                'validation_errors' => $validator->errors()->get('avatar'),
            ]);
        });
    }
}
