@extends('themes.kumbukumbu::layouts.frontend')

@section('title', 'Blog - ' . \App\Models\Setting::get('company_name', 'JamVini Hosting'))

@section('content')
<section class="kmb-page-hero">
    <div class="kmb-container">
        <p class="kmb-eyebrow">Blog</p>
        <h1>Notes from the builders.</h1>
        <p>Hosting lessons, product updates, and practical web business thinking.</p>
    </div>
</section>

<section class="kmb-section">
    <div class="kmb-container">
        <div class="kmb-blog-grid">
            @forelse($posts as $post)
                <article class="kmb-blog-card">
                    @if($post->featured_image)
                        <img src="{{ Str::startsWith($post->featured_image, ['http://', 'https://', '/']) ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}">
                    @else
                        <div class="kmb-blog-fallback">{{ strtoupper(substr($post->title, 0, 2)) }}</div>
                    @endif
                    <div>
                        <small>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</small>
                        <h2>{{ $post->title }}</h2>
                        <p>{{ Str::limit($post->excerpt ?? strip_tags($post->content), 140) }}</p>
                        <a href="{{ route('blog.post', $post->slug) }}">Read article</a>
                    </div>
                </article>
            @empty
                <div class="kmb-empty">
                    <h2>No posts yet</h2>
                    <p>Publish your first article from the CMS.</p>
                </div>
            @endforelse
        </div>
        {{ $posts->links() }}
    </div>
</section>
@endsection
