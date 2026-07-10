@extends('themes.default::layouts.admin')

@section('title', 'Add Domain — JamVini Hosting')

@section('breadcrumbs')
    <a href="{{ route('admin.domains.index') }}">Domains</a>
    <span class="separator">/</span>
    <span class="current">Add New</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Add Domain</h1>
    <p class="page-subtitle">Register a domain for a client</p>
</div>

<form action="{{ route('admin.domains.store') }}" method="POST">
    @csrf
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">🌐 Domain Details</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="client_id">Client <span class="required">*</span></label>
                <select id="client_id" name="client_id" class="form-select @error('client_id') error @enderror" required>
                    <option value="">Select a client...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->full_name }} ({{ $client->email }})
                        </option>
                    @endforeach
                </select>
                @error('client_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="domain_name">Domain Name <span class="required">*</span></label>
                    <input type="text" id="domain_name" name="domain_name" 
                           class="form-input @error('domain_name') error @enderror"
                           value="{{ old('domain_name') }}" placeholder="example.co.tz" required>
                    @error('domain_name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="tld">TLD</label>
                    <select id="tld" name="tld" class="form-select">
                        <option value="">Auto-detect</option>
                        <option value=".co.tz" {{ old('tld') === '.co.tz' ? 'selected' : '' }}>.co.tz</option>
                        <option value=".or.tz" {{ old('tld') === '.or.tz' ? 'selected' : '' }}>.or.tz</option>
                        <option value=".go.tz" {{ old('tld') === '.go.tz' ? 'selected' : '' }}>.go.tz</option>
                        <option value=".com" {{ old('tld') === '.com' ? 'selected' : '' }}>.com</option>
                        <option value=".org" {{ old('tld') === '.org' ? 'selected' : '' }}>.org</option>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="registrar">Registrar</label>
                    <select id="registrar" name="registrar" class="form-select">
                        <option value="">Manual</option>
                        <option value="tzNIC" {{ old('registrar') === 'tzNIC' ? 'selected' : '' }}>tzNIC</option>
                        <option value="Zesha" {{ old('registrar') === 'Zesha' ? 'selected' : '' }}>Zesha</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ old('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
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
                           class="form-input" value="{{ old('registration_date', date('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" 
                           class="form-input" value="{{ old('expiry_date') }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="registration_period">Period (Years)</label>
                    <input type="number" id="registration_period" name="registration_period" 
                           class="form-input" value="{{ old('registration_period', 1) }}" min="1" max="10">
                </div>
                <div class="form-group">
                    <label class="form-label" for="registration_fee">Registration Fee ({{ \App\Models\Setting::get('currency', 'TZS') }})</label>
                    <input type="number" id="registration_fee" name="registration_fee" 
                           class="form-input" value="{{ old('registration_fee', 0) }}" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label" for="renewal_fee">Renewal Fee ({{ \App\Models\Setting::get('currency', 'TZS') }})</label>
                    <input type="number" id="renewal_fee" name="renewal_fee" 
                           class="form-input" value="{{ old('renewal_fee', 0) }}" step="0.01">
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">🔧 Nameservers</h3></div>
        <div class="card-body">
            <div id="nameservers-container">
                @foreach(old('nameservers', ['', '', '', '']) as $index => $ns)
                <div class="form-group" style="margin-bottom: 8px;">
                    <input type="text" name="nameservers[]" 
                           class="form-input" 
                           value="{{ $ns }}" 
                           placeholder="ns{{ $index + 1 }}.example.com">
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">⚙️ Settings</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="auto_renew" value="1" {{ old('auto_renew') ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                    <span>Auto-renew this domain</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="2" 
                          placeholder="Internal notes...">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.domains.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">✅ Add Domain</button>
    </div>
</form>
@endsection
