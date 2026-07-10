@extends('themes.default::layouts.admin')

@section('title', 'New Order')
@section('breadcrumbs')<a href="{{ route('admin.orders.index') }}">Orders</a> <span class="separator">/</span> <span class="current">New</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Add New Order</h1>
</div>

<form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
    @csrf
    
    <div class="grid-2" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
        {{-- Left Column --}}
        <div>
            {{-- Client & Status --}}
            <div class="dash-card" style="margin-bottom: 1.5rem;">
                <div class="dash-card-head"><h3>📋 Order Details</h3></div>
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: end;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Client *</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">Select client...</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ (string) old('client_id', request('client_id')) === (string) $c->id ? 'selected' : '' }}>{{ $c->full_name }} ({{ $c->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ route('admin.clients.create') }}" target="_blank" class="btn btn-lm btn-outline-primary" style="margin-bottom: 0;">➕ New Client</a>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="accepted">Active (auto-generate invoice)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">— Default —</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="tigo">Tigo Pesa</option>
                            <option value="airtel">Airtel Money</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px;">
                    <div class="form-group" style="margin:0;"><label class="form-label">Source</label><input type="text" name="source" class="form-input" value="{{ old('source', 'admin') }}" placeholder="admin, website, WHMCS"></div>
                    <div class="form-group" style="margin:0;"><label class="form-label">External / WHMCS ID</label><input type="text" name="external_id" class="form-input" value="{{ old('external_id') }}" placeholder="Optional migration reference"></div>
                </div>
                <div style="display: flex; gap: 24px; margin-top: 12px;">
                    <label class="checkbox-group"><input type="checkbox" name="adminorderconf" checked> Order Confirmation</label>
                    <label class="checkbox-group"><input type="checkbox" name="admingenerateinvoice" checked> Generate Invoice</label>
                </div>
            </div>

            {{-- Products/Services --}}
            <div class="dash-card" style="margin-bottom: 1.5rem;">
                <div class="dash-card-head"><h3>📦 Product/Service</h3></div>
                <div id="products">
                    <p style="color: var(--jv-gray-500); font-size: 0.9rem;">Click "Add Product" to add hosting, SSL, or other services.</p>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="addProduct()" style="margin-top: 12px;">➕ Add Product</button>
            </div>

            {{-- Domain Registration --}}
            <div class="dash-card" style="margin-bottom: 1.5rem;">
                <div class="dash-card-head"><h3>🌐 Domain Registration</h3></div>
                <div id="domains">
                    <p style="color: var(--jv-gray-500); font-size: 0.9rem;">Click "Add Another Domain" to add domain registrations or transfers.</p>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="addDomain()" style="margin-top: 12px;">➕ Add Domain</button>
            </div>

            <div class="dash-card" style="margin-bottom: 1.5rem;">
                <div class="dash-card-head"><h3>📝 Notes</h3></div>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="Order notes..."></textarea>
            </div>
        </div>

        {{-- Right Column: Summary --}}
        <div class="dash-card" style="position: sticky; top: 80px;">
            <div class="dash-card-head"><h3>📊 Order Summary</h3></div>
            <div id="orderSummary">
                <p style="color: var(--jv-gray-500); text-align: center;">Add products or domains above</p>
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 16px; width: 100%;">✅ Submit Order</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const allServices = @json($services->flatten());
const nonDomainServices = allServices.filter(s => (s.group?.module || '') !== 'domains');
let productCount = 0, domainCount = 0;
const moneyCurrency = @json(\App\Models\Setting::get('currency', 'TZS'));
const moneyDecimals = {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }};
const defaultTaxRate = {{ (float) jv_tax_rate() }};
const taxLabel = @json(jv_tax_label());
function formatMoney(amount) {
    return moneyCurrency + ' ' + Number(amount || 0).toLocaleString(undefined, {
        minimumFractionDigits: moneyDecimals,
        maximumFractionDigits: moneyDecimals
    });
}

// ==================== TLD LOOKUP ====================
const tldConfigs = @json($tldConfigs ?? []);
const tldMap = {};
tldConfigs.forEach(cfg => { tldMap[cfg.tld.toLowerCase()] = cfg; });

function getTldFromDomain(domain) {
    if (!domain || !domain.includes('.')) return null;
    const parts = domain.split('.');
    if (parts.length >= 3 && ['co', 'or', 'go', 'ac'].includes(parts[parts.length - 2])) {
        return '.' + parts[parts.length - 2] + '.' + parts[parts.length - 1];
    }
    return '.' + parts[parts.length - 1];
}

function getTldPrice(domain, years = 1) {
    const tld = getTldFromDomain(domain);
    if (!tld || !tldMap[tld]) return null;
    const pricing = tldMap[tld].pricing?.find(p => p.years == years) || tldMap[tld].pricing?.[0];
    return pricing ? {
        register: pricing.register_price,
        renewal: pricing.renewal_price,
        transfer: pricing.transfer_price,
        tld: tld,
        config: tldMap[tld]
    } : null;
}

// ==================== PRODUCTS ====================
function addProduct() {
    const idx = productCount++;
    let opts = '<option value="">— Select Service —</option>';
    nonDomainServices.forEach(s => {
        const price = s.pricing?.monthly || s.pricing?.annually || s.amount || 0;
        opts += `<option value="${s.id}" data-price="${price}" data-billing="${s.billing_cycle || 'monthly'}" data-name="${s.name}" data-module="${s.group?.module || 'custom'}">${s.group?.icon || ''} ${s.name} (${formatMoney(price)})</option>`;
    });

    document.getElementById('products').insertAdjacentHTML('beforeend', `
        <div class="product-item" style="border: 1px solid var(--jv-gray-200); border-radius: 12px; padding: 16px; margin-bottom: 12px; background: var(--jv-gray-50);">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: end;">
                <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem;">Product/Service</label><select name="items[p_${idx}][service_id]" class="form-select item-service" onchange="autoFillProduct(this)">${opts}</select></div>
                <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem;">Billing</label>
                    <select name="items[p_${idx}][billing_cycle]" class="form-select item-billing" onchange="updateSummary()">
                        <option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semi_annually">Semi-Annual</option><option value="annually">Annually</option><option value="one-time">One Time</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem;">Price (${moneyCurrency})</label><input type="number" name="items[p_${idx}][unit_price]" class="form-input item-price" value="0" step="0.01" onchange="updateSummary()"></div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.product-item').remove();updateSummary();">✕</button>
            </div>
            <div class="form-group product-domain-group" style="margin-top:8px;display:none;"><label class="form-label" style="font-size:0.75rem;">Domain for Hosting *</label><input type="text" name="items[p_${idx}][domain]" class="form-input product-domain" placeholder="example.com"></div>
            <input type="hidden" name="items[p_${idx}][type]" value="custom">
            <input type="hidden" name="items[p_${idx}][description]" value="">
            <input type="hidden" name="items[p_${idx}][quantity]" value="1">
        </div>
    `);
    updateSummary();
}

function autoFillProduct(select) {
    const row = select.closest('.product-item');
    const option = select.options[select.selectedIndex];
    if (option.value) {
        row.querySelector('input[name$="[description]"]').value = option.dataset.name || '';
        row.querySelector('.item-price').value = option.dataset.price || 0;
        row.querySelector('.item-billing').value = option.dataset.billing || 'monthly';
        row.querySelector('input[name$="[type]"]').value = option.dataset.module || 'custom';
        const domainGroup = row.querySelector('.product-domain-group');
        const domainInput = row.querySelector('.product-domain');
        const requiresDomain = option.dataset.module === 'hosting';
        domainGroup.style.display = requiresDomain ? '' : 'none';
        domainInput.required = requiresDomain;
        if (!requiresDomain) domainInput.value = '';
    }
    updateSummary();
}

// ==================== DOMAINS ====================
function addDomain() {
    const idx = domainCount++;
    document.getElementById('domains').insertAdjacentHTML('beforeend', `
        <div class="domain-item" style="border: 1px solid var(--jv-gray-200); border-radius: 12px; padding: 16px; margin-bottom: 12px; background: var(--jv-gray-50);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <strong>🌐 Domain #${idx + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.domain-item').remove();updateSummary();">✕ Remove</button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem;">Registration Type</label>
                    <select name="items[d_${idx}][reg_type]" class="form-select domain-reg-action" onchange="toggleDomainFields(this);autoDetectDomainPrice(this.closest('.domain-item'));">
                        <option value="">None</option><option value="register">Register</option><option value="transfer">Transfer</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem;">Domain</label><input type="text" name="items[d_${idx}][domain]" class="form-input input-reg-domain" placeholder="example.co.tz" onkeyup="autoDetectDomainPrice(this.closest('.domain-item'))"></div>
                <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem;">Years</label>
                    <select name="items[d_${idx}][years]" class="form-select" onchange="autoDetectDomainPrice(this.closest('.domain-item'))">
                        ${[1,2,3,5,10].map(y => `<option value="${y}">${y} Year${y>1?'s':''}</option>`).join('')}
                    </select>
                </div>
            </div>
            <div class="domain-extra" style="display: none; margin-top: 12px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group" style="margin:0">
                        <label class="form-label" style="font-size:0.75rem;">Price (${moneyCurrency}/yr) <span style="color:var(--jv-gray-400);">— auto from TLD</span></label>
                        <input type="number" name="items[d_${idx}][unit_price]" class="form-input item-price" value="0" step="0.01" onchange="updateSummary()">
                        <small class="tld-info" style="color: var(--jv-primary); display: none;"></small>
                        <small class="tld-warning" style="color: #dc2626; display: none;"></small>
                    </div>
                    <div class="form-group" style="margin:0"><label class="form-label" style="font-size:0.75rem;">Price Override</label><input type="number" name="items[d_${idx}][price_override]" class="form-input" value="" step="0.01" placeholder="Manual override" onchange="updateSummary()"></div>
                </div>
                <div class="form-group domain-eppcode-group" style="margin:0; display: none;"><label class="form-label" style="font-size:0.75rem;">EPP Code</label><input type="text" name="items[d_${idx}][epp_code]" class="form-input" placeholder="Required for transfer"></div>
                <div style="display: flex; gap: 16px; margin-top: 12px; flex-wrap: wrap;">
                    <label class="checkbox-group"><input type="checkbox" name="items[d_${idx}][dns_management]" value="1"> DNS Management</label>
                    <label class="checkbox-group"><input type="checkbox" name="items[d_${idx}][email_forwarding]" value="1"> Email Forwarding</label>
                    <label class="checkbox-group"><input type="checkbox" name="items[d_${idx}][id_protection]" value="1"> ID Protection</label>
                </div>
            </div>
            <input type="hidden" name="items[d_${idx}][type]" value="domain">
            <input type="hidden" name="items[d_${idx}][description]" value="Domain Registration">
            <input type="hidden" name="items[d_${idx}][quantity]" value="1">
        </div>
    `);
}

function autoDetectDomainPrice(row) {
    const domain = row.querySelector('.input-reg-domain')?.value || '';
    const years = parseInt(row.querySelector('select[name$="[years]"]')?.value) || 1;
    const regType = row.querySelector('.domain-reg-action')?.value || '';
    const priceInput = row.querySelector('.item-price');
    const tldInfo = row.querySelector('.tld-info');
    const tldWarning = row.querySelector('.tld-warning');
    
    if (!domain || !regType) {
        if (tldInfo) tldInfo.style.display = 'none';
        if (tldWarning) tldWarning.style.display = 'none';
        updateSummary();
        return;
    }
    
    const tldPrice = getTldPrice(domain, years);
    
    if (tldPrice) {
        let price = regType === 'transfer' ? tldPrice.transfer : tldPrice.register;
        priceInput.value = price;
        if (tldInfo) {
            tldInfo.textContent = `✅ TLD ${tldPrice.tld} found — ${formatMoney(price)}/yr`;
            tldInfo.style.display = '';
        }
        if (tldWarning) tldWarning.style.display = 'none';
    } else {
        priceInput.value = 0;
        if (tldWarning) {
            tldWarning.textContent = '⚠️ TLD not configured. Enter price override or contact admin.';
            tldWarning.style.display = '';
        }
        if (tldInfo) tldInfo.style.display = 'none';
    }
    updateSummary();
}

function toggleDomainFields(select) {
    const row = select.closest('.domain-item');
    const extra = row.querySelector('.domain-extra');
    const eppGroup = row.querySelector('.domain-eppcode-group');
    const regType = select.value;
    extra.style.display = regType ? '' : 'none';
    if (eppGroup) eppGroup.style.display = regType === 'transfer' ? '' : 'none';
    autoDetectDomainPrice(row);
}

// ==================== SUMMARY ====================
function updateSummary() {
    let total = 0;
    let items = [];

    document.querySelectorAll('.product-item').forEach(row => {
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const name = row.querySelector('input[name$="[description]"]')?.value || '';
        if (price > 0) {
            total += price;
            items.push(`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.85rem;"><span>📦 ${name || 'Product'}</span><strong>${formatMoney(price)}</strong></div>`);
        }
    });

    document.querySelectorAll('.domain-item').forEach(row => {
        const priceInput = row.querySelector('.item-price');
        const overrideInput = row.querySelector('input[name$="[price_override]"]');
        const price = parseFloat(overrideInput?.value || priceInput?.value) || 0;
        const domain = row.querySelector('.input-reg-domain')?.value || '';
        const years = parseInt(row.querySelector('select[name$="[years]"]')?.value) || 1;
        const domainTotal = price * years;
        if (price > 0 && domain) {
            total += domainTotal;
            items.push(`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:.85rem;"><span>🌐 ${domain} (${years}yr)</span><strong>${formatMoney(domainTotal)}</strong></div>`);
        }
    });

    const tax = total * (defaultTaxRate / 100);
    const grandTotal = total + tax;

    document.getElementById('orderSummary').innerHTML = items.length > 0
        ? items.join('') + `
            <hr style="margin:12px 0;">
            <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><strong>${formatMoney(total)}</strong></div>
            <div style="display:flex;justify-content:space-between;"><span>${taxLabel} (${defaultTaxRate}%)</span><strong>${formatMoney(tax)}</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;margin-top:8px;"><span>Total</span><strong>${formatMoney(grandTotal)}</strong></div>
        `
        : '<p style="color:var(--jv-gray-500);text-align:center;">Add products or domains above</p>';
}
</script>
@endpush
