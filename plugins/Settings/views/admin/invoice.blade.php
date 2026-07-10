@extends('themes.default::layouts.admin')

@section('title', 'Invoice Settings')
@section('breadcrumbs')<span class="current">Invoice Settings</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Invoice Settings</h1>
    <p class="page-subtitle">Configure how invoices look and behave</p>
</div>

<form action="{{ route('admin.settings.invoice.update') }}" method="POST">
    @csrf
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">📄 Invoice Preferences</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="invoice_prefix">Invoice Number Prefix</label>
                    <input type="text" id="invoice_prefix" name="settings[invoice_prefix]" class="form-input"
                           value="{{ old('settings.invoice_prefix', \App\Models\Setting::get('invoice_prefix', 'INV')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="invoice_due_days">Default Due Days</label>
                    <input type="number" id="invoice_due_days" name="settings[invoice_due_days]" class="form-input"
                           value="{{ old('settings.invoice_due_days', \App\Models\Setting::get('invoice_due_days', '14')) }}" min="1" max="90">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="invoice_notes">Default Invoice Notes</label>
                <input type="text" id="invoice_notes" name="settings[invoice_notes]" class="form-input"
                       value="{{ old('settings.invoice_notes', \App\Models\Setting::get('invoice_notes', 'Thank you for your business!')) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="invoice_footer">Payment Instructions (Footer)</label>
                <textarea id="invoice_footer" name="settings[invoice_footer]" class="form-textarea" rows="2">{{ old('settings.invoice_footer', \App\Models\Setting::get('invoice_footer', 'Payment via M-Pesa, Tigo Pesa, Airtel Money or Bank Transfer')) }}</textarea>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <button type="submit" class="btn btn-primary btn-lg">💾 Save Invoice Settings</button>
    </div>
</form>
@endsection