<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\ControllerHandler;
use Symfony\Component\HttpFoundation\Request;

interface OnNotFound
{
    function onNotFound(App $app, Request $request) : ControllerHandler;
}