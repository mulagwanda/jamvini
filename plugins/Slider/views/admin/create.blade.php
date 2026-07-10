@extends('themes.default::layouts.admin')

@section('title', 'New Slider')
@section('breadcrumbs')<a href="{{ route('admin.slider.index') }}">Sliders</a> <span class="separator">/</span> <span class="current">New</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">New Slider</h1>
        <p class="page-subtitle">Create a polished hero, carousel, or testimonial slider.</p>
    </div>
</div>

<form action="{{ route('admin.slider.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            <div style="display:grid;grid-template-columns:minmax(0,1fr) 260px;gap:18px;">
                <div class="form-group">
                    <label class="form-label" for="title">Slider Name</label>
                    <input type="text" id="title" name="title" class="form-input" required placeholder="Homepage Hero">
                </div>
                <div class="form-group">
                    <label class="form-label" for="type">Slider Type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="hero">Hero Slider</option>
                        <option value="carousel">Carousel</option>
                        <option value="testimonial">Testimonial</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;">
                <div class="form-group">
                    <label class="form-label">Height</label>
                    <input type="number" name="settings[height]" class="form-input" value="620" min="260" max="980">
                </div>
                <div class="form-group">
                    <label class="form-label">Speed</label>
                    <input type="number" name="settings[speed]" class="form-input" value="700" min="150" max="3000">
                </div>
                <div class="form-group">
                    <label class="form-label">Delay</label>
                    <input type="number" name="settings[delay]" class="form-input" value="5500" min="1000" max="20000">
                </div>
                <div class="form-group">
                    <label class="form-label">Effect</label>
                    <select name="settings[effect]" class="form-select">
                        <option value="fade">Fade</option>
                        <option value="slide">Slide</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:18px;flex-wrap:wrap;margin-top:4px;">
                @foreach([
                    'autoplay' => 'Autoplay',
                    'pause_on_hover' => 'Pause on hover',
                    'navigation' => 'Navigation arrows',
                    'pagination' => 'Pagination dots',
                    'keyboard' => 'Keyboard control',
                    'loop' => 'Loop slides',
                ] as $key => $label)
                    <label class="checkbox-group">
                        <input type="checkbox" name="settings[{{ $key }}]" value="1" checked>
                        {{ $label }}
                    </label>
                @endforeach
                <label class="checkbox-group">
                    <input type="checkbox" name="is_active" value="1" checked>
                    Active
                </label>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px;">
        <a href="{{ route('admin.slider.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Create Slider</button>
    </div>
</form>
@endsection
