@extends('themes.default::layouts.admin')

@section('title', 'SMS Notifications')
@section('breadcrumbs')<span class="current">SMS Notifications</span>@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h1 class="page-title">SMS Notifications</h1>
            <p class="page-subtitle">Send automated SMS alerts to clients</p>
        </div>
        <a href="{{ route('admin.sms.settings') }}" class="btn btn-primary">⚙️ Configure</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">📱 Configuration Status</h3></div>
    <div class="card-body">
        @if($config['is_configured'])
            <div class="alert alert-success">
                <span class="alert-icon">✅</span>
                <span>SMS provider configured — notifications are active.</span>
            </div>
        @else
            <div class="alert alert-warning">
                <span class="alert-icon">⚠️</span>
                <span>SMS not configured. Add your API key to enable notifications.</span>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">📊 Recent SMS Log</h3></div>
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead><tr><th>To</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log['to'] }}</td>
                    <td>{{ $log['message'] }}</td>
                    <td><span class="badge badge-{{ $log['status'] === 'sent' ? 'success' : 'danger' }}">{{ $log['status'] }}</span></td>
                    <td>{{ $log['date']->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection