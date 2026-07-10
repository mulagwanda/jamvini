@extends('themes.default::layouts.frontend')

@section('title', ($post->meta_title ?: $post->title) . ' — Blog')
@section('description', $post->meta_description ?: ($post->excerpt ?? \App\Models\Setting::get('site_description', '')))

@section('content')
<section style="padding-top:3rem">
    <div class="container">
        <div class="article">
            <div class="breadcrumb" style="color:var(--gray-600)">
                <a href="{{ route('blog') }}" style="color:var(--primary)">← Back to Blog</a>
            </div>
            @if($post->featured_image)
                <img src="{{ Str::startsWith($post->featured_image, ['http://', 'https://', '/']) ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" style="width:100%;max-height:420px;object-fit:cover;border-radius:18px;margin-top:1rem;">
            @else
                <div class="article-hero" style="margin-top:1rem">
                    {{ strtoupper(substr($post->title, 0, 2)) }}
                </div>
            @endif
            
            @if($post->categories->count() > 0)
                <span class="eyebrow">{{ $post->categories->first()->name }}</span>
            @endif
            
            <h1>{{ $post->title }}</h1>
            
            <div class="article-meta">
                <div class="avatar">{{ strtoupper(substr($post->author->name ?? 'J', 0, 2)) }}</div>
                <div>
                    <strong>{{ $post->author->name ?? 'JamVini Team' }}</strong>
                    <span>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }} · @php
    $wordCount = str_word_count(strip_tags($post->content ?? ''));
    $readTime = max(1, ceil($wordCount / 200));
@endphp
{{ $readTime }} min read</span>
                    
                </div>
            </div>
            
            <div class="article-content">
                @if($post->excerpt)
                    <p><strong>{{ $post->excerpt }}</strong></p>
                @endif
                {!! $post->content !!}
            </div>

            <div class="comments">
                <h3 style="margin-bottom:1.5rem">Comments</h3>
                <div style="padding:1.5rem;background:var(--gray-50);border-radius:var(--radius);text-align:center;color:var(--gray-600)">
                    💬 Comment form coming soon
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
