<?php

namespace App\Http\Requests;

use App\Models\DailyWorkUpdate;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreWorkUpdateRequest extends FormRequest
{
    /** Work updates may be backfilled up to this many days. */
    public const MAX_BACKDATE_DAYS = 7;

    public function authorize(): bool
    {
        return $this->user()->can('create', DailyWorkUpdate::class);
    }

    public function rules(): array
    {
        $today = Carbon::today()->toDateString();
        $earliest = Carbon::today()->subDays(self::MAX_BACKDATE_DAYS)->toDateString();

        return [
            'task_id' => ['required', 'integer', Rule::exists(Task::class, 'id')],
            'work_date' => [
                'required',
                'date',
                "after_or_equal:{$earliest}",
                "before_or_equal:{$today}",
                Rule::unique(DailyWorkUpdate::class)->where(
                    fn ($query) => $query
                        ->where('task_id', $this->integer('task_id'))
                        ->where('employee_id', $this->user()->id)
                ),
            ],
            'completed_work' => ['required', 'string', 'max:3000'],
            'todays_progress' => ['required', 'string', 'max:3000'],
            'pending_work' => ['nullable', 'string', 'max:3000'],
            'blockers' => ['nullable', 'string', 'max:3000'],
            'progress_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * The task must be assigned to the submitting employee. Checked here so the
     * error appears on the form and the request never reaches the database.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $task = Task::find($this->integer('task_id'));

            if ($task && $task->assigned_to !== $this->user()->id) {
                $validator->errors()->add('task_id', 'You can only submit updates for tasks assigned to you.');
            }
        });
    }
}
