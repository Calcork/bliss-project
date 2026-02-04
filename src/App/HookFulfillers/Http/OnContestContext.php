<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface OnContestContext
{
    function onContestContext(Request $request) : Request|Response;
}