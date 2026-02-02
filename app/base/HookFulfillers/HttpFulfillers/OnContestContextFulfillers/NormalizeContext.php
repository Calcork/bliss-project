<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeContext extends HookFulfiller implements OnContestContext
{
    public function onContestContext(Request $request): Request|Response
    {
        $env = $this->app->getEnv();

        $session_options = [
            'cookie_httponly' => true,
            'cookie_secure' => !$env['APP_DEVELOPMENT'],
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
            'use_only_cookies' => true,
            'cookie_path' => '/',
        ];

        // Always enforce session config, even if session is already started
        if (session_status() === PHP_SESSION_ACTIVE) {
            $current = session_get_cookie_params();

            $needs_restart =
                $current['httponly'] !== $session_options['cookie_httponly']
                || $current['secure'] !== $session_options['cookie_secure']
                || $current['samesite'] !== $session_options['cookie_samesite']
                || $current['path'] !== $session_options['cookie_path'];

            if ($needs_restart) {
                session_write_close();
                session_start($session_options);
            }
        } else {
            session_start($session_options);
        }

        return $request;
    }
}
