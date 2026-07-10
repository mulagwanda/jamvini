@extends('themes.default::layouts.admin')

@section('title', 'Social Accounts')
@section('breadcrumbs')<a href="{{ route('admin.social.index') }}">Social Centre</a> <span class="separator">/</span> <span class="current">Accounts</span>@endsection

@section('content')
<div class="page-header">
    <div><h1 class="page-title">Accounts</h1><p class="page-subtitle">V1 tracks channels manually. API connections can attach here later.</p></div>
    <a href="{{ route('admin.social.accounts.create') }}" class="btn btn-primary">Add Account</a>
</div>

<div class="dash-card">
    <table class="data-table">
        <thead><tr><th>Account</th><th>Platform</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($accounts as $account)
                <tr>
                    <td><strong>{{ $account->name }}</strong><br><small>{{ $account->handle ?: '-' }}</small></td>
                    <td>{{ $platforms[$account->platform] ?? ucfirst($account->platform) }}</td>
                    <td><span class="pill pill-{{ $account->status === 'connected' ? 'ok' : ($account->status === 'disabled' ? 'mute' : 'info') }}">{{ ucfirst($account->status) }}</span></td>
                    <td style="text-align:right;"><a href="{{ route('admin.social.accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="4" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No accounts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $accounts->links() }}
</div>
@endsection
