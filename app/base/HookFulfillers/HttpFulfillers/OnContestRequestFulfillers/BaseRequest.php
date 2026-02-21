<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestRequestFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use App\Lib\RateLimiter\RateLimiter;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseRequest extends HookFulfiller implements OnContestRequest
{
    public function onContestRequest(Request $request): Request|Response
    {

        $path = $request->getPathInfo();

        if ($path !== '/' && str_ends_with($path, '/')) {
            $query = $request->getQueryString();
            $base_path = $request->getBaseUrl();
            $url = $base_path . rtrim($path, '/') . ($query !== null ? '?' . $query : '');
            return new RedirectResponse($url, 301);
        }

        $has_bypass_key = ($request->headers->get('X-Bypass-Key', null) === $this->app->getEnv()['BYPASS_KEY']);

        $rate_limiter = new RateLimiter(100, '1 minute');

        if ($has_bypass_key === false && $rate_limiter->isLimited($request)) {
            return new Response('Too Many Requests', 429);
        }

       return $request;

    }
}
