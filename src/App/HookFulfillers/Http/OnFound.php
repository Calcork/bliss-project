<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Hizech\Bliss\Controller\ControllerHandler;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface OnFound
{
    function onFound(Request $request, Found|null $routing_result): Response|true|ControllerHandler;
}