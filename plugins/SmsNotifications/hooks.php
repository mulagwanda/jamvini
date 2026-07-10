<?php

use App\Core\Hooks\Action;

// When an invoice is created, notify the client
Action::add('invoice.created', function($invoice) {
    $phone = $invoice->client->phone ?? $invoice->client->mobile;
    $message = "Invoice #{$invoice->invoice_number} created. Total: " . jv_format_money($invoice->total) . ". Pay before {$invoice->due_date->format('d/m/Y')}";
    
    // Send SMS (mock — replace with actual API call)
    sendSms($phone, $message);
});

// When invoice is paid
Action::add('invoice.paid', function($invoice) {
    $phone = $invoice->client->phone ?? $invoice->client->mobile;
    $message = "Payment received! Invoice #{$invoice->invoice_number} is now PAID. Thank you!";
    sendSms($phone, $message);
});

// Domain expiring alert
Action::add('domain.expiring', function($domain) {
    $phone = $domain->client->phone ?? $domain->client->mobile;
    $days = $domain->days_until_expiry;
    $message = "⚠️ {$domain->domain_name} expires in {$days} days! Renew now to avoid downtime.";
    sendSms($phone, $message);
});

function sendSms($phone, $message)
{
    if (!$phone) return;
    
    // In production, integrate with Africa's Talking or Beem API
    \Illuminate\Support\Facades\Log::info("SMS to {$phone}: {$message}");
    
    Action::do('sms.sent', ['phone' => $phone, 'message' => $message]);
}
