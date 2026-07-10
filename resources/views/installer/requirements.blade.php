@extends('installer.layout')

@section('content')
<h2 class="page-title">System Requirements</h2>
<p class="page-copy">JamVini will check the minimum server requirements before continuing with setup.</p>

@foreach($requirements as $req)
<div class="requirement">
    <span>{{ $req['name'] }}</span>
    <span class="status {{ $req['passed'] ? 'pass' : 'fail' }}">
        {{ jv_icon($req['passed'] ? 'check-circle' : 'x-circle', '', 14) }}
        {{ $req['passed'] ? 'Passed' : 'Missing' }}
        @if(isset($req['current'])) ({{ $req['current'] }}) @endif
    </span>
</div>
@endforeach

<div style="margin-top: 24px;">
    @if($allPassed)
        <a href="{{ route('install.step', 'database') }}" class="btn btn-primary btn-block">{{ jv_icon('database', '', 16) }} Continue to Database Setup</a>
    @else
        <div class="alert alert-error">Please fix the failed requirements before continuing.</div>
    @endif
</div>
@endsection
