<?php

use App\Core\ActivityLogger;
use App\Core\Hooks\Action;
use Plugins\Orders\src\Controllers\OrderController;
use Plugins\Orders\src\Models\Order;

Action::add('invoice.paid', function ($invoice) {
    $order = Order::where('invoice_id', $invoice->id)->first();

    if (!$order) {
        return;
    }

    $result = app(OrderController::class)->completePaidOrder($order, 'invoice.paid');

    ActivityLogger::log(
        $result['success'] ? 'provisioning.auto.completed' : 'provisioning.auto.failed',
        'Order',
        $order->id,
        $result['message'],
        [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'trigger' => 'invoice.paid',
        ]
    );
});
