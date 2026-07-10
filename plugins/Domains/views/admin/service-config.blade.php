{{-- Domain TLD Configuration --}}
<div id="domainConfig">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h4 style="margin: 0;">🌐 Domain TLD Configuration</h4>
        <button type="button" class="btn btn-sm btn-primary" onclick="addTld()">➕ Add TLD</button>
    </div>
    <div id="tldsContainer">
        @if($tlds && count($tlds) > 0)
            <p style="color: var(--jv-gray-500);">Loading existing TLDs...</p>
        @else
            <p style="color: var(--jv-gray-500);">Add TLDs (.co.tz, .com, etc.) with pricing and features.</p>
        @endif
    </div>
</div>

@include('plugins.Services::admin.partials.domain-scripts')

@if($tlds && count($tlds) > 0)
<script>
// Pre-fill existing TLDs
document.addEventListener('DOMContentLoaded', function() {
    var existingTlds = @json($tlds);
    existingTlds.forEach(function(tld) {
        let pricing = tld.pricing?.[0] || {};
        let grace = tld.period_pricing?.find(p => p.period_type == 1);
        let redemption = tld.period_pricing?.find(p => p.period_type == 2);
        let addons = tld.addons || [];
        addTld({
            tld: tld.tld,
            registrar_slug: tld.registrar_slug || '',
            register_price: pricing.register_price || '',
            renewal_price: pricing.renewal_price || '',
            transfer_price: pricing.transfer_price || '',
            years: tld.pricing?.map(p => p.years).join(',') || '1,2,3,5,10',
            dns_management: tld.dns_management,
            email_forwarding: tld.email_forwarding,
            id_protection: tld.id_protection,
            epp_code: tld.epp_code,
            auto_register: tld.auto_register,
            grace_days: grace?.days || '30',
            grace_price: grace?.price || '0',
            redemption_days: redemption?.days || '30',
            redemption_price: redemption?.price || '0',
            addon_dns: addons.find(a=>a.name==='DNS Management')?.price || '0',
            addon_email: addons.find(a=>a.name==='Email Forwarding')?.price || '0',
            addon_id: addons.find(a=>a.name==='ID Protection')?.price || '0',
        });
    });
});
</script>
@endif