<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\HttpController;
use Symfony\Component\HttpFoundation\Response;

interface OnContestController
{
    function onContestController(App $app, HttpController $controller) : HttpController;
}