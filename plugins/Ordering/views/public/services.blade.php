@extends('themes.default::layouts.frontend')

@section('title', 'Services')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / Services</div>
        <h1>Services</h1>
        <p>Choose hosting, SSL, email, and other services configured by the provider.</p>
    </div>
</section>

<section>
    <div class="container">
        @if($services->count())
            @foreach(($groups ?? $services->groupBy(fn($service) => $service->group?->name ?: 'Services')) as $groupName => $groupServices)
                <div class="section-head" style="margin-top:{{ $loop->first ? '0' : '3rem' }};text-align:left;">
                    <span class="eyebrow">{{ $groupServices->first()?->group?->module ?: 'service' }}</span>
                    <h2>{{ $groupName }}</h2>
                    @if($groupServices->first()?->group?->description)
                        <p>{{ $groupServices->first()->group->description }}</p>
                    @endif
                </div>

                <div class="pricing-grid">
                    @foreach($groupServices as $index => $service)
                        @php
                            $featured = $service->is_featured || ($index === 1 && $groupServices->count() >= 3);
                            $cycles = collect($service->getAvailableBillingCycles())->filter()->values();
                            if ($cycles->isEmpty()) { $cycles = collect([$service->billing_cycle ?: 'monthly']); }
                            $defaultCycle = $cycles->contains('monthly') ? 'monthly' : $cycles->first();
                            $cyclePrices = $cycles->mapWithKeys(fn ($cycle) => [$cycle => $service->getPrice($cycle)])->all();
                            $hasFeatures = is_array($service->features) && count($service->features) > 0;
                            $requiresDomain = (bool) ($service->group?->requires_domain || $service->group?->module === 'hosting');
                            $configPayload = [
                                'requires_domain' => $requiresDomain,
                                'cycles' => $cycles->all(),
                                'prices' => $cyclePrices,
                                'setup_fee' => (float) ($service->setup_fee ?? 0),
                                'module' => $service->group?->module ?? 'custom',
                                'options' => $service->options->map(fn ($option) => [
                                    'id' => $option->id,
                                    'name' => $option->name,
                                    'type' => $option->type,
                                    'choices' => $option->options ?? [],
                                    'price' => (float) ($option->prices[$defaultCycle] ?? $option->prices['monthly'] ?? 0),
                                ])->values(),
                                'custom_fields' => $service->customFields->map(fn ($field) => [
                                    'id' => $field->id,
                                    'label' => $field->label,
                                    'type' => $field->type,
                                    'options' => $field->optionList(),
                                    'placeholder' => $field->placeholder,
                                    'required' => (bool) $field->is_required,
                                ])->values(),
                                'addons' => $service->addons->map(fn ($addon) => [
                                    'id' => $addon->id,
                                    'name' => $addon->name,
                                    'description' => $addon->description,
                                    'price' => (float) $addon->price,
                                    'required' => (bool) $addon->is_required,
                                ])->values(),
                            ];
                        @endphp
                        <div class="price-card reveal {{ $featured ? 'featured' : '' }}">
                            @if($service->badge_label || $featured)<span class="badge">{{ $service->badge_label ?: 'Popular' }}</span>@endif
                            <h3>{{ $service->name }}</h3>
                            <p class="plan-desc">{{ $service->description ?? 'A service package ready for ordering.' }}</p>
                            <div class="price">
                                <strong>{{ jv_format_money($cyclePrices[$defaultCycle] ?? 0) }}</strong>
                                <span>/{{ str_replace('_', ' ', $defaultCycle) }}</span>
                            </div>
                            @if(($service->setup_fee ?? 0) > 0)
                                <p style="color:var(--gray-500);font-size:.84rem;margin-top:-.5rem;">Setup fee: {{ jv_format_money($service->setup_fee) }}</p>
                            @endif
                            @if($hasFeatures)
                                <ul class="plan-features">
                                    @foreach($service->features as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <button class="btn {{ $featured ? 'btn-light' : 'btn-outline' }} btn-block add-to-cart"
                                    data-name="{{ $service->name }}"
                                    data-type="{{ $service->group?->module ?? 'custom' }}"
                                    data-service-id="{{ $service->id }}"
                                    data-default-cycle="{{ $defaultCycle }}"
                                    data-config='@json($configPayload)'>
                                {{ $featured ? 'Choose ' . $service->name : 'Get Started' }}
                            </button>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @else
            <div style="text-align:center;padding:60px">
                <div style="font-size:64px">📦</div>
                <h2>No services available yet</h2>
                <p style="color: var(--gray-600);">Check back soon or <a href="/contact">contact us</a>.</p>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
(function() {
    const moneyCurrency = @json(\App\Models\Setting::get('currency', 'TZS'));
    const moneyDecimals = {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }};
    function formatMoney(amount) {
        return moneyCurrency + ' ' + Number(amount || 0).toLocaleString(undefined, {
            minimumFractionDigits: moneyDecimals,
            maximumFractionDigits: moneyDecimals
        });
    }
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    }
    function cycleLabel(cycle) {
        return {monthly:'Monthly', quarterly:'Quarterly', semi_annually:'Semi-Annual', annually:'Annual', 'one-time':'One Time', free:'Free'}[cycle] || cycle;
    }
    function savingsText(prices, cycle) {
        const months = {quarterly:3, semi_annually:6, annually:12}[cycle];
        const monthly = Number(prices.monthly || 0);
        const current = Number(prices[cycle] || 0);
        if (!months || !monthly || !current || current >= monthly * months) return '';
        return `You save ${Math.round(((monthly * months - current) / (monthly * months)) * 100)}% compared with monthly billing.`;
    }
    function buildConfigHtml(config) {
        let html = '<div style="text-align:left;display:grid;gap:12px;">';
        if ((config.cycles || []).length > 1) {
            html += `<label style="display:grid;gap:6px;font-weight:700;">Billing period<select name="billing_cycle" class="swal2-select" style="margin:0;width:100%;">${config.cycles.map(cycle => `<option value="${escapeHtml(cycle)}">${cycleLabel(cycle)} - ${formatMoney(config.prices?.[cycle] || 0)}</option>`).join('')}</select><small class="cycle-saving" style="color:#16a34a;"></small></label>`;
        } else {
            html += `<input type="hidden" name="billing_cycle" value="${escapeHtml((config.cycles || [])[0] || 'monthly')}">`;
        }
        if (Number(config.setup_fee || 0) > 0) {
            html += `<div style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:10px;padding:10px;">Setup fee: <strong>${formatMoney(config.setup_fee)}</strong>. Renewal uses the selected billing price.</div>`;
        }
        if (config.requires_domain) {
            html += `<label style="display:grid;gap:6px;font-weight:700;">Domain<input name="domain" class="swal2-input" style="margin:0;width:100%;" placeholder="example.com"></label>`;
        }
        (config.options || []).forEach(option => {
            const price = option.price ? ` (+${formatMoney(option.price)})` : '';
            if (option.type === 'dropdown' && option.choices?.length) {
                html += `<label style="display:grid;gap:6px;font-weight:700;">${escapeHtml(option.name)}${price}<select name="option_${option.id}" class="swal2-select" style="margin:0;width:100%;">${option.choices.map(choice => `<option value="${escapeHtml(choice)}">${escapeHtml(choice)}</option>`).join('')}</select></label>`;
            } else if (option.type === 'checkbox') {
                html += `<label style="display:flex;gap:8px;align-items:center;"><input type="checkbox" name="option_${option.id}" value="1"> <span>${escapeHtml(option.name)}${price}</span></label>`;
            } else {
                html += `<label style="display:grid;gap:6px;font-weight:700;">${escapeHtml(option.name)}${price}<input type="number" name="option_${option.id}" class="swal2-input" style="margin:0;width:100%;" value="1" min="0"></label>`;
            }
        });
        (config.custom_fields || []).forEach(field => {
            const required = field.required ? ' *' : '';
            if (field.type === 'select' && field.options?.length) {
                html += `<label style="display:grid;gap:6px;font-weight:700;">${escapeHtml(field.label)}${required}<select name="custom_${field.id}" class="swal2-select" style="margin:0;width:100%;">${field.options.map(choice => `<option value="${escapeHtml(choice)}">${escapeHtml(choice)}</option>`).join('')}</select></label>`;
            } else if (field.type === 'textarea') {
                html += `<label style="display:grid;gap:6px;font-weight:700;">${escapeHtml(field.label)}${required}<textarea name="custom_${field.id}" class="swal2-textarea" style="margin:0;width:100%;" placeholder="${escapeHtml(field.placeholder || '')}"></textarea></label>`;
            } else {
                html += `<label style="display:grid;gap:6px;font-weight:700;">${escapeHtml(field.label)}${required}<input name="custom_${field.id}" class="swal2-input" style="margin:0;width:100%;" placeholder="${escapeHtml(field.placeholder || '')}"></label>`;
            }
        });
        if ((config.addons || []).length) {
            html += '<div style="display:grid;gap:8px;"><strong>Addons</strong>';
            (config.addons || []).forEach(addon => {
                const checked = addon.required ? 'checked disabled' : '';
                html += `<label style="display:flex;gap:8px;align-items:flex-start;"><input type="checkbox" name="addons[]" value="${addon.id}" ${checked}> <span><strong>${escapeHtml(addon.name)}</strong> ${addon.price ? `(${formatMoney(addon.price)})` : ''}<br><small>${escapeHtml(addon.description || '')}</small></span></label>`;
            });
            html += '</div>';
        }
        return html + '</div>';
    }

    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', async function() {
            const config = JSON.parse(this.dataset.config || '{}');
            const popup = await Swal.fire({
                title: 'Configure ' + this.dataset.name,
                html: buildConfigHtml(config),
                confirmButtonText: 'Add to Cart',
                showCancelButton: true,
                confirmButtonColor: '#6C5CE7',
                focusConfirm: false,
                didOpen: () => {
                    const select = Swal.getPopup().querySelector('[name="billing_cycle"]');
                    const note = Swal.getPopup().querySelector('.cycle-saving');
                    const refresh = () => { if (note && select) note.textContent = savingsText(config.prices || {}, select.value); };
                    select?.addEventListener('change', refresh);
                    refresh();
                },
                preConfirm: () => {
                    const modal = Swal.getPopup();
                    const cycle = modal.querySelector('[name="billing_cycle"]')?.value || this.dataset.defaultCycle || 'monthly';
                    const domain = (modal.querySelector('[name="domain"]')?.value || '').trim().toLowerCase();
                    if (config.requires_domain && !domain) return 'Domain name is required.';
                    if (domain && !/^[a-z0-9][a-z0-9-]*(\.[a-z0-9][a-z0-9-]*)+$/i.test(domain)) return 'Enter a complete domain, for example example.com';
                    const customFields = {};
                    for (const field of config.custom_fields || []) {
                        const input = modal.querySelector(`[name="custom_${field.id}"]`);
                        const value = input?.value?.trim() || '';
                        if (field.required && !value) return `${field.label} is required.`;
                        customFields[field.id] = value;
                    }
                    const options = {};
                    for (const option of config.options || []) {
                        const input = modal.querySelector(`[name="option_${option.id}"]`);
                        if (input) options[option.id] = input.type === 'checkbox' ? input.checked : input.value;
                    }
                    const addons = Array.from(modal.querySelectorAll('[name="addons[]"]:checked')).map(input => input.value);
                    return { cycle, domain, customFields, options, addons };
                }
            });
            if (!popup.isConfirmed) return;
            this.disabled = true;
            const price = Number(config.prices?.[popup.value.cycle] || 0);
            const res = await fetch('{{ route('order.cart.add') }}', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
                body: JSON.stringify({
                    type: this.dataset.type || 'custom',
                    name: this.dataset.name,
                    price,
                    details: {
                        service_id: this.dataset.serviceId,
                        billing_cycle: popup.value.cycle,
                        domain: popup.value.domain,
                        configurable_options: popup.value.options,
                        custom_fields: popup.value.customFields,
                        addons: popup.value.addons
                    }
                })
            });
            this.disabled = false;
            if (!res.ok) {
                const error = await res.json().catch(() => ({}));
                Swal.fire('Could not add service', error.message || 'Please check the service configuration.', 'error');
                return;
            }
            const added = await Swal.fire({icon:'success',title:'Added to Cart',text:savingsText(config.prices || {}, popup.value.cycle) || 'Service added successfully.',showCancelButton:true,confirmButtonText:'View Cart',cancelButtonText:'Continue'});
            if (added.isConfirmed) window.location.href = '{{ route('order.cart') }}';
        });
    });
})();
</script>
@endpush
