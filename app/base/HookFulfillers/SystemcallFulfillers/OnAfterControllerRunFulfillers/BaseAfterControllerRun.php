<?php

namespace App\Base\HookFulfillers\SystemcallFulfillers\OnAfterControllerRunFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnAfterControllerRun;
use Hizech\Bliss\Controller\SystemcallControllerReport;

class BaseAfterControllerRun extends HookFulfiller implements OnAfterControllerRun
{
    function onAfterControllerRun(SystemcallControllerReport $response): void
    {
        // TODO: Implement onAfterControllerRun() method.
    }
}
