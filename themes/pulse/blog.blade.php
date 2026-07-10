@extends('themes.pulse::layouts.frontend')

@section('title', 'Blog')

@section('content')
<section class="pulse-page">
    <div class="pulse-container">
        <h1>Blog</h1>
        <div class="pulse-grid">
            @forelse($posts as $post)
                <article class="pulse-card">
                    <h2><a href="{{ route('blog.post', $post->slug) }}">{{ $post->title }}</a></h2>
                    @if($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
                    <small>{{ $post->published_at?->format('M d, Y') ?: $post->created_at?->format('M d, Y') }}</small>
                </article>
            @empty
                <p>No posts published yet.</p>
            @endforelse
        </div>
        {{ $posts->links() }}
    </div>
</section>
@endsection
