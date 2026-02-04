<?php

namespace App\Controllers\DirectController;

use Hizech\Bliss\Controller\SystemcallControllerReport;

class Example extends DirectController
{

    function example() : SystemcallControllerReport {
        echo 'Hi';
        return SystemcallControllerReport::success();
    }

}