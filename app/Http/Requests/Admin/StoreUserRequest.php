<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ExtendedProfileValidationRules;
use App\RoleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use ExtendedProfileValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ...$this->extendedProfileRules(includeRemoveAvatar: false),
            'role' => ['required', 'string', Rule::in([
                RoleType::ADMIN->value,
                RoleType::EMPLOYEE->value,
            ])],
        ];
    }
}
