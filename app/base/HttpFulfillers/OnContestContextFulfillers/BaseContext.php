<?php

namespace App\Base\HttpFulfillers\OnContestContextFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseContext implements OnContestContext
{
    public function onContestContext(App $app, Request $request): Request|Response
    {
        return $request;
    }
}
