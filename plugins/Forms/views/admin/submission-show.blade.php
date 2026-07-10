@extends('themes.default::layouts.admin')

@section('title', 'Submission #' . $submission->id)
@section('breadcrumbs')<a href="{{ route('admin.forms.index') }}">Forms</a> <span class="separator">/</span> <a href="{{ route('admin.forms.submissions', $form) }}">{{ $form->title }}</a> <span class="separator">/</span> <span class="current">#{{ $submission->id }}</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Submission #{{ $submission->id }}</h1></div>
<div class="card"><div class="card-header"><h3 class="card-title">📋 Data</h3></div><div class="card-body">
    <table class="table">@foreach($submission->data as $key => $val)<tr><td style="font-weight: 600; width: 150px;">{{ ucfirst($key) }}</td><td>{{ $val }}</td></tr>@endforeach</table>
</div></div>
@endsection