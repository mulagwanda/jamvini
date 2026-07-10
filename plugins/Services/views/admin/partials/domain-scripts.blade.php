<script>
let tldIndex = 0;

function toggleDomainConfig() {
    const type = document.querySelector('select[name="type"]')?.value || '';
    const groupId = document.querySelector('select[name="group_id"]')?.value;
    const groupOption = document.querySelector(`#group_id option[value="${groupId}"]`);
    const module = groupOption?.dataset?.module;
    
    const domainConfig = document.getElementById('domainConfig');
    const hostingConfig = document.getElementById('hostingConfig');
    
    if (domainConfig) domainConfig.style.display = (type === 'domain' || module === 'domains') ? '' : 'none';
    if (hostingConfig) hostingConfig.style.display = (type === 'hosting' || module === 'hosting') ? '' : 'none';
}

function addTld(data = null) {
    const container = document.getElementById('tldsContainer');
    const idx = tldIndex++;
    const tld = data?.tld || '';
    const dns = data?.dns_management !== false;
    const email = data?.email_forwarding !== false;
    const idp = data?.id_protection !== false;
    const epp = data?.epp_code !== false;
    const auto = data?.auto_register || false;
    const registrarSlug = data?.registrar_slug || '';
    
    // Build registrar options from active registrars
    const registrars = @json(\App\Core\Registries\RegistrarRegistry::active());
    const currency = @json(\App\Models\Setting::get('currency', 'TZS'));
    let registrarOptions = '<option value="">— Use Default —</option>';
    Object.entries(registrars).forEach(([slug, config]) => {
        const selected = slug === registrarSlug ? ' selected' : '';
        registrarOptions += `<option value="${slug}"${selected}>${config.icon || '🔌'} ${config.name}</option>`;
    });
    
    const html = `
        <div class="tld-row" style="border: 1px solid var(--jv-gray-200); border-radius: 12px; padding: 20px; margin-bottom: 16px; background: var(--jv-gray-50);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <strong style="font-size: 1.1rem;">TLD Configuration #${idx + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.tld-row').remove()">✕ Remove</button>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">TLD *</label>
                    <input type="text" name="tlds[${idx}][tld]" class="form-input" value="${tld}" placeholder=".co.tz" data-domain-required="true">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Registrar</label>
                    <select name="tlds[${idx}][registrar_slug]" class="form-select">
                        ${registrarOptions}
                    </select>
                    <div class="form-hint">Leave as "Use Default" to use the global default registrar</div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Registration (${currency}/yr)</label>
                    <input type="number" name="tlds[${idx}][register_price]" class="form-input" value="${data?.register_price || ''}" placeholder="35000" step="0.01">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Renewal (${currency}/yr)</label>
                    <input type="number" name="tlds[${idx}][renewal_price]" class="form-input" value="${data?.renewal_price || ''}" placeholder="35000" step="0.01">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Transfer (${currency})</label>
                    <input type="number" name="tlds[${idx}][transfer_price]" class="form-input" value="${data?.transfer_price || ''}" placeholder="35000" step="0.01">
                </div>
            </div>
            
            <div class="form-group" style="margin: 0; margin-bottom: 16px;">
                <label class="form-label">Available Years (comma-separated)</label>
                <input type="text" name="tlds[${idx}][years]" class="form-input" value="${data?.years || '1,2,3,5,10'}" placeholder="1,2,3,5,10">
            </div>
            
            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 16px;">
                <label class="checkbox-group"><input type="checkbox" name="tlds[${idx}][dns_management]" value="1" ${dns ? 'checked' : ''}> DNS Management</label>
                <label class="checkbox-group"><input type="checkbox" name="tlds[${idx}][email_forwarding]" value="1" ${email ? 'checked' : ''}> Email Forwarding</label>
                <label class="checkbox-group"><input type="checkbox" name="tlds[${idx}][id_protection]" value="1" ${idp ? 'checked' : ''}> ID Protection</label>
                <label class="checkbox-group"><input type="checkbox" name="tlds[${idx}][epp_code]" value="1" ${epp ? 'checked' : ''}> EPP Code</label>
            </div>
            
            <div class="form-group" style="margin: 0;">
                <label class="toggle-switch">
                    <input type="checkbox" name="tlds[${idx}][auto_register]" value="1" ${auto ? 'checked' : ''}>
                    <span class="toggle-slider"></span><span>Auto Register on Payment</span>
                </label>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// Monitor type/group changes
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const groupSelect = document.querySelector('select[name="group_id"]');
    if (typeSelect) typeSelect.addEventListener('change', toggleDomainConfig);
    if (groupSelect) groupSelect.addEventListener('change', toggleDomainConfig);
    toggleDomainConfig();
});
</script>
