<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSlash extends HookFulfiller implements OnContestContext
{
    public function onContestContext(Request $request): Request|Response
    {
        $path = $request->getPathInfo();

        if ($path !== '/' && !str_ends_with($path, '/')) {
            $query = $request->getQueryString();
            $base_path = $request->getBaseUrl();
            $url = $base_path . $path . '/' . ($query !== null ? '?' . $query : '');
            return new RedirectResponse($url, 301);
        }

        return $request;
    }
}
