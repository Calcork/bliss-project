<?php

namespace App\Base\HttpFulfillers\OnContestControllerFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestController;
use Hizech\Bliss\Controller\HttpController;

class BaseController implements OnContestController
{
    public function onContestController(App $app, HttpController $controller): HttpController
    {
        return $controller;
    }
}
