<div id="moduleConfig" class="module-config-panel">
    @php
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
    <div class="form-group">
        <label class="form-label">Provisioning Server</label>
        <select name="server_id" id="hostingServerSelect" class="form-select">
            <option value="">— Manual (no auto-provisioning) —</option>
            @foreach($servers as $srv)
                <option value="{{ $srv->id }}" {{ $selectedServerId == $srv->id ? 'selected' : '' }}>
                    {{ $srv->name }} ({{ ucfirst($srv->type) }}) — {{ $srv->current_accounts }}/{{ $srv->max_accounts ?: '∞' }}
                </option>
            @endforeach
        </select>
        <div class="form-hint">Select a server for automatic account provisioning. Leave empty for manual setup.</div>
    </div>

    <div class="form-group">
        <label class="form-label">WHM Package</label>
        <select name="package_name" id="hostingPackageSelect" class="form-select" data-selected="{{ $selectedPackageName ?? '' }}">
            <option value="">— Select server first —</option>
        </select>
        <div class="form-hint">Sync packages from the Servers page, then choose the WHM plan this service should create.</div>
    </div>
</div>

<script>
(() => {
    const packagesByServer = @json($serverPackages);
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
})();
</script>
