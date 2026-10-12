<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $employee */
        $employee = $this->route('employee');

        return $this->user()->can('update', $employee);
    }

    public function rules(): array
    {
        /** @var User $employee */
        $employee = $this->route('employee');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190', Rule::unique(User::class)->ignore($employee->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'manager_id' => [
                'nullable',
                'integer',
                Rule::exists(User::class, 'id')->where('role', UserRole::Manager->value),
                Rule::notIn([$employee->id]),
            ],
            'job_title' => ['nullable', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
