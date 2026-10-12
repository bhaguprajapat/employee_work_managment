<?php

namespace App\Http\Requests;

use App\Models\DailyWorkUpdate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var DailyWorkUpdate $update */
        $update = $this->route('workUpdate');

        return $this->user()->can('update', $update);
    }

    /** Task and work date are fixed once submitted; only the content can change. */
    public function rules(): array
    {
        return [
            'completed_work' => ['required', 'string', 'max:3000'],
            'todays_progress' => ['required', 'string', 'max:3000'],
            'pending_work' => ['nullable', 'string', 'max:3000'],
            'blockers' => ['nullable', 'string', 'max:3000'],
            'progress_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
