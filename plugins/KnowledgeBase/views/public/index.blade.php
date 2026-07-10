@extends(jv_theme_view('layouts.frontend'))

@section('title', 'Knowledge Base')

@section('content')
<section class="page-section">
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Knowledge Base</h1>
                <p class="page-subtitle">Helpful articles from our support team.</p>
            </div>
        </div>
        <div class="grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
            @forelse($articles as $article)
                <article class="card" style="padding:18px;">
                    <small>{{ $article->category }}</small>
                    <h2 style="font-size:1.15rem;margin:.35rem 0;"><a href="{{ route('kb.show', $article->slug) }}">{{ $article->title }}</a></h2>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($article->content), 150) }}</p>
                </article>
            @empty
                <div class="card" style="padding:24px;">No articles published yet.</div>
            @endforelse
        </div>
        {{ $articles->links() }}
    </div>
</section>
@endsection
