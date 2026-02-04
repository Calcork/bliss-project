<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Hizech\Bliss\Controller\ControllerHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface OnNotFound
{
    function onNotFound(Request $request) : true|ControllerHandler|Response;
}