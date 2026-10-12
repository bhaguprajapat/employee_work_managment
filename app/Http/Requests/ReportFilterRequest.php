<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validates the shared filter query string used by dashboard and performance pages. */
class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', Rule::exists(User::class, 'id')->where('role', UserRole::Employee->value)],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }
}
