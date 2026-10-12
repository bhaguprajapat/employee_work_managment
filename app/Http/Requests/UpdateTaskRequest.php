<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task $task */
        $task = $this->route('task');

        return $this->user()->can('update', $task);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists(User::class, 'id')->where('role', UserRole::Employee->value),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $assignee = User::find($this->integer('assigned_to'));
            $actor = $this->user();

            if ($assignee && ! $actor->managesEmployee($assignee)) {
                $validator->errors()->add('assigned_to', 'You can only assign tasks to employees in your team.');
            }
        });
    }
}
