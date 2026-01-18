<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\SystemcallController;

interface OnContestController
{
    function onContestController(App $app, SystemcallController $controller) : SystemcallController;
}