@extends('themes.default::layouts.frontend')

@section('title', 'Account Settings')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / Account</div>
        <h1>Account Settings</h1>
        <p>Manage your profile, security, and preferences.</p>
    </div>
</section>

<main class="container" style="padding: 2rem 0;">
    <div class="client-wrap" style="display: grid; grid-template-columns: 260px 1fr; gap: 2rem;">
        <aside class="client-side" style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.25rem;height:fit-content;position:sticky;top:90px;">
            <div class="who" style="display:flex;align-items:center;gap:.75rem;padding-bottom:1rem;border-bottom:1px solid var(--gray-200);margin-bottom:1rem;">
                <div class="avatar" style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;">
                    {{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}
                </div>
                <div><div style="font-weight:600;">{{ $client->full_name }}</div><small>{{ $client->email }}</small></div>
            </div>
            <nav class="client-nav" style="display:flex;flex-direction:column;gap:.25rem;">
                <a href="/client/dashboard">📊 Dashboard</a>
                <a href="/client/services">🖥️ My Services</a>
                <a href="/client/domains">🌐 Domains</a>
                <a href="/client/orders">🛒 Orders</a>
                <a href="/client/invoices">🧾 Invoices</a>
                <a href="/client/account" class="active" style="background:var(--primary);color:#fff;">⚙️ Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">🚪 Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </nav>
        </aside>

        <div>
            @if(session('success'))
                <div style="background:#dcfce7;color:#166534;padding:1rem;border-radius:12px;margin-bottom:1.5rem;">✅ {{ session('success') }}</div>
            @endif

            {{-- Profile --}}
            <div style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.5rem;margin-bottom:1.5rem;">
                <h3 style="margin-bottom:1.5rem;">👤 Profile</h3>
                <form method="POST" action="{{ route('client.account.update') }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-input" value="{{ old('first_name', $client->first_name) }}" required></div>
                        <div class="form-group"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-input" value="{{ old('last_name', $client->last_name) }}" required></div>
                    </div>
                    <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="{{ old('email', $client->email) }}" required></div>
                    <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone', $client->phone) }}"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label class="form-label">Company</label><input type="text" name="company_name" class="form-input" value="{{ old('company_name', $client->company_name) }}"></div>
                        <div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-input" value="{{ old('city', $client->city) }}"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-textarea" rows="2">{{ old('address', $client->address) }}</textarea></div>
                    <div class="form-group"><label class="form-label">Country</label><input type="text" name="country" class="form-input" value="{{ old('country', $client->country) }}"></div>
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                </form>
            </div>

            {{-- Change Password --}}
            <div style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.5rem;">
                <h3 style="margin-bottom:1.5rem;">🔐 Change Password</h3>
                <form method="POST" action="{{ route('client.account.update') }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label class="form-label">New Password</label><input type="password" name="password" class="form-input" placeholder="Min 8 characters"></div>
                        <div class="form-group"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-input" placeholder="Repeat password"></div>
                    </div>
                    <div class="form-hint" style="margin-bottom:1rem;">Leave blank to keep current password.</div>
                    <button type="submit" class="btn btn-outline">🔒 Update Password</button>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection