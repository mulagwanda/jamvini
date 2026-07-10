@extends('themes.default::layouts.frontend')

@section('title', 'Domain Search')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / Domain Search</div>
        <h1>Find your perfect domain</h1>
        <p>Search across multiple TLDs with real-time availability</p>
        
        {{-- Mode Toggle --}}
        <div style="display: flex; justify-content: center; gap: 8px; margin: 2rem 0 20px;">
            <label id="modeRegister" style="cursor: pointer; padding: 10px 24px; border-radius: 999px; font-size: 0.88rem; font-weight: 600; background: #6C5CE7; color: #fff; border: 2px solid #6C5CE7; transition: all .2s;" onclick="setMode('register')">
                🟢 Register
            </label>
            <label id="modeTransfer" style="cursor: pointer; padding: 10px 24px; border-radius: 999px; font-size: 0.88rem; font-weight: 600; background: transparent; color: #c4b5fd; border: 2px solid rgba(255,255,255,.2); transition: all .2s;" onclick="setMode('transfer')">
                🔄 Transfer
            </label>
        </div>

        <div class="domain-search" style="margin-top: 0;">
            <input type="text" id="domainInput" placeholder="e.g. mybusiness.co.tz" value="{{ $query ?? '' }}" />
            <button type="button" class="btn btn-primary" onclick="searchDomain()">🔍 Search</button>
        </div>
    </div>
</section>

<section>
    <div class="container">
        {{-- Loading Spinner --}}
        <div id="searchLoader" style="display: none; text-align: center; padding: 60px;">
            <div style="font-size: 48px; animation: pulse 1.5s infinite;">🔍</div>
            <p style="color: var(--gray-500); margin-top: 16px;">Checking domain availability...</p>
        </div>

        {{-- Results --}}
        <div id="domainResults"></div>

        {{-- Suggestions --}}
        <div id="suggestionsSection" style="display: none; margin-top: 3rem;">
            <div class="section-head">
                <span class="eyebrow">Suggestions</span>
                <h2>Other available domains</h2>
            </div>
            <div id="suggestionsGrid" class="features-grid"></div>
        </div>

        {{-- Initial State --}}
        <div id="initialState" style="padding: 3rem; text-align: center; color: var(--gray-400);">
            <div style="font-size: 64px;">🌐</div>
            <h3>Search for your domain</h3>
            <p>Enter a domain name above to check availability and pricing.</p>
        </div>
    </div>
</section>

{{-- Popular TLDs --}}
<section class="bg-gray">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">TLDs</span>
            <h2>Popular extensions</h2>
        </div>
        <div class="features-grid">
            @php
                $popularTlds = \Plugins\Domains\src\Models\DomainTld::where('is_active', true)
                    ->with(['pricing' => fn($q) => $q->where('years', 1)])
                    ->take(6)->get();
            @endphp
            @foreach($popularTlds as $tld)
            <div class="feature-card" style="text-align: center;">
                <h3>{{ $tld->tld }}</h3>
                <p style="margin-top: .5rem;">
                    <strong style="color: var(--primary); font-size: 1.4rem;">
                        {{ jv_format_money($tld->pricing->first()?->register_price ?? 0) }}
                    </strong>/yr
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    let searchMode = 'register';
    let isSearching = false;
    const moneyCurrency = @json(\App\Models\Setting::get('currency', 'TZS'));
    const moneyDecimals = {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }};

    function formatMoney(amount) {
        return moneyCurrency + ' ' + Number(amount || 0).toLocaleString(undefined, {
            minimumFractionDigits: moneyDecimals,
            maximumFractionDigits: moneyDecimals
        });
    }

    // Pre-fill from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const domainParam = urlParams.get('domain');
    const typeParam = urlParams.get('type');

    window.setMode = function(mode, shouldSearch = true) {
        searchMode = mode;
        const reg = document.getElementById('modeRegister');
        const trans = document.getElementById('modeTransfer');
        if (mode === 'register') {
            reg.style.background = '#6C5CE7'; reg.style.color = '#fff'; reg.style.border = '2px solid #6C5CE7';
            trans.style.background = 'transparent'; trans.style.color = '#c4b5fd'; trans.style.border = '2px solid rgba(255,255,255,.2)';
        } else {
            trans.style.background = '#6C5CE7'; trans.style.color = '#fff'; trans.style.border = '2px solid #6C5CE7';
            reg.style.background = 'transparent'; reg.style.color = '#c4b5fd'; reg.style.border = '2px solid rgba(255,255,255,.2)';
        }
        const q = document.getElementById('domainInput').value.trim();
        if (shouldSearch && q) searchDomain();
    };

    if (typeParam === 'transfer') {
        window.setMode('transfer', false);
    }
    if (domainParam) {
        document.getElementById('domainInput').value = domainParam;
        setTimeout(() => searchDomain(), 300);
    }

    window.searchDomain = async function() {
        const domain = document.getElementById('domainInput').value.trim();
        if (!domain || isSearching) return;
        
        isSearching = true;
        document.getElementById('initialState').style.display = 'none';
        document.getElementById('domainResults').innerHTML = '';
        document.getElementById('searchLoader').style.display = 'block';
        document.getElementById('suggestionsSection').style.display = 'none';

        try {
            const res = await fetch('{{ route('order.check') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ domain: domain, type: searchMode })

            });
            const data = await res.json();
            document.getElementById('searchLoader').style.display = 'none';
            renderResults(data);
        } catch (e) {
            document.getElementById('searchLoader').style.display = 'none';
            document.getElementById('domainResults').innerHTML = '<p style="text-align:center;color:#dc2626;">Error. Try again.</p>';
        }
        isSearching = false;
    };

    function renderResults(data) {
    const container = document.getElementById('domainResults');
    const result = data.result;
    const suggestions = data.suggestions || [];
    const type = data.type || searchMode;
    let html = '';

    const tldConfig = result.tld_config;
    const price = tldConfig?.pricing?.[0];
    
    if (type === 'transfer') {
        // Transfer mode
        const transferPrice = price?.transfer || 0;
        const isEligible = result.transfer_eligible;
        
        html += `<div class="domain-results" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden;">`;
        html += `<div class="domain-row" style="color:#0F172A;">`;
        html += `<div class="domain-name" style="color:#0F172A;">${result.domain}`;
        if (tldConfig && isEligible) {
            html += `<small style="display:block;color:var(--gray-600);">Transfer price: ${formatMoney(transferPrice)}/yr</small>`;
        }
        html += `</div>`;
        html += `<div class="${isEligible ? 'status-available' : 'status-taken'}">${result.transfer_message || result.message}</div>`;
        html += `<div>${tldConfig && isEligible ? '<strong>' + formatMoney(transferPrice) + '</strong>/yr' : '<strong>—</strong>'}</div>`;
        
        if (isEligible && tldConfig) {
            html += `<button class="btn btn-primary cart-btn" onclick="openTransferConfig('${result.domain}', ${transferPrice}, ${JSON.stringify(tldConfig).replace(/"/g, '&quot;')})">🔄 Transfer Domain</button>`;
        } else {
            html += `<button class="btn btn-outline" disabled style="opacity:.5;">${tldConfig ? 'Cannot Transfer' : 'Unavailable'}</button>`;
        }
        html += `</div></div>`;
    } else {
        // Register mode (existing code)
        const displayPrice = price?.register || 0;
        
        html += `<div class="domain-results" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.04);overflow:hidden;">`;
        html += `<div class="domain-row" style="color:#0F172A;">`;
        html += `<div class="domain-name" style="color:#0F172A;">${result.domain}`;
        if (tldConfig && result.available) {
            html += `<small style="display:block;color:var(--gray-600);">${formatMoney(displayPrice)}/yr</small>`;
        }
        html += `</div>`;
        html += `<div class="${result.available ? 'status-available' : 'status-taken'}">${result.message}</div>`;
        html += `<div>${tldConfig && result.available ? '<strong>' + formatMoney(displayPrice) + '</strong>/yr' : '<strong>—</strong>'}</div>`;
        
        if (result.available && tldConfig) {
            html += `<button class="btn btn-primary cart-btn" onclick="openDomainConfig('${result.domain}', ${displayPrice}, ${JSON.stringify(tldConfig).replace(/"/g, '&quot;')})">Add to Cart</button>`;
        } else {
            html += `<button class="btn btn-outline" disabled style="opacity:.5;">${tldConfig ? 'Taken' : 'Unavailable'}</button>`;
        }
        html += `</div></div>`;
    }
    container.innerHTML = html;

    // Suggestions (only for register mode)
    if (suggestions.length > 0 && type !== 'transfer') {
        let sugHtml = '';
        suggestions.forEach(s => {
            const sp = s.tld_config?.pricing?.[0];
            const spPrice = searchMode === 'transfer' ? (sp?.transfer || 0) : (sp?.register || 0);
            sugHtml += `<div class="feature-card" style="text-align:center;background:#fff;">`;
            sugHtml += `<h3>${s.domain}</h3>`;
            sugHtml += `<p><strong style="color:var(--primary);font-size:1.3rem;">${formatMoney(spPrice)}</strong>/yr</p>`;
            sugHtml += `<button class="btn btn-outline btn-sm cart-btn" onclick="openDomainConfig('${s.domain}', ${spPrice}, ${JSON.stringify(s.tld_config).replace(/"/g, '&quot;')})">Add to Cart</button>`;
            sugHtml += `</div>`;
        });
        document.getElementById('suggestionsGrid').innerHTML = sugHtml;
        document.getElementById('suggestionsSection').style.display = '';
    }
}

    function bindCartButtons() {
    document.querySelectorAll('.cart-btn').forEach(btn => {
        btn.onclick = async function() {
            const domain = this.dataset.domain;
            const price = parseFloat(this.dataset.price);
            const res = await fetch('{{ route('order.cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type: 'domain', name: domain, price: price, details: { billing_cycle: 'annually' } })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success', title: 'Added to Cart!', text: domain + ' has been added.',
                    showCancelButton: true, confirmButtonText: '🛒 View Cart', cancelButtonText: 'Continue',
                    confirmButtonColor: '#6C5CE7',
                }).then((r) => { if (r.isConfirmed) window.location.href = '{{ route('order.cart') }}'; });
            }
        };
    });
}

    // Enter key
    document.getElementById('domainInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchDomain();
    });


    let domainConfig = {};

function openDomainConfig(domain, price, tldConfig) {
    // Generate year options from pricing data
    let yearOptions = '';
    if (tldConfig?.pricing && tldConfig.pricing.length > 0) {
        tldConfig.pricing.forEach(p => {
            const yearPrice = searchMode === 'transfer' ? p.transfer : p.register;
            yearOptions += `<option value="${p.years}" data-price="${yearPrice}">${p.years} Year${p.years > 1 ? 's' : ''} — ${formatMoney(yearPrice)}</option>`;
        });
    } else {
        // Fallback: default years
        [1, 2, 3, 5, 10].forEach(y => {
            yearOptions += `<option value="${y}" data-price="${price}">${y} Year${y > 1 ? 's' : ''} — ${formatMoney(price * y)}</option>`;
        });
    }

    // Build modal HTML with dynamic years
    const content = `
        <div style="text-align: left; padding: 10px 0;">
            <div style="background: #f8fafc; border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                <strong style="font-size: 1.1rem;">${domain}</strong>
                <span class="pill pill-ok" style="margin-left: 8px;">Available</span>
            </div>
            <div class="form-group">
                <label class="form-label">Registration Period</label>
                <select id="configYears" class="form-select" onchange="updateConfigModalTotal(${price})">
                    ${yearOptions}
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nameservers</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <input type="text" id="configNs1" class="form-input" value="{{ \App\Models\Setting::get('domain_default_ns1', 'ns1.jamvini.co.tz') }}" placeholder="NS1">
                    <input type="text" id="configNs2" class="form-input" value="{{ \App\Models\Setting::get('domain_default_ns2', 'ns2.jamvini.co.tz') }}" placeholder="NS2">
                    <input type="text" id="configNs3" class="form-input" value="{{ \App\Models\Setting::get('domain_default_ns3', '') }}" placeholder="NS3 (optional)">
                    <input type="text" id="configNs4" class="form-input" value="{{ \App\Models\Setting::get('domain_default_ns4', '') }}" placeholder="NS4 (optional)">
                </div>
            </div>
            <div style="margin-top: 12px;">
                <label class="form-label" style="margin-bottom: 8px;">Addons</label>
                <div id="addonList">${buildAddonHtml(tldConfig)}</div>
            </div>
            <div style="background: #f1f5f9; border-radius: 10px; padding: 14px; margin-top: 16px; display: flex; justify-content: space-between;">
                <span style="font-weight: 600;">Total:</span>
                <strong style="font-size: 1.2rem; color: var(--primary);" id="configTotal">${formatMoney(price)}</strong>
            </div>
        </div>
    `;

    Swal.fire({
        title: 'Configure Domain',
        html: content,
        width: '560px',
        showCancelButton: true,
        confirmButtonText: '🛒 Add to Cart',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#6C5CE7',
        preConfirm: () => {
            const years = parseInt(document.getElementById('configYears')?.value || 1);
            const selectedPrice = parseFloat(document.getElementById('configYears')?.selectedOptions[0]?.dataset?.price || price);
            const addons = [];
            document.querySelectorAll('.config-addon:checked').forEach(cb => {
                addons.push({ name: cb.dataset.addon, price: parseFloat(cb.dataset.price) });
            });
            return {
                domain, years, basePrice: selectedPrice,
                nameservers: [
                    document.getElementById('configNs1')?.value || '',
                    document.getElementById('configNs2')?.value || '',
                    document.getElementById('configNs3')?.value || '',
                    document.getElementById('configNs4')?.value || '',
                ].filter(ns => ns !== ''),
                addons
            };
        }
    }).then(async (r) => {
        if (r.isConfirmed && r.value) await addDomainToCart(r.value);
    });
}


function openTransferConfig(domain, price, tldConfig) {
    let yearOptions = '';
    if (tldConfig?.pricing && tldConfig.pricing.length > 0) {
        tldConfig.pricing.forEach(p => {
            if ((p.transfer || 0) > 0) {
                yearOptions += `<option value="${p.years}" data-price="${p.transfer}">${p.years} Year${p.years > 1 ? 's' : ''} — ${formatMoney(p.transfer)}</option>`;
            }
        });
    }
    if (!yearOptions) {
        yearOptions = `<option value="1" data-price="${price}">1 Year — ${formatMoney(price)}</option>`;
    }

    const content = `
        <div style="text-align: left; padding: 10px 0;">
            <div style="background: #f8fafc; border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                <strong style="font-size: 1.1rem;">${domain}</strong>
                <span class="pill pill-info" style="margin-left: 8px;">Transfer</span>
            </div>
            <div class="form-group">
                <label class="form-label">EPP / Auth Code <span style="color:#dc2626;">*</span></label>
                <input type="text" id="configEpp" class="form-input" placeholder="Enter the auth code from current registrar" required>
                <div class="form-hint">You can get this from your current domain registrar</div>
            </div>
            <div class="form-group">
                <label class="form-label">Registration Period</label>
                <select id="configYears" class="form-select" onchange="updateTransferModalTotal(${price})">
                    ${yearOptions}
                </select>
                <div class="form-hint">Transfer adds time to your current expiry date</div>
            </div>
            <div class="form-group">
                <label class="form-label">Nameservers (optional)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <input type="text" id="configNs1" class="form-input" value="{{ \App\Models\Setting::get('domain_default_ns1', 'ns1.jamvini.co.tz') }}" placeholder="NS1">
                    <input type="text" id="configNs2" class="form-input" value="{{ \App\Models\Setting::get('domain_default_ns2', 'ns2.jamvini.co.tz') }}" placeholder="NS2">
                    <input type="text" id="configNs3" class="form-input" value="" placeholder="NS3 (optional)">
                    <input type="text" id="configNs4" class="form-input" value="" placeholder="NS4 (optional)">
                </div>
            </div>
            <div style="margin-top: 12px;">
                <label class="form-label" style="margin-bottom: 8px;">Addons</label>
                <div id="addonList">${buildAddonHtml(tldConfig)}</div>
            </div>
            <div style="background: #f1f5f9; border-radius: 10px; padding: 14px; margin-top: 16px; display: flex; justify-content: space-between;">
                <span style="font-weight: 600;">Total:</span>
                <strong style="font-size: 1.2rem; color: var(--primary);" id="configTotal">${formatMoney(price)}</strong>
            </div>
        </div>
    `;

    Swal.fire({
        title: '🔄 Transfer Domain',
        html: content,
        width: '560px',
        showCancelButton: true,
        confirmButtonText: '🛒 Add to Cart',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#6C5CE7',
        preConfirm: () => {
            const epp = document.getElementById('configEpp')?.value?.trim();
            if (!epp) {
                Swal.showValidationMessage('EPP/Auth Code is required for transfer');
                return false;
            }
            const years = parseInt(document.getElementById('configYears')?.value || 1);
            const selectedPrice = parseFloat(document.getElementById('configYears')?.selectedOptions[0]?.dataset?.price || price);
            const addons = [];
            document.querySelectorAll('.config-addon:checked').forEach(cb => {
                addons.push({ name: cb.dataset.addon, price: parseFloat(cb.dataset.price) });
            });
            return {
                domain, years, basePrice: selectedPrice, epp,
                nameservers: [
                    document.getElementById('configNs1')?.value || '',
                    document.getElementById('configNs2')?.value || '',
                    document.getElementById('configNs3')?.value || '',
                    document.getElementById('configNs4')?.value || '',
                ].filter(ns => ns !== ''),
                addons
            };
        }
    }).then(async (r) => {
        if (r.isConfirmed && r.value) await addTransferToCart(r.value);
    });
}

function updateTransferModalTotal() {
    const years = parseInt(document.getElementById('configYears')?.value || 1);
    const priceSelect = document.getElementById('configYears');
    const basePrice = parseFloat(priceSelect?.selectedOptions[0]?.dataset?.price || 0);
    let total = basePrice * years;
    document.querySelectorAll('.config-addon:checked').forEach(cb => {
        total += parseFloat(cb.dataset.price) * years;
    });
    const totalEl = document.getElementById('configTotal');
    if (totalEl) totalEl.textContent = formatMoney(total);
}

async function addTransferToCart(config) {
    const years = config.years;
    const basePrice = config.basePrice;
    
    // Add domain transfer
    await fetch('{{ route('order.cart.add') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: 'domain_transfer',
            name: 'Transfer: ' + config.domain + ' (' + years + ' year' + (years > 1 ? 's' : '') + ')',
            price: basePrice * years,
            details: {
                billing_cycle: 'annually',
                years: years,
                epp_code: config.epp,
                nameservers: config.nameservers,
                domain: config.domain
            }
        })
    }).then(r => r.json());

    // Add addons
    for (const addon of config.addons) {
        await fetch('{{ route('order.cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: 'domain_addon',
                name: addon.name + ' — ' + config.domain,
                price: addon.price * years,
                details: { billing_cycle: 'annually', domain: config.domain, years: years }
            })
        }).then(r => r.json());
    }

    Swal.fire({
        icon: 'success',
        title: 'Transfer Added!',
        text: config.domain + ' transfer has been added to cart.',
        showCancelButton: true,
        confirmButtonText: '🛒 View Cart',
        cancelButtonText: 'Continue',
        confirmButtonColor: '#6C5CE7',
    }).then((r) => {
        if (r.isConfirmed) window.location.href = '{{ route('order.cart') }}';
    });
}



function buildAddonHtml(tldConfig) {
    const addons = tldConfig?.addons || [];
    if (!addons.length) return '<p style="color:var(--gray-500);font-size:.85rem;">No addons for this TLD</p>';
    
    let html = '';
    addons.forEach(addon => {
        html += `<label class="checkbox-group" style="display:block;margin-bottom:10px;">
            <input type="checkbox" class="config-addon" data-addon="${addon.name}" data-price="${addon.price}" onchange="updateConfigModalTotal()">
            <span>${addon.name} <small style="color:var(--gray-500);">+${formatMoney(addon.price)}/yr</small></span>
        </label>`;
    });
    return html;
}

function updateConfigModalTotal() {
    const years = parseInt(document.getElementById('configYears')?.value || 1);
    const priceSelect = document.getElementById('configYears');
    const basePrice = parseFloat(priceSelect?.selectedOptions[0]?.dataset?.price || 0);
    let total = basePrice * years;
    document.querySelectorAll('.config-addon:checked').forEach(cb => {
        total += parseFloat(cb.dataset.price) * years;
    });
    const totalEl = document.getElementById('configTotal');
    if (totalEl) totalEl.textContent = formatMoney(total);
}

async function addDomainToCart(config) {
    const years = config.years;
    const basePrice = config.basePrice;
    let totalPrice = basePrice * years;
    
    // Add domain with config
    await fetch('{{ route('order.cart.add') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: 'domain',
            name: config.domain + ' (' + years + ' year' + (years > 1 ? 's' : '') + ')',
            price: basePrice * years,
            details: {
                billing_cycle: 'annually',
                years: years,
                nameservers: config.nameservers,
                domain: config.domain
            }
        })
    }).then(r => r.json());

    // Add addons — each as separate request
    for (const addon of config.addons) {
        totalPrice += addon.price * years;
        await fetch('{{ route('order.cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: 'domain_addon',
                name: addon.name + ' — ' + config.domain,
                price: addon.price * years,
                details: {
                    billing_cycle: 'annually',
                    domain: config.domain,
                    years: years
                }
            })
        }).then(r => r.json());
    }

    Swal.fire({
        icon: 'success',
        title: 'Added to Cart!',
        text: config.domain + ' with ' + (config.addons.length > 0 ? config.addons.length + ' addon(s)' : 'no addons'),
        showCancelButton: true,
        confirmButtonText: '🛒 View Cart',
        cancelButtonText: 'Continue Shopping',
        confirmButtonColor: '#6C5CE7',
    }).then((r) => {
        if (r.isConfirmed) window.location.href = '{{ route('order.cart') }}';
    });
}

window.openDomainConfig = openDomainConfig;
window.openTransferConfig = openTransferConfig;
window.buildAddonHtml = buildAddonHtml;
window.updateConfigModalTotal = updateConfigModalTotal;
window.updateTransferModalTotal = updateTransferModalTotal;
window.addDomainToCart = addDomainToCart;
window.addTransferToCart = addTransferToCart;
})();
</script>
@endpush
