<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Api\InternalApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoreApiController extends Controller
{
    public function clients(Request $request)
    {
        return response()->json(app(InternalApi::class)->call('GetClients', $request->all()));
    }

    public function createClient(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'first_name' => 'nullable|string', 'last_name' => 'nullable|string', 'phone' => 'nullable|string', 'company_name' => 'nullable|string']);

        return response()->json(app(InternalApi::class)->call('CreateClient', $data), 201);
    }

    public function invoices(Request $request)
    {
        return response()->json(app(InternalApi::class)->call('GetInvoices', $request->all()));
    }

    public function services(Request $request)
    {
        return response()->json(app(InternalApi::class)->call('GetServices', $request->all()));
    }

    public function domains(Request $request)
    {
        return response()->json(app(InternalApi::class)->call('GetDomains', $request->all()));
    }

    public function tickets()
    {
        $tickets = DB::table('support_tickets')->latest()->limit(50)->get();

        return response()->json(['success' => true, 'data' => $tickets]);
    }

    public function action(Request $request)
    {
        $data = $request->validate(['action' => 'required|string', 'params' => 'nullable|array']);

        return response()->json(app(InternalApi::class)->call($data['action'], $data['params'] ?? []));
    }
}
