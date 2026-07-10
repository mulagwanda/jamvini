@extends('themes.default::layouts.admin')

@section('title', 'Submissions — ' . $form->title)
@section('breadcrumbs')<a href="{{ route('admin.forms.index') }}">Forms</a> <span class="separator">/</span> <span class="current">{{ $form->title }}</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">{{ $form->title }} — Submissions</h1></div>
<div class="card">
    <div class="card-body" style="padding: 0;">
        @if($submissions->count() > 0)
        <table class="table">
            <thead><tr><th>#</th><th>Data</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @foreach($submissions as $sub)
                <tr>
                    <td>{{ $sub->id }}</td>
                    <td>{{ Str::limit(json_encode($sub->data), 60) }}</td>
                    <td><span class="badge badge-{{ $sub->status === 'unread' ? 'warning' : 'gray' }}">{{ $sub->status }}</span></td>
                    <td>{{ $sub->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.forms.submissions.show', [$form, $sub]) }}" class="btn btn-sm btn-outline-primary">👁️</a>
                        <form action="{{ route('admin.forms.submissions.delete', [$form, $sub]) }}" method="POST" style="display: inline;" data-confirm="Delete?" data-danger="true">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state"><div class="empty-state-icon">📩</div><div class="empty-state-title">No submissions yet</div></div>
        @endif
    </div>
</div>
@endsection