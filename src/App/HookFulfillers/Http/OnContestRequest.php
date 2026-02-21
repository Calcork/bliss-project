<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface OnContestRequest
{
    function onContestRequest(Request $request) : Request|Response;
}