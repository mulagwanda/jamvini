@extends('themes.kumbukumbu::layouts.frontend')

@section('title', ($post->meta_title ?: $post->title) . ' - Blog')
@section('description', $post->meta_description ?: ($post->excerpt ?? \App\Models\Setting::get('site_description', '')))

@section('content')
<article class="kmb-page">
    <div class="kmb-container kmb-readable">
        <a href="{{ route('blog') }}" class="kmb-back">Back to Blog</a>
        @if($post->featured_image)
            <img src="{{ Str::startsWith($post->featured_image, ['http://', 'https://', '/']) ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="kmb-featured">
        @endif
        <p class="kmb-eyebrow">{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</p>
        <h1>{{ $post->title }}</h1>
        @if($post->excerpt)<p class="kmb-lead">{{ $post->excerpt }}</p>@endif
        <div class="kmb-content">{!! $post->content !!}</div>
    </div>
</article>
@endsection
