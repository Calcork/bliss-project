<?php

namespace App\Base\SystemcallFulfillers\OnContestControllerFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnContestController;
use Hizech\Bliss\Controller\SystemcallController;

class BaseController implements OnContestController
{
    public function onContestController(App $app, SystemcallController $controller): SystemcallController
    {
        return $controller;
    }
}
