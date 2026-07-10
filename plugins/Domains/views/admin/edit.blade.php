@extends('themes.default::layouts.admin')

@section('title', 'Edit — ' . $domain->domain_name)

@section('breadcrumbs')
    <a href="{{ route('admin.domains.index') }}">Domains</a>
    <span class="separator">/</span>
    <a href="{{ route('admin.domains.show', $domain) }}">{{ $domain->domain_name }}</a>
    <span class="separator">/</span>
    <span class="current">Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <h1 class="page-title">Edit Domain</h1>
        <form action="{{ route('admin.domains.destroy', $domain) }}" method="POST"
              data-confirm="Delete '{{ $domain->domain_name }}'?"
              data-title="Delete Domain" data-confirm-text="Yes, Delete" data-danger="true">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">🗑️ Delete</button>
        </form>
    </div>
</div>

<form action="{{ route('admin.domains.update', $domain) }}" method="POST">
    @csrf @method('PUT')
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">🌐 Domain Details</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="client_id">Client</label>
                <select id="client_id" name="client_id" class="form-select" required>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $domain->client_id) == $client->id ? 'selected' : '' }}>
                            {{ $client->full_name }} ({{ $client->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="domain_name">Domain Name <span class="required">*</span></label>
                    <input type="text" id="domain_name" name="domain_name" 
                           class="form-input" value="{{ old('domain_name', $domain->domain_name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tld">TLD</label>
                    <select id="tld" name="tld" class="form-select">
                        @foreach(['.co.tz', '.or.tz', '.go.tz', '.com', '.org'] as $tld)
                            <option value="{{ $tld }}" {{ old('tld', $domain->tld) === $tld ? 'selected' : '' }}>{{ $tld }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="registrar">Registrar</label>
                    <select id="registrar" name="registrar" class="form-select">
                        @foreach(['Manual', 'tzNIC', 'Zesha'] as $reg)
                            <option value="{{ $reg === 'Manual' ? '' : $reg }}" {{ old('registrar', $domain->registrar) === $reg || ($reg === 'Manual' && !$domain->registrar) ? 'selected' : '' }}>
                                {{ $reg }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        @foreach(['active', 'expired', 'transferred', 'suspended'] as $status)
                            <option value="{{ $status }}" {{ old('status', $domain->status) === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">📅 Dates & Fees</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="registration_date">Registration Date</label>
                    <input type="date" id="registration_date" name="registration_date" 
                           class="form-input" value="{{ old('registration_date', $domain->registration_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" 
                           class="form-input" value="{{ old('expiry_date', $domain->expiry_date?->format('Y-m-d')) }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="registration_period">Period (Years)</label>
                    <input type="number" id="registration_period" name="registration_period" 
                           class="form-input" value="{{ old('registration_period', $domain->registration_period) }}" min="1" max="10">
                </div>
                <div class="form-group">
                    <label class="form-label" for="registration_fee">Registration Fee ({{ \App\Models\Setting::get('currency', 'TZS') }})</label>
                    <input type="number" id="registration_fee" name="registration_fee" 
                           class="form-input" value="{{ old('registration_fee', $domain->registration_fee) }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label" for="renewal_fee">Renewal Fee ({{ \App\Models\Setting::get('currency', 'TZS') }})</label>
                    <input type="number" id="renewal_fee" name="renewal_fee" 
                           class="form-input" value="{{ old('renewal_fee', $domain->renewal_fee) }}" step="0.01">
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">⚙️ Settings</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="auto_renew" value="1" {{ old('auto_renew', $domain->auto_renew) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                    <span>Auto-renew this domain</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2">{{ old('notes', $domain->notes) }}</textarea>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.domains.show', $domain) }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">💾 Update Domain</button>
    </div>
</form>
@endsection
