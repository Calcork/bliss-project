<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use App\Lib\RateLimiter\RateLimiter;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimit extends HookFulfiller implements OnContestContext
{

    /**
     * @param Request $request
     * @return Request|Response
     */
    function onContestContext(Request $request): Request|Response
    {

        if($request->attributes->get('has_bypass_key', false) === true) {
            return $request;
        }

        $rate_limiter = new RateLimiter(100, '1 minute');

        if ($rate_limiter->isLimited($request)) {
            return new Response('Too Many Requests', 429);
        }

        return $request;

    }

}