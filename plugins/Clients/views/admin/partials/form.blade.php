@php
    $client = $client ?? null;
    $groups = $groups ?? collect();
    $currencies = ['TZS' => 'TZS', 'USD' => 'USD', 'KES' => 'KES', 'UGX' => 'UGX', 'RWF' => 'RWF'];
    $languages = ['en' => 'English', 'sw' => 'Swahili'];
    $timezones = ['Africa/Dar_es_Salaam', 'Africa/Nairobi', 'Africa/Kampala', 'Africa/Kigali', 'UTC'];
    $customFields = $customFields ?? collect();
    $customFieldValues = $customFieldValues ?? collect();
@endphp

<div style="display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.5rem; align-items: start;">
    <div style="display: grid; gap: 1.5rem;">
        <div class="dash-card">
            <div class="dash-card-head"><h3>Account Identity</h3></div>
            <div style="display: grid; grid-template-columns: 180px 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Client Number</label>
                    <input type="text" name="client_number" class="form-input" value="{{ old('client_number', $client->client_number ?? '') }}" placeholder="Auto">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select name="type" class="form-select" required>
                        @foreach(['individual' => 'Individual', 'company' => 'Company', 'government' => 'Government', 'nonprofit' => 'Non-profit'] as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $client->type ?? 'individual') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'closed' => 'Closed'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $client->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($groups->count())
                <div class="form-group">
                    <label class="form-label">Client Group</label>
                    <select name="client_group_id" class="form-select">
                        <option value="">No group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ (string) old('client_group_id', $client->client_group_id ?? '') === (string) $group->id ? 'selected' : '' }}>
                                {{ $group->name }}@if((float) $group->discount_percent > 0) - {{ $group->discount_percent }}% discount @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-input" value="{{ old('first_name', $client->first_name ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-input" value="{{ old('last_name', $client->last_name ?? '') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Company / Organization</label>
                <input type="text" name="company_name" class="form-input" value="{{ old('company_name', $client->company_name ?? '') }}">
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Contacts</h3></div>
            <div class="form-group">
                <label class="form-label">Primary Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $client->email ?? '') }}" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Billing Email</label><input type="email" name="billing_email" class="form-input" value="{{ old('billing_email', $client->billing_email ?? '') }}"></div>
                <div class="form-group"><label class="form-label">Technical Email</label><input type="email" name="technical_email" class="form-input" value="{{ old('technical_email', $client->technical_email ?? '') }}"></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone', $client->phone ?? '') }}" placeholder="+255..."></div>
                <div class="form-group"><label class="form-label">Mobile</label><input type="text" name="mobile" class="form-input" value="{{ old('mobile', $client->mobile ?? '') }}" placeholder="+255..."></div>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head">
                <h3>Additional Contacts</h3>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addClientContact()">Add Contact</button>
            </div>
            <div id="clientContacts" style="display:grid;gap:10px;">
                @php
                    $oldContacts = old('contacts');
                    $contacts = $oldContacts !== null ? collect($oldContacts) : ($client?->contacts ?? collect());
                @endphp
                @foreach($contacts as $index => $contact)
                    <div class="contact-row" style="display:grid;grid-template-columns:1fr 150px 1fr 150px auto;gap:8px;align-items:end;padding:10px;border:1px solid var(--jv-gray-200);border-radius:8px;">
                        <div class="form-group" style="margin:0;"><label class="form-label">Name</label><input type="text" name="contacts[{{ $index }}][name]" class="form-input" value="{{ data_get($contact, 'name') }}"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Role</label><input type="text" name="contacts[{{ $index }}][role]" class="form-input" value="{{ data_get($contact, 'role') }}" placeholder="Billing"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Email</label><input type="email" name="contacts[{{ $index }}][email]" class="form-input" value="{{ data_get($contact, 'email') }}"></div>
                        <div class="form-group" style="margin:0;"><label class="form-label">Phone</label><input type="text" name="contacts[{{ $index }}][phone]" class="form-input" value="{{ data_get($contact, 'phone') }}"></div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.contact-row').remove()">Remove</button>
                        <label class="checkbox-group"><input type="checkbox" name="contacts[{{ $index }}][receives_billing]" value="1" {{ data_get($contact, 'receives_billing') ? 'checked' : '' }}> Billing</label>
                        <label class="checkbox-group"><input type="checkbox" name="contacts[{{ $index }}][receives_support]" value="1" {{ data_get($contact, 'receives_support') ? 'checked' : '' }}> Support</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Address</h3></div>
            <div class="form-group"><label class="form-label">Street Address</label><textarea name="address" class="form-textarea" rows="2">{{ old('address', $client->address ?? '') }}</textarea></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 140px 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-input" value="{{ old('city', $client->city ?? '') }}"></div>
                <div class="form-group"><label class="form-label">State / Region</label><input type="text" name="state" class="form-input" value="{{ old('state', $client->state ?? '') }}"></div>
                <div class="form-group"><label class="form-label">Postal Code</label><input type="text" name="postal_code" class="form-input" value="{{ old('postal_code', $client->postal_code ?? '') }}"></div>
                <div class="form-group"><label class="form-label">Country</label><input type="text" name="country" class="form-input" value="{{ old('country', $client->country ?? 'Tanzania') }}"></div>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Billing & Tax</h3></div>
            <div style="display: grid; grid-template-columns: 1fr 150px 150px 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">TIN / Tax ID</label><input type="text" name="tin_number" class="form-input" value="{{ old('tin_number', $client->tin_number ?? '') }}"></div>
                <div class="form-group">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select">
                        <option value="">System default</option>
                        @foreach($currencies as $value => $label)
                            <option value="{{ $value }}" {{ old('currency', $client->currency ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Credit Balance</label><input type="number" name="credit_balance" class="form-input" value="{{ old('credit_balance', $client->credit_balance ?? 0) }}" min="0" step="0.01"></div>
                <div class="form-group">
                    <input type="hidden" name="vat_exempt" value="0">
                    <label class="toggle-switch" style="margin-top: 28px;"><input type="checkbox" name="vat_exempt" value="1" {{ old('vat_exempt', $client->vat_exempt ?? false) ? 'checked' : '' }}><span class="toggle-slider"></span><span>VAT Exempt</span></label>
                </div>
            </div>
        </div>

        @if($customFields->count())
            <div class="dash-card">
                <div class="dash-card-head"><h3>{{ jv_icon('brackets', '', 18) }} Custom Fields</h3></div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
                    @include('plugins.CustomFields::partials.fields', ['fields' => $customFields, 'values' => $customFieldValues])
                </div>
            </div>
        @endif
    </div>

    <aside>
        <div class="dash-card">
            <div class="dash-card-head"><h3>Portal</h3></div>
            <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-input" placeholder="{{ $client ? 'Leave blank to keep current' : 'Leave blank to set later' }}"></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Language</label>
                    <select name="language" class="form-select">
                        @foreach($languages as $value => $label)
                            <option value="{{ $value }}" {{ old('language', $client->language ?? 'en') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Timezone</label>
                    <select name="timezone" class="form-select">
                        <option value="">System default</option>
                        @foreach($timezones as $timezone)
                            <option value="{{ $timezone }}" {{ old('timezone', $client->timezone ?? '') === $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <input type="hidden" name="email_marketing_opt_in" value="0">
            <label class="toggle-switch"><input type="checkbox" name="email_marketing_opt_in" value="1" {{ old('email_marketing_opt_in', $client->email_marketing_opt_in ?? false) ? 'checked' : '' }}><span class="toggle-slider"></span><span>Email marketing opt-in</span></label>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Source & Import</h3></div>
            <div class="form-group"><label class="form-label">Source</label><input type="text" name="source" class="form-input" value="{{ old('source', $client->source ?? '') }}" placeholder="Referral, WHMCS, Website"></div>
            <div class="form-group"><label class="form-label">External / WHMCS ID</label><input type="text" name="external_id" class="form-input" value="{{ old('external_id', $client->external_id ?? '') }}" placeholder="WHMCS client ID"></div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Internal Notes</h3></div>
            <textarea name="notes" class="form-textarea" rows="8" placeholder="Private account notes, billing preferences, migration notes...">{{ old('notes', $client->notes ?? '') }}</textarea>
        </div>
    </aside>
</div>

@push('scripts')
<script>
let clientContactIndex = {{ max(0, ($contacts ?? collect())->count()) }};
function addClientContact() {
    const wrap = document.getElementById('clientContacts');
    const index = clientContactIndex++;
    const row = document.createElement('div');
    row.className = 'contact-row';
    row.style.cssText = 'display:grid;grid-template-columns:1fr 150px 1fr 150px auto;gap:8px;align-items:end;padding:10px;border:1px solid var(--jv-gray-200);border-radius:8px;';
    row.innerHTML = `
        <div class="form-group" style="margin:0;"><label class="form-label">Name</label><input type="text" name="contacts[${index}][name]" class="form-input"></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Role</label><input type="text" name="contacts[${index}][role]" class="form-input" placeholder="Billing"></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Email</label><input type="email" name="contacts[${index}][email]" class="form-input"></div>
        <div class="form-group" style="margin:0;"><label class="form-label">Phone</label><input type="text" name="contacts[${index}][phone]" class="form-input"></div>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.contact-row').remove()">Remove</button>
        <label class="checkbox-group"><input type="checkbox" name="contacts[${index}][receives_billing]" value="1"> Billing</label>
        <label class="checkbox-group"><input type="checkbox" name="contacts[${index}][receives_support]" value="1"> Support</label>
    `;
    wrap.appendChild(row);
}
</script>
@endpush
