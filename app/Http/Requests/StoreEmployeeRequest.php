<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Admins may assign any role and manager. Managers may only create employees
     * placed under themselves; the controller enforces that on the way in.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'manager_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')->where('role', UserRole::Manager->value)],
            'job_title' => ['nullable', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
