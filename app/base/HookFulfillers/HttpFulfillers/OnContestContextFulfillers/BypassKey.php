<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BypassKey extends HookFulfiller implements OnContestContext
{

    /**
     * @param Request $request
     * @return Request|Response
     */
    function onContestContext(Request $request): Request|Response
    {

        $has_bypass_key = ($request->headers->get('X-Bypass-Key', null) === $this->app->getEnv()['BYPASS_KEY']);
        $request->attributes->add(['has_bypass_key' => $has_bypass_key]);

        return $request;

    }

}