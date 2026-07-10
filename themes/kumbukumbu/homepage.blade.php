@extends('themes.kumbukumbu::layouts.frontend')

@section('title', $page->meta_title ?: ($page->title ?? \App\Models\Setting::get('site_title', 'JamVini Hosting')))
@section('description', $page->meta_description ?: ($page->excerpt ?? \App\Models\Setting::get('site_description', '')))

@section('content')
    @if(!empty($page->blocks))
        @foreach($page->blocks as $block)
            @php
                $styles = $block['styles'] ?? [];
                $bg = $styles['background'] ?? 'transparent';
                $padding = $styles['padding'] ?? '16px 0';
                $fullWidth = $styles['fullWidth'] ?? false;
                $data = $block['data'] ?? [];
            @endphp
            <div class="page-section {{ $fullWidth ? 'full-width' : '' }}" style="background: {{ $bg }}; padding: {{ $padding }};">
                <div class="{{ $fullWidth ? '' : 'kmb-container' }}" style="{{ $fullWidth ? 'padding: 0;' : '' }}">
                    @include('themes.kumbukumbu::blocks.render', ['block' => $block, 'data' => $data])
                </div>
            </div>
        @endforeach
    @else
        @include('themes.kumbukumbu::homepage-default')
    @endif
@endsection
