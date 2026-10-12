@php
    $task = $task ?? new \App\Models\Task();
@endphp

<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" value="{{ old('title', $task->title) }}"
               class="form-control @error('title') is-invalid @enderror" maxlength="150" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="4"
                  class="form-control @error('description') is-invalid @enderror" maxlength="5000">{{ old('description', $task->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="assigned_to" class="form-label">Assign to <span class="text-danger">*</span></label>
        <select id="assigned_to" name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
            <option value="">Select an employee</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected((string) old('assigned_to', $task->assigned_to) === (string) $employee->id)>{{ $employee->name }}</option>
            @endforeach
        </select>
        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-3">
        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
        <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>
            @foreach($priorities as $priority)
                <option value="{{ $priority->value }}" @selected(old('priority', $task->priority?->value ?? 'medium') === $priority->value)>{{ $priority->label() }}</option>
            @endforeach
        </select>
        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $task->status?->value ?? 'todo') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-3">
        <label for="start_date" class="form-label">Start date <span class="text-danger">*</span></label>
        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', optional($task->start_date)->format('Y-m-d')) }}"
               class="form-control @error('start_date') is-invalid @enderror" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-3">
        <label for="due_date" class="form-label">Due date <span class="text-danger">*</span></label>
        <input type="date" id="due_date" name="due_date" value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}"
               class="form-control @error('due_date') is-invalid @enderror" required>
        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
