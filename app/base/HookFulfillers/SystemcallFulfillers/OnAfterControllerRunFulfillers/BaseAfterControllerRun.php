<?php

namespace App\Base\HookFulfillers\SystemcallFulfillers\OnAfterControllerRunFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnAfterControllerRun;
use Hizech\Bliss\Controller\SystemcallControllerReport;

class BaseAfterControllerRun extends HookFulfiller  implements OnAfterControllerRun
{

    /**
     * @param App $app
     * @param SystemcallControllerReport $response
     * @return void
     */
    function onAfterControllerRun(SystemcallControllerReport $response): void
    {
        // TODO: Implement onAfterControllerRun() method.
    }
}
