@extends('themes.default::layouts.admin')

@section('title', $report['label'])
@section('breadcrumbs')<a href="{{ route('admin.reports.index') }}">Reports</a> <span class="separator">/</span> <span class="current">{{ $report['label'] }}</span>@endsection

@section('content')
@php
    $chart = $result['chart'] ?? null;
    $columns = $result['columns'] ?? [];
    $rows = $result['rows'] ?? [];
    $summary = $result['summary'] ?? [];
    $maxChartValue = 1;
    if ($chart && !empty($chart['series'][0]['data'])) {
        $maxChartValue = max(1, max($chart['series'][0]['data']));
    }
    $toneMap = ['success' => 'success', 'warning' => 'warning', 'danger' => 'danger', 'info' => 'info', 'gray' => ''];
@endphp

<style>
.report-head { display:flex; align-items:flex-start; justify-content:space-between; gap:18px; margin-bottom:18px; }
.report-title-wrap { display:flex; align-items:flex-start; gap:14px; }
.report-title-icon { width:48px; height:48px; border-radius:8px; display:grid; place-items:center; background:var(--jv-primary-bg); color:var(--jv-primary); flex:0 0 auto; }
.report-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
.report-filter { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; align-items:end; }
.report-summary { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin:18px 0; }
.report-chart { display:grid; gap:10px; }
.report-bar-row { display:grid; grid-template-columns:minmax(90px, 180px) 1fr minmax(58px, auto); gap:10px; align-items:center; font-size:.84rem; }
.report-bar-label { color:var(--jv-gray-600); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.report-bar-track { height:12px; background:var(--jv-gray-100); border-radius:999px; overflow:hidden; }
.report-bar-fill { height:100%; min-width:4px; background:linear-gradient(90deg, var(--jv-primary), #0984e3); border-radius:999px; }
.report-table-wrap { overflow:auto; }
.report-empty { padding:28px; text-align:center; color:var(--jv-gray-500); }
@media (max-width: 760px) {
    .report-head { display:grid; }
    .report-actions { justify-content:flex-start; }
    .report-bar-row { grid-template-columns:1fr; gap:6px; }
}
</style>

<div class="report-head">
    <div class="report-title-wrap">
        <div class="report-title-icon">{{ jv_icon($report['icon'] ?? 'bar-chart-3', '', 22) }}</div>
        <div>
            <h1 class="page-title">{{ $report['label'] }}</h1>
            <p class="page-subtitle">{{ $report['description'] }}</p>
        </div>
    </div>
    <div class="report-actions">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary">{{ jv_icon('arrow-left', '', 16) }} All Reports</a>
        @if($report['exportable'] ?? true)
            <a href="{{ route('admin.reports.export.csv', array_merge(['key' => $report['key']], request()->query())) }}" class="btn btn-primary">{{ jv_icon('file-spreadsheet', '', 16) }} Export CSV</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ jv_icon('filter', '', 18) }} Filters</h3></div>
    <div class="card-body">
        <form method="GET" class="report-filter">
            <div class="form-group">
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-input">
            </div>
            @if(in_array('status', $report['filters'] ?? [], true))
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <input type="text" name="status" value="{{ $filters['status'] ?? '' }}" class="form-input" placeholder="active, pending, suspended">
                </div>
            @endif
            <div style="display:flex;gap:8px;align-items:center;">
                <button class="btn btn-primary">{{ jv_icon('search', '', 16) }} Apply</button>
                <a href="{{ route('admin.reports.show', $report['key']) }}" class="btn btn-outline-primary">Reset</a>
            </div>
        </form>
    </div>
</div>

@if(!empty($summary))
    <div class="report-summary">
        @foreach($summary as $item)
            <div class="stat-card {{ $toneMap[$item['tone'] ?? 'gray'] ?? '' }}">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-card-label">{{ $item['label'] }}</div>
                        <div class="stat-card-value">{{ $item['value'] }}</div>
                    </div>
                    <div class="stat-card-icon">{{ jv_icon($report['icon'] ?? 'bar-chart-3') }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if($chart && !empty($chart['labels']))
    <div class="dash-card" style="margin-bottom:18px;">
        <div class="dash-card-head"><h3>{{ jv_icon('bar-chart-3', '', 18) }} {{ $chart['series'][0]['label'] ?? 'Trend' }}</h3></div>
        <div class="report-chart">
            @foreach(array_slice($chart['labels'], 0, 16) as $index => $label)
                @php $value = (float) ($chart['series'][0]['data'][$index] ?? 0); @endphp
                <div class="report-bar-row">
                    <div class="report-bar-label" title="{{ $label }}">{{ $label }}</div>
                    <div class="report-bar-track"><div class="report-bar-fill" style="width:{{ min(100, max(2, ($value / $maxChartValue) * 100)) }}%"></div></div>
                    <strong>{{ number_format($value) }}</strong>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ jv_icon('clipboard-list', '', 18) }} Results</h3>
        <span class="badge badge-gray">{{ count($rows) }} row{{ count($rows) === 1 ? '' : 's' }}</span>
    </div>
    <div class="card-body" style="padding:0;">
        @if(empty($rows))
            <div class="report-empty">No rows found for this report and filter range.</div>
        @else
            <div class="report-table-wrap">
                <table class="table" style="margin:0;">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                @foreach($columns as $column)
                                    <td>{{ $row[$column['key']] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
