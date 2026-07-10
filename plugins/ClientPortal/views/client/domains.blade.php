@extends('themes.default::layouts.frontend')

@section('title', 'My Domains')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / Domains</div>
        <h1>My Domains</h1>
        <p>Manage registered and transferred domains.</p>
    </div>
</section>

<main class="container" style="padding:2rem 0;">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;gap:12px;flex-wrap:wrap;">
            <h3 style="margin:0;">Domains</h3>
            <a href="{{ route('order.domains') }}" class="btn btn-primary btn-sm">+ Register Domain</a>
        </div>

        @if($domains->count())
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--gray-50);">
                            <th style="padding:12px;text-align:left;">Domain</th>
                            <th style="padding:12px;text-align:left;">Registrar</th>
                            <th style="padding:12px;text-align:left;">Expiry</th>
                            <th style="padding:12px;text-align:left;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($domains as $domain)
                            @php
                                $expiryValue = $domain->expiry_date ?? null;
                                $expiry = $expiryValue ? \Illuminate\Support\Carbon::parse($expiryValue)->format('M d, Y') : '-';
                            @endphp
                            <tr style="border-bottom:1px solid var(--gray-100);">
                                <td style="padding:12px;"><strong>{{ $domain->domain_name ?? $domain->domain ?? 'Domain #' . $domain->id }}</strong></td>
                                <td style="padding:12px;">{{ $domain->registrar_slug ?? $domain->registrar ?? 'Manual' }}</td>
                                <td style="padding:12px;">{{ $expiry }}</td>
                                <td style="padding:12px;"><span class="pill pill-{{ ($domain->status ?? '') === 'active' ? 'ok' : 'info' }}">{{ ucfirst($domain->status ?? 'pending') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:1rem;">{{ $domains->links() }}</div>
        @else
            <div style="text-align:center;padding:56px;">
                <div style="font-size:3rem;">🌐</div>
                <h3>No domains yet</h3>
                <p style="color:var(--gray-600);">Register or transfer a domain to manage it here.</p>
                <a href="{{ route('order.domains') }}" class="btn btn-primary">Search Domains</a>
            </div>
        @endif
    </div>
</main>
@endsection
