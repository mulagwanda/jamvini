@extends('themes.pulse::layouts.frontend')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: ($post->excerpt ?? \App\Models\Setting::get('site_description', '')))

@section('content')
<article class="pulse-page">
    <div class="pulse-container">
        @if($post->featured_image)
            <img src="{{ \Illuminate\Support\Str::startsWith($post->featured_image, ['http://', 'https://', '/']) ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="pulse-page-image">
        @endif
        <h1>{{ $post->title }}</h1>
        @if($post->excerpt)<p class="pulse-lead">{{ $post->excerpt }}</p>@endif
        <div class="pulse-content">{!! $post->content !!}</div>
    </div>
</article>
@endsection
