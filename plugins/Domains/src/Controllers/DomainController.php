<?php

namespace Plugins\Domains\src\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Domains\src\Models\Domain;
use Plugins\Clients\src\Models\Client;
use Illuminate\Http\Request;
use App\Core\Hooks\Action;

class DomainController extends Controller
{
    public function index(Request $request)
    {
        $domains = Domain::with('client')
            ->when($request->search, function($query, $search) {
                $query->where('domain_name', 'like', "%{$search}%")
                      ->orWhere('registrar', 'like', "%{$search}%");
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(15);

        $stats = [
            'total' => Domain::count(),
            'active' => Domain::active()->count(),
            'expiring' => Domain::expiringSoon()->count(),
            'expired' => Domain::expired()->count(),
        ];

        return view('plugins.Domains::admin.index', compact('domains', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('first_name')->get();
        return view('plugins.Domains::admin.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'domain_name' => 'required|string|max:255|unique:domains,domain_name',
            'tld' => 'nullable|string|max:20',
            'registrar' => 'nullable|string|max:100',
            'registration_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'registration_period' => 'nullable|integer|min:1|max:10',
            'registration_fee' => 'nullable|numeric|min:0',
            'renewal_fee' => 'nullable|numeric|min:0',
            'nameservers' => 'nullable|array',
            'status' => 'required|in:active,expired,transferred,suspended',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['nameservers'])) {
            $validated['nameservers'] = array_values(array_filter($validated['nameservers']));
        }

        $domain = Domain::create($validated);
        Action::do('domain.created', $domain);

        return redirect()->route('admin.domains.index')
            ->with('success', 'Domain "' . $domain->domain_name . '" added!');
    }

    public function show(Domain $domain)
    {
        $domain->load('client');
        return view('plugins.Domains::admin.show', compact('domain'));
    }

    public function edit(Domain $domain)
    {
        $clients = Client::orderBy('first_name')->get();
        return view('plugins.Domains::admin.edit', compact('domain', 'clients'));
    }

    public function update(Request $request, Domain $domain)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'domain_name' => 'required|string|max:255|unique:domains,domain_name,' . $domain->id,
            'tld' => 'nullable|string|max:20',
            'registrar' => 'nullable|string|max:100',
            'registration_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'registration_period' => 'nullable|integer|min:1|max:10',
            'registration_fee' => 'nullable|numeric|min:0',
            'renewal_fee' => 'nullable|numeric|min:0',
            'nameservers' => 'nullable|array',
            'status' => 'required|in:active,expired,transferred,suspended',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!empty($validated['nameservers'])) {
            $validated['nameservers'] = array_values(array_filter($validated['nameservers']));
        }

        $domain->update($validated);
        Action::do('domain.updated', $domain);

        return redirect()->route('admin.domains.index')
            ->with('success', 'Domain "' . $domain->domain_name . '" updated!');
    }

    public function destroy(Domain $domain)
    {
        $domainName = $domain->domain_name;
        $domain->delete();
        Action::do('domain.deleted', $domain);

        return redirect()->route('admin.domains.index')
            ->with('success', 'Domain "' . $domainName . '" deleted!');
    }
}