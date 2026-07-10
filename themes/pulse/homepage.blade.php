@extends('themes.pulse::layouts.frontend')

@section('title', $page->meta_title ?: ($page->title ?? \App\Models\Setting::get('site_title', 'JamVini Hosting')))
@section('description', $page->meta_description ?: ($page->excerpt ?? \App\Models\Setting::get('site_description', '')))

@section('content')
    @if(!empty($page->blocks))
        @foreach($page->blocks as $block)
            @php
                $styles = $block['styles'] ?? [];
                $bg = $styles['background'] ?? 'transparent';
                $padding = $styles['padding'] ?? '24px 0';
                $fullWidth = $styles['fullWidth'] ?? false;
                $data = $block['data'] ?? [];
            @endphp
            <section class="pulse-section {{ $fullWidth ? 'full-width' : '' }}" style="background:{{ $bg }};padding:{{ $padding }};">
                <div class="{{ $fullWidth ? '' : 'pulse-container' }}">
                    @includeFirst(['themes.pulse::blocks.render', 'themes.default::blocks.render'], ['block' => $block, 'data' => $data])
                </div>
            </section>
        @endforeach
    @else
        @include('themes.pulse::public.index')
    @endif
@endsection
