@if (session('success'))
    <div class="alert alert-success py-2 alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger py-2 alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('status'))
    <div class="alert alert-info py-2" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
    <div class="alert alert-danger py-2" role="alert">
        Please correct the highlighted fields and try again.
    </div>
@endif
