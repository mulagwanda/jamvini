<?php

namespace Plugins\SelcomPayment\src;

use App\Http\Controllers\Controller;
use App\Core\Payments\PaymentManager;
use App\Core\Payments\PaymentGatewayRegistry;
use App\Models\Setting;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Invoices\src\Models\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        // Show payment configuration and transaction history
        $config = [
            'enabled' => Setting::get('selcom_enabled', '1'),
            'vendor_id' => Setting::get('selcom_vendor_id', ''),
            'api_key' => Setting::get('selcom_api_key', ''),
            'api_secret' => str_repeat('*', 12),
            'test_mode' => Setting::get('selcom_test_mode', '1'),
            'is_configured' => !empty(Setting::get('selcom_vendor_id')),
        ];

        return view('plugins.selcom-payment::admin.index', compact('config'));
    }

    public function settings(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'selcom_enabled' => 'nullable|boolean',
                'selcom_test_mode' => 'nullable|boolean',
                'vendor_id' => 'required|string',
                'api_key' => 'required|string',
                'api_secret' => 'nullable|string',
            ]);

            Setting::set('selcom_enabled', $request->boolean('selcom_enabled') ? '1' : '0', 'payments');
            Setting::set('selcom_test_mode', $request->boolean('selcom_test_mode') ? '1' : '0', 'payments');
            Setting::set('selcom_vendor_id', $validated['vendor_id'], 'payments');
            Setting::set('selcom_api_key', $validated['api_key'], 'payments');
            if (!empty($validated['api_secret'])) {
                Setting::set('selcom_api_secret', $validated['api_secret'], 'payments');
            }

            return redirect()->route('admin.selcom.index')
                ->with('success', 'Selcom configuration saved!');
        }

        return view('plugins.selcom-payment::admin.settings');
    }

    public function processPayment(Invoice $invoice)
    {
        $result = app(PaymentManager::class)->initiate($invoice, 'selcom');

        return view('plugins.selcom-payment::admin.payment', [
            'invoice' => $invoice,
            'paymentData' => $result->payload + [
                'amount' => $result->amount,
                'status' => $result->status,
            ],
        ]);
    }

    public function callback(Request $request)
    {
        // Handle Selcom callback
        $data = $request->all();
        
        if ($request->input('status') === 'SUCCESS') {
            $invoiceId = $request->input('invoice_id');
            $invoice = $invoiceId ? Invoice::find($invoiceId) : null;
            if ($invoice) {
                app(PaymentManager::class)->record(
                    invoice: $invoice,
                    amount: (float) $request->input('amount', $invoice->remaining_amount),
                    gatewaySlug: 'selcom',
                    transactionId: $request->input('transaction_id', $request->input('transid')),
                    status: 'completed',
                    notes: 'Selcom callback payment.',
                    metadata: $data
                );
            }
            \App\Core\Hooks\Action::do('payment.received', $data);
            return response()->json(['status' => 'ok']);
        }

        \App\Core\Hooks\Action::do('payment.failed', $data);
        return response()->json(['status' => 'failed']);
    }
}
