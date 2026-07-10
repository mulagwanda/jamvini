<?php

use App\Core\Hooks\Action;
use Plugins\Reports\src\Reports\BuiltInReports;

BuiltInReports::register();

Action::do('reports.registered');
