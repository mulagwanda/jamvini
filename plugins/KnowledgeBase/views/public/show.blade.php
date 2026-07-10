@extends(jv_theme_view('layouts.frontend'))

@section('title', $article->title)

@section('content')
<section class="page-section">
    <div class="container">
        <article class="card" style="padding:24px;max-width:860px;margin:0 auto;">
            <a href="{{ route('kb.index') }}">&larr; Knowledge Base</a>
            <p style="margin-top:18px;"><span class="badge badge-info">{{ $article->category }}</span></p>
            <h1>{{ $article->title }}</h1>
            <div class="content" style="line-height:1.8;">
                {!! $article->content !!}
            </div>
        </article>
    </div>
</section>
@endsection
