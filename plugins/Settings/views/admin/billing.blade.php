@extends('themes.default::layouts.admin')

@section('title', 'Billing & Tax Settings')
@section('breadcrumbs')<span class="current">Billing & Tax</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Billing & Tax</h1>
    <p class="page-subtitle">Currency, tax identity, and default tax calculation rules.</p>
</div>

<form action="{{ route('admin.settings.billing.update') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header"><h3 class="card-title">Currency</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="currency">Default Currency</label>
                    <select id="currency" name="settings[currency]" class="form-select">
                        @foreach(['TZS' => 'TZS (Tanzanian Shilling)', 'USD' => 'USD (US Dollar)', 'KES' => 'KES (Kenyan Shilling)', 'UGX' => 'UGX (Ugandan Shilling)', 'RWF' => 'RWF (Rwandan Franc)'] as $value => $label)
                            <option value="{{ $value }}" {{ old('settings.currency', \App\Models\Setting::get('currency', 'TZS')) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="currency_decimal_places">Decimal Places</label>
                    <input type="number" id="currency_decimal_places" name="settings[currency_decimal_places]" class="form-input" value="{{ old('settings.currency_decimal_places', \App\Models\Setting::get('currency_decimal_places', '0')) }}" min="0" max="4">
                    <div class="form-hint">Use 0 for TZS-style pricing, 2 for USD-style pricing.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Tax Identity</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="company_tin">TIN Number</label>
                    <input type="text" id="company_tin" name="settings[company_tin]" class="form-input" value="{{ old('settings.company_tin', \App\Models\Setting::get('company_tin')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="company_vrn">VRN / VAT Number</label>
                    <input type="text" id="company_vrn" name="settings[company_vrn]" class="form-input" value="{{ old('settings.company_vrn', \App\Models\Setting::get('company_vrn')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="tax_label">Tax Label</label>
                    <input type="text" id="tax_label" name="settings[tax_label]" class="form-input" value="{{ old('settings.tax_label', \App\Models\Setting::get('tax_label', 'VAT')) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Tax Calculation</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; align-items: end;">
                <div class="form-group">
                    <label class="form-label">Enable Tax</label>
                    <label class="toggle-switch">
                        <input type="hidden" name="settings[vat_enabled]" value="0">
                        <input type="checkbox" name="settings[vat_enabled]" value="1" {{ old('settings.vat_enabled', \App\Models\Setting::get('vat_enabled', '1')) === '1' ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>Charge tax by default</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" for="vat_rate">Default Tax Rate (%)</label>
                    <input type="number" id="vat_rate" name="settings[vat_rate]" class="form-input" value="{{ old('settings.vat_rate', \App\Models\Setting::get('vat_rate', '18')) }}" min="0" max="100" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Price Entry</label>
                    <label class="toggle-switch">
                        <input type="hidden" name="settings[prices_include_tax]" value="0">
                        <input type="checkbox" name="settings[prices_include_tax]" value="1" {{ old('settings.prices_include_tax', \App\Models\Setting::get('prices_include_tax', '0')) === '1' ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                        <span>Prices include tax</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary btn-lg">Save Billing Settings</button>
    </div>
</form>
@endsection
