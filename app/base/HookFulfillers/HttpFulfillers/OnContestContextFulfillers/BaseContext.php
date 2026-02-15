<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use App\Lib\RateLimiter\RateLimiter;
use App\Lib\TemplateMaster\TranslationExtension;
use App\Models\Language;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class BaseContext extends HookFulfiller implements OnContestContext
{
    public function onContestContext(Request $request): Request|Response
    {
        $has_bypass_key = ($request->headers->get('X-Bypass-Key', null) === $this->app->getEnv()['BYPASS_KEY']);

        $rate_limiter = new RateLimiter(100, '1 minute');

        if ($has_bypass_key === false && $rate_limiter->isLimited($request)) {
            return new Response('Too Many Requests', 429);
        }

        $path = $request->getPathInfo();

        if ($path !== '/' && str_ends_with($path, '/')) {
            $query = $request->getQueryString();
            $base_path = $request->getBaseUrl();
            $url = $base_path . rtrim($path, '/') . ($query !== null ? '?' . $query : '');
            return new RedirectResponse($url, 301);
        }

        if(!$request->hasSession()) {

            $env = $this->app->getEnv();

            $storage = new NativeSessionStorage([
                'cookie_httponly' => true,
                'cookie_secure' => !$env['APP_DEVELOPMENT'],
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
                'use_only_cookies' => true,
                'cookie_path' => '/',
            ], $this->app->getSessionHandler());

            $session = new Session($storage);
            $request->setSession($session);
            $session->start();

            $request->setSession($session);

        }


        return $request;
    }
}
