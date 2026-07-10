@extends('themes.pulse::layouts.frontend')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: ($page->excerpt ?? \App\Models\Setting::get('site_description', '')))

@section('content')
<section class="pulse-page">
    <div class="pulse-container">
        @if($page->featured_image)
            <img src="{{ \Illuminate\Support\Str::startsWith($page->featured_image, ['http://', 'https://', '/']) ? $page->featured_image : asset('storage/' . $page->featured_image) }}" alt="{{ $page->title }}" class="pulse-page-image">
        @endif
        <h1>{{ $page->title }}</h1>
        @if($page->excerpt)<p class="pulse-lead">{{ $page->excerpt }}</p>@endif
        @if(!empty($page->blocks))
            @foreach($page->blocks as $block)
                @php
                    $styles = $block['styles'] ?? [];
                    $bg = $styles['background'] ?? 'transparent';
                    $padding = $styles['padding'] ?? '24px 0';
                    $fullWidth = $styles['fullWidth'] ?? false;
                @endphp
                <section class="pulse-section {{ $fullWidth ? 'full-width' : '' }}" style="background:{{ $bg }};padding:{{ $padding }};">
                    <div class="{{ $fullWidth ? '' : 'pulse-container' }}">
                        @includeFirst(['themes.pulse::blocks.render', 'themes.default::blocks.render'], ['block' => $block, 'data' => $block['data'] ?? []])
                    </div>
                </section>
            @endforeach
        @else
            <div class="pulse-content">{!! $page->content ?? '' !!}</div>
        @endif
    </div>
</section>
@endsection
