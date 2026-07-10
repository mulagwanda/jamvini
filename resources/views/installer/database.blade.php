@extends('installer.layout')

@section('content')
<h2 class="page-title">Database Configuration</h2>
<p class="page-copy">Enter the database created for this JamVini installation. The installer will test the connection before continuing.</p>

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

<form method="POST" data-install-progress data-progress-title="Testing database connection" data-progress-text="JamVini is checking the database credentials.">
    @csrf
    <div class="form-group">
        <label class="form-label">Database Host</label>
        <input type="text" name="db_host" class="form-input" value="{{ old('db_host', '127.0.0.1') }}" required>
        <div class="form-hint">Use the host provided by your hosting panel. On many servers this is localhost or 127.0.0.1.</div>
    </div>
    <div class="form-group">
        <label class="form-label">Database Port</label>
        <input type="text" name="db_port" class="form-input" value="{{ old('db_port', '3306') }}" required>
    </div>
    <div class="form-group">
        <label class="form-label">Database Name</label>
        <input type="text" name="db_database" class="form-input" value="{{ old('db_database') }}" placeholder="jamvini_hosting" required>
    </div>
    <div class="form-group">
        <label class="form-label">Database Username</label>
        <input type="text" name="db_username" class="form-input" value="{{ old('db_username') }}" required>
    </div>
    <div class="form-group">
        <label class="form-label">Database Password</label>
        <input type="password" name="db_password" class="form-input">
    </div>
    <button type="submit" class="btn btn-primary btn-block">{{ jv_icon('database', '', 16) }} Test Connection and Continue</button>
</form>
@endsection
