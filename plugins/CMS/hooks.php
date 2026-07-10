<?php

use App\Core\Hooks\Filter;
use Plugins\CMS\src\Controllers\FrontendController;

Filter::add('frontend.homepage', function ($homepage) {
    try {
        return app(FrontendController::class)->homepage();
    } catch (\Throwable $e) {
        report($e);

        return $homepage;
    }
}, 10, 1);
