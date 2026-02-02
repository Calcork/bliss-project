<?php

namespace App\Base\HookFulfillers\SystemcallFulfillers\OnAfterControllerRunFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnAfterControllerRun;
use Hizech\Bliss\Controller\SystemcallControllerReport;
use Hizech\Bliss\Controller\SystemcallControllerReportCode;

class NoteIfFailed  extends HookFulfiller implements OnAfterControllerRun
{

    /**
     * @param App $app
     * @param SystemcallControllerReport $response
     * @return void
     */
    function onAfterControllerRun(SystemcallControllerReport $response): void
    {
        if($response->status !== SystemcallControllerReportCode::Success) {

            echo N . 'Command failed with status: ' . $response->status->name . ', code: ' . $response->status->value . N;
            echo N . 'report error message is: ' . N . ($response->message ?? '') . N;

        }
    }
}
