<?php

use App\Core\Hooks\Action;
use App\Core\NotificationService;

// Invoice created → notify client
Action::add('invoice.created', function($invoice) {
    $service = new NotificationService();
    $service->send('invoice.created', $invoice->client->email, [
        'client_name' => $invoice->client->full_name,
        'invoice_number' => $invoice->invoice_number,
        'total' => jv_format_money($invoice->total),
        'due_date' => $invoice->due_date ? $invoice->due_date->format('d M, Y') : 'N/A',
    ]);
});

// Invoice paid → notify client
Action::add('invoice.paid', function($invoice) {
    $service = new NotificationService();
    $service->send('invoice.paid', $invoice->client->email, [
        'client_name' => $invoice->client->full_name,
        'invoice_number' => $invoice->invoice_number,
        'total' => jv_format_money($invoice->total),
        'paid_date' => $invoice->paid_at ? $invoice->paid_at->format('d M, Y') : now()->format('d M, Y'),
    ]);
});

// Domain expiring → notify client
Action::add('domain.expiring', function($domain, $days) {
    $service = new NotificationService();
    $service->send('domain.expiring', $domain->client->email, [
        'client_name' => $domain->client->full_name,
        'domain' => $domain->domain_name,
        'days' => $days,
        'expiry_date' => $domain->expiry_date ? $domain->expiry_date->format('d M, Y') : 'N/A',
    ]);
});

// Order confirmed → notify client
Action::add('order.accepted', function($order) {
    $service = new NotificationService();
    $service->send('order.confirmed', $order->client->email, [
        'client_name' => $order->client->full_name,
        'order_number' => $order->order_number,
        'total' => jv_format_money($order->total),
    ]);
});
