@extends('themes.default::layouts.admin')

@section('title', 'General Settings')
@section('breadcrumbs')<span class="current">General Settings</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">General Settings</h1>
    <p class="page-subtitle">Company identity, contact details, and local preferences.</p>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="card">
        <div class="card-header"><h3 class="card-title">Company Identity</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="company_name">Display Name *</label>
                <input type="text" id="company_name" name="settings[company_name]" class="form-input" value="{{ old('settings.company_name', \App\Models\Setting::get('company_name', 'JamVini Hosting')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="company_legal_name">Legal Company Name</label>
                <input type="text" id="company_legal_name" name="settings[company_legal_name]" class="form-input" value="{{ old('settings.company_legal_name', \App\Models\Setting::get('company_legal_name')) }}" placeholder="Smart Web Hosting Company Limited">
            </div>

            <div class="form-group">
                <label class="form-label" for="company_website">Website URL</label>
                <input type="url" id="company_website" name="settings[company_website]" class="form-input" value="{{ old('settings.company_website', \App\Models\Setting::get('company_website')) }}" placeholder="https://example.co.tz">
            </div>

            <div class="form-group">
                <label class="form-label" for="company_logo">Company Legal Logo</label>
                @if(\App\Models\Setting::get('company_logo'))
                    <div style="margin-bottom: 8px;">
                        <img src="{{ asset('storage/' . \App\Models\Setting::get('company_logo')) }}" alt="Company logo" style="max-height: 60px; border-radius: var(--jv-radius-sm);">
                    </div>
                @endif
                <input type="file" id="company_logo" name="company_logo" class="form-input" accept="image/*">
                <div class="form-hint">Used for invoices and legal documents. Public website logo is controlled in Tanzanite Options.</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Contact Details</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="company_email">Main Email</label>
                    <input type="email" id="company_email" name="settings[company_email]" class="form-input" value="{{ old('settings.company_email', \App\Models\Setting::get('company_email')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="support_email">Support Email</label>
                    <input type="email" id="support_email" name="settings[support_email]" class="form-input" value="{{ old('settings.support_email', \App\Models\Setting::get('support_email')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="billing_email">Billing Email</label>
                    <input type="email" id="billing_email" name="settings[billing_email]" class="form-input" value="{{ old('settings.billing_email', \App\Models\Setting::get('billing_email')) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="company_phone">Phone</label>
                <input type="text" id="company_phone" name="settings[company_phone]" class="form-input" value="{{ old('settings.company_phone', \App\Models\Setting::get('company_phone')) }}">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Address</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="company_address">Street Address</label>
                <textarea id="company_address" name="settings[company_address]" class="form-textarea" rows="2">{{ old('settings.company_address', \App\Models\Setting::get('company_address')) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="company_city">City</label>
                    <input type="text" id="company_city" name="settings[company_city]" class="form-input" value="{{ old('settings.company_city', \App\Models\Setting::get('company_city')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="company_region">Region/State</label>
                    <input type="text" id="company_region" name="settings[company_region]" class="form-input" value="{{ old('settings.company_region', \App\Models\Setting::get('company_region')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="company_postal_code">Postal Code</label>
                    <input type="text" id="company_postal_code" name="settings[company_postal_code]" class="form-input" value="{{ old('settings.company_postal_code', \App\Models\Setting::get('company_postal_code')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="company_country">Country</label>
                    <input type="text" id="company_country" name="settings[company_country]" class="form-input" value="{{ old('settings.company_country', \App\Models\Setting::get('company_country', 'Tanzania')) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Localization</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="timezone">Timezone</label>
                    <select id="timezone" name="settings[timezone]" class="form-select">
                        @foreach(['Africa/Dar_es_Salaam' => 'Africa/Dar es Salaam (EAT)', 'Africa/Nairobi' => 'Africa/Nairobi', 'UTC' => 'UTC'] as $value => $label)
                            <option value="{{ $value }}" {{ old('settings.timezone', \App\Models\Setting::get('timezone', 'Africa/Dar_es_Salaam')) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="date_format">Date Format</label>
                    <select id="date_format" name="settings[date_format]" class="form-select">
                        @foreach(['d/m/Y' => 'dd/mm/yyyy (31/12/2026)', 'm/d/Y' => 'mm/dd/yyyy (12/31/2026)', 'Y-m-d' => 'yyyy-mm-dd (2026-12-31)', 'M d, Y' => 'Dec 31, 2026'] as $value => $label)
                            <option value="{{ $value }}" {{ old('settings.date_format', \App\Models\Setting::get('date_format', 'd/m/Y')) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Save General Settings</button>
    </div>
</form>
@endsection
