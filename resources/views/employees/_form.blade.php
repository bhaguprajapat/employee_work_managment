@php
    // Create page passes no employee, so fall back to an unsaved instance.
    $employee = $employee ?? new \App\Models\User();
    $isEdit = $employee->exists;
    $actor = auth()->user();
@endphp

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $employee->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" maxlength="120" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" value="{{ old('email', $employee->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror" maxlength="190" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="password" class="form-label">
            Password {!! $isEdit ? '<span class="text-muted small">(leave blank to keep current)</span>' : '<span class="text-danger">*</span>' !!}
        </label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" {{ $isEdit ? '' : 'required' }}>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">At least 8 characters, with upper and lower case letters and a number.</div>
    </div>

    <div class="col-12 col-md-6">
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control"
               {{ $isEdit ? '' : 'required' }}>
    </div>

    <div class="col-12 col-md-6">
        <label for="job_title" class="form-label">Job title</label>
        <input type="text" id="job_title" name="job_title" value="{{ old('job_title', $employee->job_title ?? '') }}"
               class="form-control @error('job_title') is-invalid @enderror" maxlength="120">
        @error('job_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if($actor->isAdmin())
        <div class="col-12 col-md-6">
            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                @foreach($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $employee->role?->value ?? 'employee') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-6">
            <label for="manager_id" class="form-label">Reporting manager</label>
            <select id="manager_id" name="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
                <option value="">No manager</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" @selected((string) old('manager_id', $employee->manager_id ?? '') === (string) $manager->id)>{{ $manager->name }}</option>
                @endforeach
            </select>
            @error('manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @else
        <div class="col-12">
            <div class="alert alert-light border small mb-0 py-2">
                This account will be an employee reporting to you.
            </div>
        </div>
    @endif

    <div class="col-12">
        {{-- Hidden 0 ensures an unticked box submits false. --}}
        <input type="hidden" name="is_active" value="0">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                   @checked(old('is_active', $employee->is_active ?? true))>
            <label class="form-check-label" for="is_active">Account is active (can sign in)</label>
        </div>
    </div>
</div>
