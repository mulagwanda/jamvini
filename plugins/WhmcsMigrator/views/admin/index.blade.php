@extends('themes.default::layouts.admin')

@section('title', 'WHMCS Migrator')
@section('breadcrumbs')<span class="current">WHMCS Migrator</span>@endsection

@section('content')
<div class="page-header"><div><h1 class="page-title">WHMCS Migrator</h1><p class="page-subtitle">Upload WHMCS exports, inspect detected records, and prepare safe imports.</p></div></div>

<div class="dash-card" style="margin-bottom:1.5rem;">
    <div class="dash-card-head"><h3>{{ jv_icon('upload', '', 18) }} New Migration Batch</h3></div>
    <form action="{{ route('admin.whmcs-migrator.upload') }}" method="POST" enctype="multipart/form-data" style="display:grid;gap:12px;">
        @csrf
        <div class="form-group"><label class="form-label">Batch Name</label><input name="name" class="form-input" required placeholder="WHMCS July 2026 Export"></div>
        <div class="form-group"><label class="form-label">Source Type</label><select name="source_type" class="form-select"><option value="archive">WHMCS archive</option><option value="csv">CSV files</option><option value="json">JSON export</option></select></div>
        <div class="form-group"><label class="form-label">Export File</label><input type="file" name="file" class="form-input" required></div>
        <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-textarea" rows="3"></textarea></div>
        <div><button class="btn btn-primary">Upload Batch</button></div>
    </form>
</div>

<div class="dash-card" style="padding:0;overflow:hidden;">
    <table class="table" style="margin:0;">
        <thead><tr><th>Batch</th><th>Status</th><th>Detected</th><th>Created</th><th></th></tr></thead>
        <tbody>
            @forelse($batches as $batch)
                <tr>
                    <td><strong>{{ $batch->name }}</strong><br><small>{{ $batch->source_type }}</small></td>
                    <td><span class="pill pill-info">{{ ucfirst($batch->status) }}</span></td>
                    <td>{{ implode(', ', $batch->summary['detected'] ?? []) ?: '-' }}</td>
                    <td>{{ $batch->created_at?->format('M d, Y H:i') }}</td>
                    <td><form action="{{ route('admin.whmcs-migrator.analyze', $batch) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-primary">Analyze</button></form></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--jv-gray-500);">No migration batches yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $batches->links() }}
@endsection
