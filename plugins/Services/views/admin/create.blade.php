@extends('themes.default::layouts.admin')

@section('title', 'Add Service')
@section('breadcrumbs')<a href="{{ route('admin.services.index') }}">Services</a> <span class="separator">/</span> <span class="current">Add</span>@endsection

@section('content')
@php
    $selectedServerId = old('server_id');
    $selectedPackageName = old('package_name');
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
<div class="page-header">
    <h1 class="page-title">Add Service to Catalog</h1>
</div>

<form action="{{ route('admin.services.store') }}" method="POST" id="serviceForm">
    @csrf

    {{-- Basic Info --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>📋 Basic Information</h3></div>
        <div class="form-group">
            <label class="form-label" for="group_id">Service Group *</label>
            <select id="group_id" name="group_id" class="form-select" required onchange="toggleGroupConfig()">
                <option value="">— Select Group —</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" data-module="{{ $g->module }}" data-config="{{ json_encode($g->config_schema) }}">
                        {{ $g->icon }} {{ $g->name }} {{ $g->module ? '(' . ucfirst($g->module) . ')' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group"><label class="form-label">Service Name *</label><input type="text" name="name" class="form-input" value="{{ old('name') }}" required></div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="2">{{ old('description') }}</textarea></div>
        <div style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:end;">
            <div class="form-group"><label class="form-label">Catalog Badge</label><input type="text" name="badge_label" class="form-input" value="{{ old('badge_label') }}" placeholder="NEW, FEATURED, POPULAR"></div>
            <label class="toggle-switch" style="margin-bottom:16px;"><input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}><span class="toggle-slider"></span><span>Featured</span></label>
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
                <div class="form-hint">Select the server JamVini should use when creating hosting accounts.</div>
            </div>
            <div class="form-group">
                <label class="form-label">WHM Package</label>
                <select name="package_name" id="hostingPackageSelect" class="form-select" data-selected="{{ $selectedPackageName ?? '' }}">
                    <option value="">— Select server first —</option>
                </select>
                <div class="form-hint">Choose the synced WHM package this service should create.</div>
            </div>
        </div>

        {{-- Module Configuration (injected by module plugins) --}}
        <div id="moduleConfigContainer" style="margin-top: 16px;">
            <p style="color: var(--jv-gray-500);">Select a service group to configure module-specific settings.</p>
        </div>
    </div>

    {{-- Domain Configuration --}}
    @include('plugins.Services::admin.domain-config')

    {{-- Pricing --}}
    <div id="pricingCard" class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>💰 Pricing</h3></div>
        <div class="form-group">
            <label class="toggle-switch">
                <input type="checkbox" name="is_free" value="1" id="isFreeToggle" onchange="togglePricing()">
                <span class="toggle-slider"></span><span>This is a free service</span>
            </label>
        </div>
        <div id="pricingSection">
            <div class="form-group">
                <label class="form-label">Billing Type</label>
                <select name="billing_type" class="form-select" style="width: 200px;">
                    <option value="recurring">Recurring</option>
                    <option value="one-time">One-Time</option>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                <div class="form-group"><label class="form-label">Monthly ({{ \App\Models\Setting::get('currency', 'TZS') }})</label><input type="number" name="pricing[monthly]" class="form-input" step="0.01" placeholder="e.g. 25000"></div>
                <div class="form-group"><label class="form-label">Quarterly ({{ \App\Models\Setting::get('currency', 'TZS') }})</label><input type="number" name="pricing[quarterly]" class="form-input" step="0.01"></div>
                <div class="form-group"><label class="form-label">Semi-Annual ({{ \App\Models\Setting::get('currency', 'TZS') }})</label><input type="number" name="pricing[semi_annually]" class="form-input" step="0.01"></div>
                <div class="form-group"><label class="form-label">Annual ({{ \App\Models\Setting::get('currency', 'TZS') }})</label><input type="number" name="pricing[annually]" class="form-input" step="0.01"></div>
            </div>
            <div class="form-group"><label class="form-label">Setup Fee ({{ \App\Models\Setting::get('currency', 'TZS') }})</label><input type="number" name="setup_fee" class="form-input" value="0" style="width: 200px;"></div>

            <div id="freeDomainSection" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Free Domain on Billing Cycles</label>
                    <div style="display: flex; gap: 16px;">
                        @foreach(['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'semi_annually' => 'Semi-Annual', 'annually' => 'Annual'] as $k => $l)
                            <label class="checkbox-group"><input type="checkbox" name="free_domain_cycles[]" value="{{ $k }}"><span>{{ $l }}</span></label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Configurable Options --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head" style="display: flex; justify-content: space-between;">
            <h3>⚙️ Configurable Options</h3>
            <button type="button" class="btn btn-sm btn-primary" onclick="addOption()">➕ Add Option</button>
        </div>
        <div id="optionsContainer">
            <p style="color: var(--jv-gray-500);">Add configurable options like extra disk space, bandwidth, etc.</p>
        </div>
    </div>

    {{-- Custom Fields --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head" style="display: flex; justify-content: space-between;">
            <h3>{{ jv_icon('clipboard-list', '', 18) }} Custom Fields</h3>
            <button type="button" class="btn btn-sm btn-primary" onclick="addCustomField()">Add Field</button>
        </div>
        <div id="customFieldsContainer">
            <p style="color: var(--jv-gray-500);">Collect extra signup details such as website URL, preferred domain, or migration notes.</p>
        </div>
    </div>

    {{-- Product Addons --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head" style="display: flex; justify-content: space-between;">
            <h3>{{ jv_icon('plus-circle', '', 18) }} Product Addons</h3>
            <button type="button" class="btn btn-sm btn-primary" onclick="addAddon()">Add Addon</button>
        </div>
        <div id="addonsContainer">
            <p style="color: var(--jv-gray-500);">Offer optional extras such as migration, premium SSL setup, backups, or managed support.</p>
        </div>
    </div>

    {{-- Features --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>✅ Features</h3></div>
        <textarea name="features" class="form-textarea" rows="5" placeholder="1 Website&#10;10 GB SSD Storage&#10;Free SSL">{{ old('features') }}</textarea>
    </div>

    {{-- Upgrade/Downgrade --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>🔄 Upgrade/Downgrade</h3></div>
        <div style="display: flex; gap: 24px;">
            <input type="hidden" name="upgradable" value="0">
            <label class="toggle-switch"><input type="checkbox" name="upgradable" value="1" checked><span class="toggle-slider"></span><span>Allow Upgrades</span></label>
            <input type="hidden" name="allow_downgrade" value="0">
            <label class="toggle-switch"><input type="checkbox" name="allow_downgrade" value="1"><span class="toggle-slider"></span><span>Allow Downgrades</span></label>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 1.5rem;">
        <a href="{{ route('admin.services.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">✅ Add to Catalog</button>
    </div>
</form>
@endsection

@push('scripts')
@include('plugins.Services::admin.partials.domain-scripts')
<script>
const serviceId = null;
const currencyCode = @json(\App\Models\Setting::get('currency', 'TZS'));
const packagesByServer = @json($serverPackages);

async function toggleGroupConfig() {
    const groupSelect = document.getElementById('group_id');
    const option = groupSelect.options[groupSelect.selectedIndex];
    const module = option?.dataset?.module || '';
    
    // Show/hide pricing based on module
    document.getElementById('pricingCard').style.display = module === 'domains' ? 'none' : '';
    document.getElementById('freeDomainSection').style.display = module === 'hosting' ? '' : 'none';
    document.getElementById('provisioningGroup').style.display = module === 'hosting' ? '' : 'none';
    document.getElementById('domainConfig').style.display = module === 'domains' ? '' : 'none';
    updateDomainRequiredState(module === 'domains');
    
    // Load module config dynamically
    const configContainer = document.getElementById('moduleConfigContainer');
    if (module === 'hosting') {
        configContainer.innerHTML = '';
        renderHostingPackages();
    } else if (module) {
        try {
            const res = await fetch(`/admin/module-config/${module}${serviceId ? '?service_id=' + serviceId : ''}`);
            if (res.ok) {
                configContainer.innerHTML = await res.text();
            }
        } catch(e) {
            configContainer.innerHTML = '<p style="color: var(--jv-gray-500);">Could not load configuration for this module.</p>';
        }
    } else {
        configContainer.innerHTML = '<p style="color: var(--jv-gray-500);">Select a service group to configure module-specific settings.</p>';
    }
}

function updateDomainRequiredState(isDomainGroup) {
    document.querySelectorAll('#domainConfig [data-domain-required="true"]').forEach((input) => {
        input.required = isDomainGroup;
    });
}

function renderHostingPackages() {
    const serverSelect = document.getElementById('hostingServerSelect');
    const packageSelect = document.getElementById('hostingPackageSelect');

    if (!serverSelect || !packageSelect) return;

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

function togglePricing() {
    document.getElementById('pricingSection').style.display = document.getElementById('isFreeToggle').checked ? 'none' : '';
}
let optionCount = 0;
function addOption() {
    const container = document.getElementById('optionsContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="option-row" style="display: grid; grid-template-columns: 1fr 150px 1fr 150px auto; gap: 12px; padding: 12px; margin-bottom: 8px; background: var(--jv-gray-50); border-radius: 8px;">
            <div class="form-group" style="margin:0"><label class="form-label">Option Name</label><input type="text" name="options[${optionCount}][name]" class="form-input" placeholder="Extra Disk Space"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Type</label><select name="options[${optionCount}][type]" class="form-select"><option value="number">Number</option><option value="dropdown">Dropdown</option><option value="checkbox">Checkbox</option></select></div>
            <div class="form-group" style="margin:0"><label class="form-label">Choices</label><input type="text" name="options[${optionCount}][choices]" class="form-input" placeholder="One per line or leave blank"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Monthly Price (${currencyCode})</label><input type="number" name="options[${optionCount}][price_monthly]" class="form-input" placeholder="5000" step="0.01"></div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.option-row').remove()" style="align-self:flex-end">✕</button>
        </div>
    `);
    optionCount++;
}
let customFieldCount = 0;
function addCustomField() {
    const container = document.getElementById('customFieldsContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="custom-field-row" style="display:grid;grid-template-columns:1fr 150px 1fr 1fr auto;gap:12px;padding:12px;margin-bottom:8px;background:var(--jv-gray-50);border-radius:8px;">
            <div class="form-group" style="margin:0"><label class="form-label">Label</label><input type="text" name="custom_fields[${customFieldCount}][label]" class="form-input" placeholder="Website URL"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Type</label><select name="custom_fields[${customFieldCount}][type]" class="form-select"><option value="text">Text</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="checkbox">Checkbox</option><option value="url">URL</option><option value="number">Number</option></select></div>
            <div class="form-group" style="margin:0"><label class="form-label">Options</label><input type="text" name="custom_fields[${customFieldCount}][options]" class="form-input" placeholder="For select fields"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Placeholder</label><input type="text" name="custom_fields[${customFieldCount}][placeholder]" class="form-input"></div>
            <div style="display:flex;gap:8px;align-items:end;"><label class="checkbox-group"><input type="checkbox" name="custom_fields[${customFieldCount}][is_required]" value="1"><span>Required</span></label><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.custom-field-row').remove()">✕</button></div>
        </div>
    `);
    customFieldCount++;
}
let addonCount = 0;
function addAddon() {
    const container = document.getElementById('addonsContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="addon-row" style="display:grid;grid-template-columns:1fr 1fr 140px 150px auto;gap:12px;padding:12px;margin-bottom:8px;background:var(--jv-gray-50);border-radius:8px;">
            <div class="form-group" style="margin:0"><label class="form-label">Name</label><input type="text" name="addons[${addonCount}][name]" class="form-input" placeholder="Website Migration"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Description</label><input type="text" name="addons[${addonCount}][description]" class="form-input"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Price (${currencyCode})</label><input type="number" name="addons[${addonCount}][price]" class="form-input" step="0.01" value="0"></div>
            <div class="form-group" style="margin:0"><label class="form-label">Billing</label><select name="addons[${addonCount}][billing_cycle]" class="form-select"><option value="same_as_parent">Same as parent</option><option value="one-time">One-time</option><option value="monthly">Monthly</option><option value="annually">Annual</option></select></div>
            <div style="display:flex;gap:8px;align-items:end;"><label class="checkbox-group"><input type="checkbox" name="addons[${addonCount}][is_required]" value="1"><span>Required</span></label><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.addon-row').remove()">✕</button></div>
        </div>
    `);
    addonCount++;
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('hostingServerSelect')?.addEventListener('change', function() {
        const packageSelect = document.getElementById('hostingPackageSelect');
        if (packageSelect) packageSelect.dataset.selected = '';
        renderHostingPackages();
    });
    toggleGroupConfig();
});
</script>
@endpush
