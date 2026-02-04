<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class NormalizeSession extends HookFulfiller implements OnContestContext
{
    public function onContestContext(Request $request): Request|Response
    {
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
