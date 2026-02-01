<?php

namespace App\Controllers\AppController\SystemCall;

use Hizech\Bliss\Controller\SystemcallControllerReport;

class Example extends SystemCall
{
    function example() : SystemcallControllerReport
    {
        echo 'Bla';
        return SystemcallControllerReport::Success();
    }
}