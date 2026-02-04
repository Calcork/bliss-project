<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Symfony\Component\HttpFoundation\Response;

interface OnAfterResponseDecided
{
    function OnAfterResponseDecided(Response $response) : void;
}