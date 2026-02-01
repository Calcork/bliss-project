<?php

namespace App\Base\HttpFulfillers\OnContestContextFulfillers;

use App\Lib\RateLimiter\RateLimiter;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimit implements OnContestContext
{

    /**
     * @param App $app
     * @param Request $request
     * @return Request|Response
     */
    function onContestContext(App $app, Request $request): Request|Response
    {

        if($request->attributes->get('has_admin_key', false)) {
            return $request;
        }


        $rate_limiter = new RateLimiter(100, '1 minute');

        if ($rate_limiter->isLimited($request)) {
            return new Response('Too Many Requests', 429);
        }

        return $request;

    }

}