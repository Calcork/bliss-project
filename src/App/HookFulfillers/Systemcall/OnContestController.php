<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\SystemcallController;
use Hizech\Bliss\Controller\SystemcallControllerReport;

interface OnContestController
{
    function onContestController(App $app, SystemcallController $controller) : SystemcallController;
}