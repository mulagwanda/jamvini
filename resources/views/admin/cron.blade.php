@extends('themes.default::layouts.admin')

@section('title', 'Cron Manager')
@section('breadcrumbs')<span class="current">Cron Manager</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Cron Manager</h1>
        <p class="page-subtitle">Manage scheduled tasks and automation</p>
    </div>
    <form action="{{ route('admin.cron.run') }}" method="POST">
        @csrf
        <button class="btn btn-primary">{{ jv_icon('play', '', 16) }} Run All Tasks Now</button>
    </form>
</div>

@php
    $activeTasks = collect($tasks)->where('enabled', true)->count();
    $runningTasks = collect($tasks)->where('is_running', true)->count();
    $failedTasks = collect($tasks)->filter(fn($task) => ($task['last_result']['success'] ?? true) === false)->count();
@endphp

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-card-value">{{ count($tasks) }}</div>
        <div class="stat-card-label">Registered Tasks</div>
    </div>
    <div class="stat-card success">
        <div class="stat-card-value">{{ $activeTasks }}</div>
        <div class="stat-card-label">Active Tasks</div>
    </div>
    <div class="stat-card info">
        <div class="stat-card-value">{{ $runningTasks }}</div>
        <div class="stat-card-label">Running Now</div>
    </div>
    <div class="stat-card {{ $failedTasks > 0 ? 'danger' : 'success' }}">
        <div class="stat-card-value">{{ $failedTasks }}</div>
        <div class="stat-card-label">Recent Failures</div>
    </div>
</div>

{{-- How to Setup --}}
<div class="dash-card" style="margin-bottom: 1.5rem;">
    <div class="dash-card-head"><h3>{{ jv_icon('clipboard-list', '', 20) }} Setup Instructions</h3></div>
    <p style="color: var(--jv-gray-600); margin-bottom: 1rem;">Add one of these commands to your server's crontab to run scheduled tasks automatically every minute:</p>
    
    <div style="margin-bottom: 1rem;">
        <label class="form-label" style="font-size: 0.85rem;">Using wget (recommended):</label>
        <div style="background: #0F172A; color: #10b981; padding: 12px 16px; border-radius: 8px; font-family: monospace; font-size: 0.85rem; word-break: break-all;">
            {{ $cronCommand }}
        </div>
    </div>
    
    <div style="margin-bottom: 1rem;">
        <label class="form-label" style="font-size: 0.85rem;">Using curl:</label>
        <div style="background: #0F172A; color: #10b981; padding: 12px 16px; border-radius: 8px; font-family: monospace; font-size: 0.85rem; word-break: break-all;">
            {{ $curlCommand }}
        </div>
    </div>

    <div style="background: #fef3c7; border: 1px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin-top: 1rem;">
        <strong style="color: #92400e;">For cPanel/Shared Hosting:</strong>
        <ol style="margin: 8px 0 0 16px; color: #92400e; font-size: 0.9rem;">
            <li>Go to cPanel Cron Jobs</li>
            <li>Set "Common Settings" to "Once Per Minute (* * * *)"</li>
            <li>Paste the command above in the "Command" field</li>
            <li>Click "Add New Cron Job"</li>
        </ol>
    </div>

    <div style="margin-top: 1rem; display: flex; gap: 12px;">
        <form action="{{ route('admin.cron.regenerate') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-outline-warning">{{ jv_icon('refresh-cw', '', 16) }} Regenerate Key</button>
        </form>
        <span style="font-size: 0.85rem; color: var(--jv-gray-500); align-self: center; word-break: break-all;">
            Secure cron URL: <code>{{ $webUrl }}</code>
        </span>
    </div>
</div>

{{-- Task List --}}
<div class="dash-card" style="padding: 0; overflow: hidden;">
    <table class="table" style="margin: 0;">
        <thead><tr><th>Task</th><th>Schedule</th><th>Last Run</th><th>Next Run</th><th>Last Result</th><th>Status</th><th class="text-center">Action</th></tr></thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>
                    <strong>{{ $task['name'] }}</strong>
                    <div style="font-size: 0.8rem; color: var(--jv-gray-500);">{{ $task['description'] }}</div>
                </td>
                <td><span class="pill pill-info">{{ $task['schedule'] }}</span></td>
                <td>
                    {{ $task['last_run'] ? \Carbon\Carbon::parse($task['last_run'])->diffForHumans() : 'Never' }}
                    @if($task['last_run'])
                        <div style="font-size:.75rem;color:var(--jv-gray-500);">{{ jv_format_date(\Carbon\Carbon::parse($task['last_run'])) }}</div>
                    @endif
                </td>
                <td>
                    @if($task['enabled'] && $task['next_run'])
                        {{ \Carbon\Carbon::parse($task['next_run'])->diffForHumans() }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($task['last_result'])
                        @if($task['last_result']['success'] ?? false)
                            <span class="pill pill-ok">OK {{ $task['last_result']['duration'] ?? '' }}s</span>
                        @else
                            <span class="pill pill-bad">Failed</span>
                            <div style="font-size:.75rem;color:#dc2626;max-width:260px;">{{ $task['last_result']['error'] ?? 'Unknown error' }}</div>
                        @endif
                    @else
                        <span class="pill pill-mute">No result</span>
                    @endif
                </td>
                <td>
                    @if($task['is_running'])
                        <span class="pill pill-warn">Running...</span>
                    @elseif($task['enabled'])
                        <span class="pill pill-ok">Active</span>
                    @else
                        <span class="pill pill-mute">Disabled</span>
                    @endif
                </td>
                <td class="text-center">
                    <form action="{{ route('admin.cron.toggle', $task['name']) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm {{ $task['enabled'] ? 'btn-outline-danger' : 'btn-outline-primary' }}">
                            {{ $task['enabled'] ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if(count($tasks) === 0)
    <div class="empty-state" style="padding: 40px;">
        <div class="empty-state-icon">{{ jv_icon('clock', '', 42) }}</div>
        <div class="empty-state-title">No tasks registered</div>
        <p>Tasks will appear here when plugins register them.</p>
    </div>
    @endif
</div>
@endsection
