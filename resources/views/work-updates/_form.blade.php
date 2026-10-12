@php
    $update = $update ?? null;
@endphp

<div class="row g-3">
    @if(isset($tasks))
        <div class="col-12 col-md-6">
            <label for="task_id" class="form-label">Task <span class="text-danger">*</span></label>
            <select id="task_id" name="task_id" class="form-select @error('task_id') is-invalid @enderror" required>
                <option value="">Select one of your tasks</option>
                @foreach($tasks as $task)
                    <option value="{{ $task->id }}" @selected((string) old('task_id') === (string) $task->id)>{{ $task->title }} (due {{ $task->due_date->format('d M Y') }})</option>
                @endforeach
            </select>
            @error('task_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($tasks->isEmpty())
                <div class="form-text">No tasks are assigned to you yet.</div>
            @endif
        </div>

        <div class="col-12 col-md-6">
            <label for="work_date" class="form-label">Work date <span class="text-danger">*</span></label>
            <input type="date" id="work_date" name="work_date" value="{{ old('work_date', now()->toDateString()) }}"
                   max="{{ now()->toDateString() }}" min="{{ now()->subDays($maxBackdate ?? 7)->toDateString() }}"
                   class="form-control @error('work_date') is-invalid @enderror" required>
            @error('work_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">You can record today or up to {{ $maxBackdate ?? 7 }} days back.</div>
        </div>
    @else
        <div class="col-12 col-md-6">
            <div class="small text-muted">Task</div>
            <div class="fw-semibold">{{ $update->task->title }}</div>
        </div>
        <div class="col-12 col-md-6">
            <div class="small text-muted">Work date</div>
            <div class="fw-semibold">{{ $update->work_date->format('d M Y') }}</div>
        </div>
    @endif

    <div class="col-12">
        <label for="completed_work" class="form-label">Completed work <span class="text-danger">*</span></label>
        <textarea id="completed_work" name="completed_work" rows="3" class="form-control @error('completed_work') is-invalid @enderror" maxlength="3000" required>{{ old('completed_work', $update->completed_work ?? '') }}</textarea>
        @error('completed_work')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="todays_progress" class="form-label">Today's progress <span class="text-danger">*</span></label>
        <textarea id="todays_progress" name="todays_progress" rows="3" class="form-control @error('todays_progress') is-invalid @enderror" maxlength="3000" required>{{ old('todays_progress', $update->todays_progress ?? '') }}</textarea>
        @error('todays_progress')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="pending_work" class="form-label">Pending work</label>
        <textarea id="pending_work" name="pending_work" rows="2" class="form-control @error('pending_work') is-invalid @enderror" maxlength="3000">{{ old('pending_work', $update->pending_work ?? '') }}</textarea>
        @error('pending_work')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="blockers" class="form-label">Blockers or issues</label>
        <textarea id="blockers" name="blockers" rows="2" class="form-control @error('blockers') is-invalid @enderror" maxlength="3000">{{ old('blockers', $update->blockers ?? '') }}</textarea>
        @error('blockers')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="progress_percentage" class="form-label">Progress (optional)</label>
        <div class="input-group">
            <input type="number" id="progress_percentage" name="progress_percentage" min="0" max="100" step="1"
                   value="{{ old('progress_percentage', $update->progress_percentage ?? '') }}"
                   class="form-control @error('progress_percentage') is-invalid @enderror">
            <span class="input-group-text">%</span>
            @error('progress_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
