<?php

use App\Core\Payments\PaymentGatewayRegistry;
use Plugins\OfflinePayments\src\Gateways\BankDepositGateway;
use Plugins\OfflinePayments\src\Gateways\CashPaymentGateway;

PaymentGatewayRegistry::register('bank_deposit', BankDepositGateway::class);
PaymentGatewayRegistry::register('cash', CashPaymentGateway::class);
