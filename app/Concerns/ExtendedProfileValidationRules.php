<?php

namespace App\Concerns;

trait ExtendedProfileValidationRules
{
    /**
     * Validation rules for optional extended profile fields.
     *
     * @return array<string, array<int, string>>
     */
    protected function extendedProfileRules(bool $includeRemoveAvatar = true): array
    {
        $rules = [
            'phone' => ['nullable', 'string', 'max:50'],
            'socials' => ['nullable', 'array'],
            'socials.reddit' => ['nullable', 'url', 'max:255'],
            'socials.linkedin' => ['nullable', 'url', 'max:255'],
            'socials.facebook' => ['nullable', 'url', 'max:255'],
            'socials.instagram' => ['nullable', 'url', 'max:255'],
            'socials.website' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($includeRemoveAvatar) {
            $rules['remove_avatar'] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}
