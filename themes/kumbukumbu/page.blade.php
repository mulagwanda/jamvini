@extends('themes.kumbukumbu::layouts.frontend')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: ($page->excerpt ?? \App\Models\Setting::get('site_description', '')))

@section('content')
    @if(!empty($page->blocks))
        @foreach($page->blocks as $block)
            @php
                $data = $block['data'] ?? [];
                $styles = $block['styles'] ?? [];
                $bg = $styles['background'] ?? 'transparent';
                $padding = $styles['padding'] ?? '16px 0';
                $fullWidth = $styles['fullWidth'] ?? false;
            @endphp
            <div class="page-section {{ $fullWidth ? 'full-width' : '' }}" style="background: {{ $bg }}; padding: {{ $padding }};">
                <div class="{{ $fullWidth ? '' : 'kmb-container' }}">
                    @include('themes.kumbukumbu::blocks.render', ['block' => $block, 'data' => $data])
                </div>
            </div>
        @endforeach
    @else
        <section class="kmb-page">
            <div class="kmb-container kmb-readable">
                @if($page->featured_image)
                    <img src="{{ Str::startsWith($page->featured_image, ['http://', 'https://', '/']) ? $page->featured_image : asset('storage/' . $page->featured_image) }}" alt="{{ $page->title }}" class="kmb-featured">
                @endif
                <p class="kmb-eyebrow">Page</p>
                <h1>{{ $page->title }}</h1>
                @if($page->excerpt)<p class="kmb-lead">{{ $page->excerpt }}</p>@endif
                <div class="kmb-content">{!! $page->content ?? '' !!}</div>
            </div>
        </section>
    @endif
@endsection
