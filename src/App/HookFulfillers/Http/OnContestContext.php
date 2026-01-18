<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Hizech\Bliss\App\App;
use Symfony\Component\HttpFoundation\Request;

interface OnContestContext
{
    function onContestContext(App $app, Request $request) : Request;
}