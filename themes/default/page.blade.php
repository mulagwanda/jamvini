@extends('themes.default::layouts.frontend')

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
            <div class="page-section {{ $fullWidth ? 'full-width' : '' }}" 
                 style="background: {{ $bg }}; padding: {{ $padding }};">
                <div class="{{ $fullWidth ? '' : 'container' }}">
                    @include('themes.default::blocks.render', ['block' => $block, 'data' => $data])
                </div>
            </div>
        @endforeach
    @else
        <section style="padding: 0rem 0;">
            <div class="container">
                @if($page->featured_image)
                    <img src="{{ Str::startsWith($page->featured_image, ['http://', 'https://', '/']) ? $page->featured_image : asset('storage/' . $page->featured_image) }}" alt="{{ $page->title }}" style="width:100%;max-height:420px;object-fit:cover;border-radius:12px;margin-bottom:24px;">
                @endif
                <h1>{{ $page->title }}</h1>
                @if($page->excerpt)
                    <p style="font-size:1.15rem;color:var(--gray-600);line-height:1.7;">{{ $page->excerpt }}</p>
                @endif
                <div style="line-height: 1.8; font-size: 1.05rem;">
                    {!! $page->content ?? '' !!}
                </div>
            </div>
        </section>
    @endif
@endsection
