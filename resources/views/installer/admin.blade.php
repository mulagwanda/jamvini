@extends('installer.layout')

@section('content')
<h2 class="page-title">Admin Account</h2>
<p class="page-copy">Create the first administrator account. You will use this account to sign in to the JamVini admin panel.</p>

@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        Please check the highlighted details.
        <ul class="error-list">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" data-install-progress data-progress-title="Installing JamVini" data-progress-text="JamVini is creating tables, installing core plugins, and creating your admin account.">
    @csrf
    <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Super Admin" required>
    </div>
    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="admin@example.com" required>
    </div>
    <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Min 8 characters" required>
    </div>
    <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-input" required>
    </div>
    <div class="form-group">
        <label class="form-label">Company Name</label>
        <input type="text" name="company_name" class="form-input" value="{{ old('company_name') }}" placeholder="My Hosting Company">
    </div>
    <button type="submit" class="btn btn-primary btn-block">{{ jv_icon('check-circle', '', 16) }} Complete Installation</button>
</form>
@endsection
