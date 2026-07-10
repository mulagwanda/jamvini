@extends('themes.pulse::layouts.admin')

@section('title', 'Pulse Theme Settings')

@section('content')
<div class="pulse-admin-section">
    <h2 class="text-2xl font-bold">Pulse Theme Customization</h2>
    
    <form action="{{ route('admin.theme.pulse.update') }}" method="POST" class="mt-6">
        @csrf
        
        <div class="grid grid-cols-2 gap-6">
            <div class="pulse-form-group">
                <label for="primary_color">Primary Color</label>
                <input type="color" id="primary_color" name="primary_color" 
                       value="{{ setting('pulse.primary_color', '#1a5276') }}" 
                       class="pulse-form-control">
            </div>
            
            <div class="pulse-form-group">
                <label for="secondary_color">Secondary Color</label>
                <input type="color" id="secondary_color" name="secondary_color" 
                       value="{{ setting('pulse.secondary_color', '#2e86c1') }}" 
                       class="pulse-form-control">
            </div>
            
            <div class="pulse-form-group">
                <label for="accent_color">Accent Color</label>
                <input type="color" id="accent_color" name="accent_color" 
                       value="{{ setting('pulse.accent_color', '#f39c12') }}" 
                       class="pulse-form-control">
            </div>
            
            <div class="pulse-form-group">
                <label for="layout">Layout Style</label>
                <select id="layout" name="layout" class="pulse-form-control">
                    <option value="boxed" {{ setting('pulse.layout') === 'boxed' ? 'selected' : '' }}>Boxed</option>
                    <option value="fullwidth" {{ setting('pulse.layout') === 'fullwidth' ? 'selected' : '' }}>Full Width</option>
                </select>
            </div>
            
            <div class="pulse-form-group">
                <label for="dark_mode">Dark Mode</label>
                <select id="dark_mode" name="dark_mode" class="pulse-form-control">
                    <option value="0" {{ !setting('pulse.dark_mode') ? 'selected' : '' }}>Disabled</option>
                    <option value="1" {{ setting('pulse.dark_mode') ? 'selected' : '' }}>Enabled</option>
                </select>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
