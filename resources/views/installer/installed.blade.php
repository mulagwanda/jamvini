@extends('installer.layout')

@section('content')
<div style="text-align: center;">
    <div style="font-size: 56px; margin-bottom: 16px; color: #6C5CE7;">{{ jv_icon('check-circle', '', 56) }}</div>
    <h2 style="margin-bottom: 8px;">JamVini Is Already Installed</h2>
    <p style="color: #64748b; margin-bottom: 28px; line-height: 1.6;">The installer is locked to protect this installation. Use the admin panel to manage your hosting business.</p>

    <div class="action-grid">
        <a href="/admin/login" class="btn btn-primary">{{ jv_icon('monitor', '', 16) }} Open Admin Panel</a>
        <a href="/" class="btn btn-secondary">{{ jv_icon('home', '', 16) }} Visit Site</a>
    </div>
</div>
@endsection
