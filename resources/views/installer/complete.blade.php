@extends('installer.layout')

@section('content')
<div style="text-align: center;">
    <div style="font-size: 64px; margin-bottom: 16px; color: #047857;">{{ jv_icon('check-circle', '', 64) }}</div>
    <h2 style="margin-bottom: 8px;">Installation Complete!</h2>
    <p style="color: #64748b; margin-bottom: 32px;">JamVini has been installed successfully. You can now sign in and finish configuring your hosting business.</p>
    
    <div class="action-grid">
        <a href="/admin/login" class="btn btn-primary">{{ jv_icon('monitor', '', 16) }} Open Admin Panel</a>
        <a href="/" class="btn btn-secondary">{{ jv_icon('home', '', 16) }} Visit Site</a>
    </div>
</div>
@endsection
