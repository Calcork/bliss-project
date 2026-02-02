<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseContext extends HookFulfiller implements OnContestContext
{
    public function onContestContext(Request $request): Request|Response
    {
        return $request;
    }
}
