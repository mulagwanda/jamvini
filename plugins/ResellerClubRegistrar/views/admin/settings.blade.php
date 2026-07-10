@extends('themes.default::layouts.admin')

@section('title', 'ResellerClub Settings')
@section('breadcrumbs')<span class="current">ResellerClub Settings</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">ResellerClub Registrar</h1>
    <p class="page-subtitle">Configure your ResellerClub API credentials for domain registration</p>
</div>

{{-- Connection Status --}}
@if($connectionStatus === 'connected')
<div class="alert alert-success"><span class="alert-icon">✅</span> Connected to ResellerClub API successfully!</div>
@elseif($connectionStatus === 'failed')
<div class="alert alert-danger"><span class="alert-icon">❌</span> Connection failed. Check your credentials.</div>
@elseif(str_starts_with($connectionStatus, 'error:'))
<div class="alert alert-danger"><span class="alert-icon">❌</span> {{ substr($connectionStatus, 6) }}</div>
@else
<div class="alert alert-info"><span class="alert-icon">ℹ️</span> Enter your ResellerClub credentials to get started.</div>
@endif

<form action="{{ route('admin.resellerclub.settings.save') }}" method="POST">
    @csrf
    
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>🔑 API Credentials</h3></div>
        <div class="form-group">
            <label class="form-label">Reseller ID *</label>
            <input type="text" name="resellerclub_reseller_id" class="form-input" value="{{ old('resellerclub_reseller_id', $settings['reseller_id']) }}" placeholder="123456" required>
            <div class="form-hint">Found in your ResellerClub control panel → Settings → API</div>
        </div>
        <div class="form-group">
            <label class="form-label">API Key *</label>
            <input type="text" name="resellerclub_api_key" class="form-input" value="{{ old('resellerclub_api_key', $settings['api_key']) }}" placeholder="Enter your API key" required>
            <div class="form-hint">Generate from ResellerClub → Settings → API → Generate Key</div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label">Customer ID</label>
                <input type="text" name="resellerclub_customer_id" class="form-input" value="{{ old('resellerclub_customer_id', $settings['customer_id']) }}" placeholder="Your ResellerClub customer ID">
            </div>
            <div class="form-group">
                <label class="form-label">Default Contact ID</label>
                <input type="text" name="resellerclub_contact_id" class="form-input" value="{{ old('resellerclub_contact_id', $settings['contact_id']) }}" placeholder="Default contact for registrations">
            </div>
        </div>
    </div>

    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>⚙️ Settings</h3></div>
        <div style="display: flex; gap: 24px;">
            <label class="toggle-switch">
                <input type="checkbox" name="resellerclub_test_mode" value="1" {{ $settings['test_mode'] === '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span><span>Test/Sandbox Mode</span>
            </label>
            <label class="toggle-switch">
                <input type="checkbox" name="resellerclub_auto_register" value="1" {{ $settings['auto_register'] === '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span><span>Auto-register on order completion</span>
            </label>
        </div>
        <div class="form-hint" style="margin-top: 8px;">Test mode uses sandbox environment. No real domains are registered or charged.</div>
    </div>

    {{-- Supported TLDs --}}
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>🌐 Supported TLDs</h3></div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @foreach(['.com', '.net', '.org', '.io', '.co', '.africa', '.biz', '.info', '.xyz', '.online', '.store', '.site', '.tech', '.dev', '.app', '.me', '.club', '.design', '.agency', '.marketing', '.digital'] as $tld)
                <span class="pill pill-info">{{ $tld }}</span>
            @endforeach
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <button type="button" class="btn btn-outline-primary" onclick="testConnection()" id="testBtn">🔌 Test Connection</button>
        <button type="submit" class="btn btn-primary btn-lg">💾 Save Settings</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
async function testConnection() {
    const btn = document.getElementById('testBtn');
    btn.textContent = '⏳ Testing...';
    btn.disabled = true;
    
    try {
        const res = await fetch('{{ route('admin.resellerclub.test') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Connected!', text: 'ResellerClub API is working.' });
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Could not connect.' });
        }
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.' });
    }
    
    btn.textContent = '🔌 Test Connection';
    btn.disabled = false;
}
</script>
@endpush