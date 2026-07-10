<?php

use App\Core\Payments\PaymentGatewayRegistry;
use Plugins\SelcomPayment\src\Gateways\SelcomGateway;

PaymentGatewayRegistry::register('selcom', SelcomGateway::class);
