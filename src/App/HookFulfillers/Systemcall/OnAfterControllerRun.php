<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\SystemcallControllerReport;

interface OnAfterControllerRun
{
    function onAfterControllerRun(SystemcallControllerReport $response) : void;
}