<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Hizech\Bliss\App\App;
use Symfony\Component\HttpFoundation\Response;

interface OnAfterResponseSent
{
    function OnAfterResponseSent(App $app, Response $response) : void;
}