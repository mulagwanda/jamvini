@extends('themes.default::layouts.admin')

@section('title', 'Reports')
@section('breadcrumbs')<span class="current">Reports</span>@endsection

@section('content')
<style>
.reports-hero { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; margin-bottom:22px; }
.reports-library { display:grid; gap:22px; }
.reports-group { display:grid; gap:12px; }
.reports-group-head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
.reports-group-head h2 { margin:0; font-size:1rem; font-weight:800; color:var(--jv-gray-900); }
.reports-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:14px; }
.report-link { display:grid; grid-template-columns:auto 1fr auto; gap:14px; align-items:start; background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; padding:16px; color:inherit; text-decoration:none; box-shadow:0 1px 3px rgba(15,23,42,.04); transition:.18s ease; }
.report-link:hover { border-color:var(--jv-primary); transform:translateY(-1px); box-shadow:0 8px 22px rgba(15,23,42,.08); }
.report-ico { width:42px; height:42px; border-radius:8px; display:grid; place-items:center; background:var(--jv-primary-bg); color:var(--jv-primary); }
.report-link h3 { margin:0 0 5px; font-size:.98rem; color:var(--jv-gray-900); }
.report-link p { margin:0; color:var(--jv-gray-600); font-size:.86rem; line-height:1.45; }
.report-arrow { color:var(--jv-gray-400); margin-top:8px; }
</style>

<div class="reports-hero">
    <div>
        <h1 class="page-title">Reports</h1>
        <p class="page-subtitle">One reporting center for billing, clients, domains, services, support, marketing, and plugin-provided insights.</p>
    </div>
    <a href="{{ url('/admin/dashboard') }}" class="btn btn-outline-primary">{{ jv_icon('layout-dashboard', '', 16) }} Dashboard</a>
</div>

@if(empty($groups))
    <div class="empty-state">
        <div class="empty-state-icon">{{ jv_icon('bar-chart-3', '', 42) }}</div>
        <div class="empty-state-title">No reports registered</div>
        <div class="empty-state-desc">Activate the Reports plugin or install plugins that provide reports.</div>
    </div>
@else
    <div class="reports-library">
        @foreach($groups as $category => $reports)
            <section class="reports-group">
                <div class="reports-group-head">
                    <h2>{{ $category }}</h2>
                    <span class="badge badge-gray">{{ count($reports) }} report{{ count($reports) === 1 ? '' : 's' }}</span>
                </div>
                <div class="reports-grid">
                    @foreach($reports as $report)
                        <a class="report-link" href="{{ route('admin.reports.show', $report['key']) }}">
                            <span class="report-ico">{{ jv_icon($report['icon'] ?? 'bar-chart-3', '', 20) }}</span>
                            <span>
                                <h3>{{ $report['label'] }}</h3>
                                <p>{{ $report['description'] }}</p>
                            </span>
                            <span class="report-arrow">{{ jv_icon('external-link', '', 16) }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endif
@endsection
