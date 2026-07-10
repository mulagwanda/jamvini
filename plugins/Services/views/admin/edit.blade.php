@extends('themes.default::layouts.admin')

@section('title', 'Edit — ' . $service->name)
@section('breadcrumbs')<a href="{{ route('admin.services.index') }}">Services</a> <span class="separator">/</span> <span class="current">{{ $service->name }}</span>@endsection

@section('content')
@php
    $currency = \App\Models\Setting::get('currency', 'TZS');
    $selectedServerId = old('server_id', $service->servers->first()?->id);
    $selectedPackageName = old('package_name', $service->servers->first()?->pivot?->package_name);
    $serverPackages = $servers->mapWithKeys(fn ($srv) => [
        $srv->id => $srv->packages
            ->where('is_active', true)
            ->values()
            ->map(fn ($package) => [
                'name' => $package->name,
                'display_name' => $package->display_name ?: $package->name,
            ])
            ->all()
    ]);
@endphp
<div class="page-header"><h1 class="page-title">Edit: {{ $service->name }}</h1></div>

<form action="{{ route('admin.services.update', $service) }}" method="POST" id="serviceForm">
    @csrf @method('PUT')
    
    {{-- Basic Info --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>📋 Basic Information</h3></div>
        <div class="form-group">
            <label class="form-label">Service Group</label>
            <select name="group_id" id="group_id" class="form-select" onchange="toggleGroupConfig()">
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" data-module="{{ $g->module }}" {{ old('group_id', $service->group_id) == $g->id ? 'selected' : '' }}>
                        {{ $g->icon }} {{ $g->name }} {{ $g->module ? '(' . ucfirst($g->module) . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label class="form-label">Service Name *</label><input type="text" name="name" class="form-input" value="{{ old('name', $service->name) }}" required></div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="2">{{ old('description', $service->description) }}</textarea></div>
        <div style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:end;">
            <div class="form-group"><label class="form-label">Catalog Badge</label><input type="text" name="badge_label" class="form-input" value="{{ old('badge_label', $service->badge_label) }}" placeholder="NEW, FEATURED, POPULAR"></div>
            <label class="toggle-switch" style="margin-bottom:16px;"><input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $service->is_featured) ? 'checked' : '' }}><span class="toggle-slider"></span><span>Featured</span></label>
        </div>
        
        <div id="provisioningGroup" style="display: none;">
            <div class="form-group">
                <label class="form-label">Provisioning Server</label>
                <select name="server_id" id="hostingServerSelect" class="form-select">
                    <option value="">— Manual —</option>
                    @foreach($servers as $srv)
                        <option value="{{ $srv->id }}" {{ $selectedServerId == $srv->id ? 'selected' : '' }}>{{ $srv->name }} ({{ ucfirst($srv->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">WHM Package</label>
                <select name="package_name" id="hostingPackageSelect" class="form-select" data-selected="{{ $selectedPackageName ?? '' }}">
                    <option value="">— Select server first —</option>
                </select>
                <div class="form-hint">Choose the synced WHM package this service should create.</div>
            </div>
        </div>
    </div>

    {{-- Pricing --}}
    <div id="pricingCard" class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>💰 Pricing</h3></div>
        <div class="form-group">
            <label class="toggle-switch">
                <input type="checkbox" name="is_free" value="1" id="isFreeToggle" {{ $service->is_free ? 'checked' : '' }} onchange="togglePricing()">
                <span class="toggle-slider"></span><span>Free service</span>
            </label>
        </div>
        <div id="pricingSection" style="{{ $service->is_free ? 'display:none' : '' }}">
            <div class="form-group">
                <label class="form-label">Billing Type</label>
                <select name="billing_type" class="form-select" style="width: 200px;">
                    <option value="recurring" {{ $service->billing_type === 'recurring' ? 'selected' : '' }}>Recurring</option>
                    <option value="one-time" {{ $service->billing_type === 'one-time' ? 'selected' : '' }}>One-Time</option>
                </select>
            </div>
            @php $pricing = $service->pricing ?? []; @endphp
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                <div class="form-group"><label class="form-label">Monthly ({{ $currency }})</label><input type="number" name="pricing[monthly]" class="form-input" value="{{ old('pricing.monthly', $pricing['monthly'] ?? '') }}" step="0.01"></div>
                <div class="form-group"><label class="form-label">Quarterly ({{ $currency }})</label><input type="number" name="pricing[quarterly]" class="form-input" value="{{ old('pricing.quarterly', $pricing['quarterly'] ?? '') }}" step="0.01"></div>
                <div class="form-group"><label class="form-label">Semi-Annual ({{ $currency }})</label><input type="number" name="pricing[semi_annually]" class="form-input" value="{{ old('pricing.semi_annually', $pricing['semi_annually'] ?? '') }}" step="0.01"></div>
                <div class="form-group"><label class="form-label">Annual ({{ $currency }})</label><input type="number" name="pricing[annually]" class="form-input" value="{{ old('pricing.annually', $pricing['annually'] ?? '') }}" step="0.01"></div>
            </div>
            <div class="form-group"><label class="form-label">Setup Fee ({{ $currency }})</label><input type="number" name="setup_fee" class="form-input" value="{{ old('setup_fee', $service->setup_fee) }}" style="width: 200px;"></div>
            
            <div id="freeDomainSection" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Free Domain on Billing Cycles</label>
                    <div style="display: flex; gap: 16px;">
                        @php $freeCycles = $service->free_domain_cycles ?? []; @endphp
                        @foreach(['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'semi_annually' => 'Semi-Annual', 'annually' => 'Annual'] as $k => $l)
                            <label class="checkbox-group"><input type="checkbox" name="free_domain_cycles[]" value="{{ $k }}" {{ in_array($k, $freeCycles) ? 'checked' : '' }}><span>{{ $l }}</span></label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Domain Configuration --}}
    @include('plugins.Services::admin.domain-config')

    {{-- Configurable Options --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head">
            <h3>⚙️ Configurable Options</h3>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()">+ Add Option</button>
        </div>
        <div id="optionsContainer">
            @foreach($service->options as $index => $option)
                <div class="option-row" style="display: grid; grid-template-columns: 1fr 150px 1fr 160px auto; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Option Name</label>
                        <input type="text" name="options[{{ $index }}][name]" class="form-input" value="{{ old('options.' . $index . '.name', $option->name) }}" placeholder="e.g. Extra Disk Space">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Type</label>
                        <select name="options[{{ $index }}][type]" class="form-select">
                            @foreach(['number' => 'Number', 'dropdown' => 'Dropdown', 'checkbox' => 'Checkbox'] as $value => $label)
                                <option value="{{ $value }}" {{ old('options.' . $index . '.type', $option->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Choices</label>
                        <input type="text" name="options[{{ $index }}][choices]" class="form-input" value="{{ old('options.' . $index . '.choices', implode("\n", $option->options ?? [])) }}">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label class="form-label">Monthly Price ({{ $currency }})</label>
                        <input type="number" name="options[{{ $index }}][price_monthly]" class="form-input" value="{{ old('options.' . $index . '.price_monthly', $option->prices['monthly'] ?? 0) }}" placeholder="5000" step="0.01">
                    </div>
                    <button type="button" class="btn btn-outline-danger" style="align-self:end" onclick="this.closest('.option-row').remove()">Remove</button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Custom Fields --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>{{ jv_icon('clipboard-list', '', 18) }} Custom Fields</h3><button type="button" class="btn btn-sm btn-outline-primary" onclick="addCustomField()">Add Field</button></div>
        <div id="customFieldsContainer">
            @foreach($service->customFields as $index => $field)
                <div class="custom-field-row" style="display:grid;grid-template-columns:1fr 150px 1fr 1fr auto;gap:12px;margin-bottom:12px;">
                    <div class="form-group" style="margin:0"><label class="form-label">Label</label><input type="text" name="custom_fields[{{ $index }}][label]" class="form-input" value="{{ old('custom_fields.' . $index . '.label', $field->label) }}"></div>
                    <div class="form-group" style="margin:0"><label class="form-label">Type</label><select name="custom_fields[{{ $index }}][type]" class="form-select">@foreach(['text'=>'Text','textarea'=>'Textarea','select'=>'Select','checkbox'=>'Checkbox','url'=>'URL','number'=>'Number'] as $value => $label)<option value="{{ $value }}" {{ old('custom_fields.' . $index . '.type', $field->type) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="form-group" style="margin:0"><label class="form-label">Options</label><input type="text" name="custom_fields[{{ $index }}][options]" class="form-input" value="{{ old('custom_fields.' . $index . '.options', $field->options) }}"></div>
                    <div class="form-group" style="margin:0"><label class="form-label">Placeholder</label><input type="text" name="custom_fields[{{ $index }}][placeholder]" class="form-input" value="{{ old('custom_fields.' . $index . '.placeholder', $field->placeholder) }}"></div>
                    <div style="display:flex;gap:8px;align-items:end;"><label class="checkbox-group"><input type="checkbox" name="custom_fields[{{ $index }}][is_required]" value="1" {{ old('custom_fields.' . $index . '.is_required', $field->is_required) ? 'checked' : '' }}><span>Required</span></label><button type="button" class="btn btn-outline-danger" onclick="this.closest('.custom-field-row').remove()">Remove</button></div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Product Addons --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>{{ jv_icon('plus-circle', '', 18) }} Product Addons</h3><button type="button" class="btn btn-sm btn-outline-primary" onclick="addAddon()">Add Addon</button></div>
        <div id="addonsContainer">
            @foreach($service->addons as $index => $addon)
                <div class="addon-row" style="display:grid;grid-template-columns:1fr 1fr 140px 150px auto;gap:12px;margin-bottom:12px;">
                    <div class="form-group" style="margin:0"><label class="form-label">Name</label><input type="text" name="addons[{{ $index }}][name]" class="form-input" value="{{ old('addons.' . $index . '.name', $addon->name) }}"></div>
                    <div class="form-group" style="margin:0"><label class="form-label">Description</label><input type="text" name="addons[{{ $index }}][description]" class="form-input" value="{{ old('addons.' . $index . '.description', $addon->description) }}"></div>
                    <div class="form-group" style="margin:0"><label class="form-label">Price ({{ $currency }})</label><input type="number" name="addons[{{ $index }}][price]" class="form-input" value="{{ old('addons.' . $index . '.price', $addon->price) }}" step="0.01"></div>
                    <div class="form-group" style="margin:0"><label class="form-label">Billing</label><select name="addons[{{ $index }}][billing_cycle]" class="form-select">@foreach(['same_as_parent'=>'Same as parent','one-time'=>'One-time','monthly'=>'Monthly','annually'=>'Annual'] as $value => $label)<option value="{{ $value }}" {{ old('addons.' . $index . '.billing_cycle', $addon->billing_cycle) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div style="display:flex;gap:8px;align-items:end;"><label class="checkbox-group"><input type="checkbox" name="addons[{{ $index }}][is_required]" value="1" {{ old('addons.' . $index . '.is_required', $addon->is_required) ? 'checked' : '' }}><span>Required</span></label><button type="button" class="btn btn-outline-danger" onclick="this.closest('.addon-row').remove()">Remove</button></div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Features --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>✅ Features</h3></div>
        <textarea name="features" class="form-textarea" rows="5">{{ old('features', is_array($service->features) ? implode("\n", $service->features) : '') }}</textarea>
    </div>

    {{-- Upgrade/Downgrade --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>🔄 Upgrade/Downgrade</h3></div>
        <div style="display: flex; gap: 24px;">
            <input type="hidden" name="upgradable" value="0">
            <label class="toggle-switch"><input type="checkbox" name="upgradable" value="1" {{ $service->upgradable ? 'checked' : '' }}><span class="toggle-slider"></span><span>Allow Upgrades</span></label>
            <input type="hidden" name="allow_downgrade" value="0">
            <label class="toggle-switch"><input type="checkbox" name="allow_downgrade" value="1" {{ $service->allow_downgrade ? 'checked' : '' }}><span class="toggle-slider"></span><span>Allow Downgrades</span></label>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 1.5rem;">
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">💾 Update Service</button>
    </div>
</form>
@endsection

@push('scripts')
@include('plugins.Services::admin.partials.domain-scripts')
<script>
let optionCount = {{ $service->options->count() }};
let customFieldCount = {{ $service->customFields->count() }};
let addonCount = {{ $service->addons->count() }};
const currencyCode = @json($currency);
const packagesByServer = @json($serverPackages);

function toggleGroupConfig() {
    const groupSelect = document.getElementById('group_id');
    const option = groupSelect.options[groupSelect.selectedIndex];
    const module = option?.dataset?.module || '';
    document.getElementById('provisioningGroup').style.display = module === 'hosting' ? '' : 'none';
    document.getElementById('freeDomainSection').style.display = module === 'hosting' ? '' : 'none';
    document.getElementById('domainConfig').style.display = module === 'domains' ? '' : 'none';
    document.getElementById('pricingCard').style.display = module === 'domains' ? 'none' : '';
}
function togglePricing() {
    document.getElementById('pricingSection').style.display = document.getElementById('isFreeToggle').checked ? 'none' : '';
}

function addOption() {
    const container = document.getElementById('optionsContainer');
    const row = document.createElement('div');
    row.className = 'option-row';
    row.style.cssText = 'display: grid; grid-template-columns: 1fr 150px 1fr 160px auto; gap: 12px; margin-bottom: 12px;';
    row.innerHTML = `
        <div class="form-group" style="margin:0">
            <label class="form-label">Option Name</label>
            <input type="text" name="options[${optionCount}][name]" class="form-input" placeholder="e.g. Extra Disk Space">
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Type</label>
            <select name="options[${optionCount}][type]" class="form-select"><option value="number">Number</option><option value="dropdown">Dropdown</option><option value="checkbox">Checkbox</option></select>
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Choices</label>
            <input type="text" name="options[${optionCount}][choices]" class="form-input">
        </div>
        <div class="form-group" style="margin:0">
            <label class="form-label">Monthly Price (${currencyCode})</label>
            <input type="number" name="options[${optionCount}][price_monthly]" class="form-input" placeholder="5000" step="0.01">
        </div>
        <button type="button" class="btn btn-outline-danger" style="align-self:end" onclick="this.closest('.option-row').remove()">Remove</button>
    `;
    container.appendChild(row);
    optionCount++;
}

function addCustomField() {
    const container = document.getElementById('customFieldsContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="custom-field-row" style="display:grid;grid-template-columns:1fr 150px 1fr 1fr auto;gap:12px;margin-bottom:12px;">
            <div class="form-group" style="margin:0"><label class="form-label">Label</label><input type="text" name="custom_fields[${customFieldCount}][label]" class="form-input"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Type</label><select name="custom_fields[${customFieldCount}][type]" class="form-select"><option value="text">Text</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="checkbox">Checkbox</option><option value="url">URL</option><option value="number">Number</option></select></div>
            <div class="form-group" style="margin:0"><label class="form-label">Options</label><input type="text" name="custom_fields[${customFieldCount}][options]" class="form-input"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Placeholder</label><input type="text" name="custom_fields[${customFieldCount}][placeholder]" class="form-input"></div>
            <div style="display:flex;gap:8px;align-items:end;"><label class="checkbox-group"><input type="checkbox" name="custom_fields[${customFieldCount}][is_required]" value="1"><span>Required</span></label><button type="button" class="btn btn-outline-danger" onclick="this.closest('.custom-field-row').remove()">Remove</button></div>
        </div>
    `);
    customFieldCount++;
}

function addAddon() {
    const container = document.getElementById('addonsContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="addon-row" style="display:grid;grid-template-columns:1fr 1fr 140px 150px auto;gap:12px;margin-bottom:12px;">
            <div class="form-group" style="margin:0"><label class="form-label">Name</label><input type="text" name="addons[${addonCount}][name]" class="form-input"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Description</label><input type="text" name="addons[${addonCount}][description]" class="form-input"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Price (${currencyCode})</label><input type="number" name="addons[${addonCount}][price]" class="form-input" step="0.01" value="0"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Billing</label><select name="addons[${addonCount}][billing_cycle]" class="form-select"><option value="same_as_parent">Same as parent</option><option value="one-time">One-time</option><option value="monthly">Monthly</option><option value="annually">Annual</option></select></div>
            <div style="display:flex;gap:8px;align-items:end;"><label class="checkbox-group"><input type="checkbox" name="addons[${addonCount}][is_required]" value="1"><span>Required</span></label><button type="button" class="btn btn-outline-danger" onclick="this.closest('.addon-row').remove()">Remove</button></div>
        </div>
    `);
    addonCount++;
}

document.addEventListener('DOMContentLoaded', toggleGroupConfig);
document.addEventListener('DOMContentLoaded', function() {
    const serverSelect = document.getElementById('hostingServerSelect');
    const packageSelect = document.getElementById('hostingPackageSelect');

    if (!serverSelect || !packageSelect) return;

    function renderPackages() {
        const selectedPackage = packageSelect.dataset.selected || '';
        const packages = packagesByServer[serverSelect.value] || [];
        packageSelect.innerHTML = '';

        if (!serverSelect.value) {
            packageSelect.insertAdjacentHTML('beforeend', '<option value="">— Manual provisioning —</option>');
            return;
        }

        if (!packages.length) {
            packageSelect.insertAdjacentHTML('beforeend', '<option value="">— No synced packages —</option>');
            return;
        }

        packages.forEach((pkg) => {
            const option = document.createElement('option');
            option.value = pkg.name;
            option.textContent = pkg.display_name || pkg.name;
            option.selected = pkg.name === selectedPackage;
            packageSelect.appendChild(option);
        });
    }

    serverSelect.addEventListener('change', () => {
        packageSelect.dataset.selected = '';
        renderPackages();
    });

    renderPackages();
});
</script>

@if(in_array($service->type, ['domain', 'domains']) && $service->tlds->count() > 0)
<script>
let existingTlds = @json($service->tlds ?? []);
document.addEventListener('DOMContentLoaded', function() {
    if (existingTlds.length > 0) {
        existingTlds.forEach(function(tld) {
            let pricing = tld.pricing?.[0] || {};
            let grace = tld.period_pricing?.find(p => p.period_type == 1);
            let redemption = tld.period_pricing?.find(p => p.period_type == 2);
            let addons = tld.addons || [];
            addTld({
                tld: tld.tld, register_price: pricing.register_price || '', renewal_price: pricing.renewal_price || '',
                transfer_price: pricing.transfer_price || '', years: tld.pricing?.map(p => p.years).join(',') || '1,2,3,5,10',
                dns_management: tld.dns_management, email_forwarding: tld.email_forwarding,
                id_protection: tld.id_protection, epp_code: tld.epp_code, auto_register: tld.auto_register,
                grace_days: grace?.days || '30', grace_price: grace?.price || '0',
                redemption_days: redemption?.days || '30', redemption_price: redemption?.price || '0',
                addon_dns: addons.find(a=>a.name==='DNS Management')?.price || '0',
                addon_email: addons.find(a=>a.name==='Email Forwarding')?.price || '0',
                addon_id: addons.find(a=>a.name==='ID Protection')?.price || '0',
            });
        });
    }
});
</script>
@endif
@endpush
