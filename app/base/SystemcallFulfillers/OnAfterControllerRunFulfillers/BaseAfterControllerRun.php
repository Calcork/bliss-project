<?php

namespace App\Base\SystemcallFulfillers\OnAfterControllerRunFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnAfterControllerRun;
use Hizech\Bliss\Controller\SystemcallControllerReport;

class BaseAfterControllerRun implements OnAfterControllerRun
{

    /**
     * @param App $app
     * @param SystemcallControllerReport $response
     * @return void
     */
    function onAfterControllerRun(App $app, SystemcallControllerReport $response): void
    {
        // TODO: Implement onAfterControllerRun() method.
    }
}
